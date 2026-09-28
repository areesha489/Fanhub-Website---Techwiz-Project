<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\ChatbotKnowledge;
use App\Models\ChatbotQuery;
use App\Support\ApiSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KnowledgeController extends AdminApiController
{
    /**
     * GET /api/admin/knowledge — the assistant's knowledge base.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ChatbotKnowledge::query();

        $this->applySearch($request, $query, ['question', 'answer', 'keywords']);

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $query->orderBy('id');

        $paginator = $query->paginate($this->perPage($request, 15))->withQueryString();

        return response()->json(
            ApiSerializer::page($paginator, fn (ChatbotKnowledge $entry): array => $this->payload($entry))
        );
    }

    /**
     * POST /api/admin/knowledge — add a FAQ entry.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $entry = ChatbotKnowledge::query()->create($data);

        return response()->json(['data' => $this->payload($entry)], 201);
    }

    /**
     * PUT/PATCH /api/admin/knowledge/{knowledge} — update a FAQ entry.
     */
    public function update(Request $request, ChatbotKnowledge $knowledge): JsonResponse
    {
        $data = $this->validated($request, partial: true);

        $knowledge->update($data);

        return response()->json(['data' => $this->payload($knowledge->refresh())]);
    }

    /**
     * DELETE /api/admin/knowledge/{knowledge} — remove a FAQ entry.
     */
    public function destroy(ChatbotKnowledge $knowledge): JsonResponse
    {
        $knowledge->delete();

        return response()->json(['message' => 'Knowledge entry deleted.']);
    }

    /**
     * GET /api/admin/chat-queries — everything the assistant has been asked.
     */
    public function queries(Request $request): JsonResponse
    {
        $query = ChatbotQuery::query()->with(['user', 'knowledge']);

        $this->applySearch($request, $query, ['message', 'response', 'session_id']);

        $query->orderByDesc('id');

        $paginator = $query->paginate($this->perPage($request, 15))->withQueryString();

        return response()->json(
            ApiSerializer::page($paginator, fn (ChatbotQuery $row): array => [
                'id' => $row->id,
                'user' => ApiSerializer::user($row->user),
                'session_id' => $row->session_id,
                'message' => $row->message,
                'response' => $row->response,
                'knowledge_id' => $row->knowledge_id,
                'knowledge' => $row->knowledge !== null ? $this->payload($row->knowledge) : null,
                'created_at' => $row->created_at?->toIso8601String(),
            ])
        );
    }

    /**
     * Shared validation for store/update.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'question' => [$required, 'string', 'max:500'],
            'answer' => [$required, 'string', 'max:4000'],
            'keywords' => ['nullable', 'string', 'max:1000'],
            'category' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    /**
     * JSON payload for a knowledge entry.
     *
     * @return array<string, mixed>
     */
    private function payload(ChatbotKnowledge $entry): array
    {
        return [
            'id' => $entry->id,
            'question' => $entry->question,
            'answer' => $entry->answer,
            'keywords' => $entry->keywords,
            'category' => $entry->category,
            'is_active' => (bool) $entry->is_active,
            'created_at' => $entry->created_at?->toIso8601String(),
            'updated_at' => $entry->updated_at?->toIso8601String(),
        ];
    }
}
