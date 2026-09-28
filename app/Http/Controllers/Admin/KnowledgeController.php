<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatbotKnowledge;
use App\Models\ChatbotQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class KnowledgeController extends Controller
{
    /**
     * Paginated chatbot knowledge base with search and active filter.
     */
    public function index(Request $request): View
    {
        $query = ChatbotKnowledge::query();

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->search($search);
        }

        $active = (string) $request->query('active', '');
        if ($active === '1') {
            $query->where('is_active', true);
        } elseif ($active === '0') {
            $query->where('is_active', false);
        }

        $sortOptions = [
            'newest' => 'Newest first',
            'question' => 'Question A → Z',
            'category' => 'Category A → Z',
        ];

        $sort = (string) $request->query('sort', 'newest');
        if (! isset($sortOptions[$sort])) {
            $sort = 'newest';
        }

        match ($sort) {
            'question' => $query->orderBy('question', 'asc'),
            'category' => $query->orderBy('category')->orderBy('question'),
            default => $query->orderBy('created_at', 'desc'),
        };

        $items = $query->paginate(25)->withQueryString();

        return view('admin.chatbot.index', [
            'items' => $items,
            'search' => $search,
            'active' => $active,
            'sort' => $sort,
            'sortOptions' => $sortOptions,
        ]);
    }

    /**
     * Blank form for a new knowledge entry.
     */
    public function create(): View
    {
        return view('admin.chatbot.form', [
            'knowledge' => new ChatbotKnowledge,
            'formTitle' => 'Add knowledge entry',
        ]);
    }

    /**
     * Persist a new knowledge entry.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateKnowledge($request);

        $knowledge = ChatbotKnowledge::create($this->mapFields($request, $validated));

        return redirect()
            ->route('admin.chatbot.edit', $knowledge)
            ->with('success', 'Knowledge entry created.');
    }

    /**
     * Edit form for an existing knowledge entry.
     */
    public function edit(ChatbotKnowledge $knowledge): View
    {
        return view('admin.chatbot.form', [
            'knowledge' => $knowledge,
            'formTitle' => 'Edit knowledge entry',
        ]);
    }

    /**
     * Update a knowledge entry.
     */
    public function update(Request $request, ChatbotKnowledge $knowledge): RedirectResponse
    {
        $validated = $this->validateKnowledge($request);

        $knowledge->fill($this->mapFields($request, $validated))->save();

        return redirect()
            ->route('admin.chatbot.index')
            ->with('success', 'Knowledge entry updated.');
    }

    /**
     * Delete a knowledge entry.
     */
    public function destroy(ChatbotKnowledge $knowledge): RedirectResponse
    {
        $knowledge->delete();

        return redirect()
            ->route('admin.chatbot.index')
            ->with('success', 'Knowledge entry deleted.');
    }

    /**
     * Chatbot question log with search and has-user filter.
     */
    public function queries(Request $request): View
    {
        $query = ChatbotQuery::query()->with('user:id,name,email');

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';
            $query->where(fn ($builder) => $builder->where('message', 'like', $like)
                ->orWhere('response', 'like', $like));
        }

        $hasUser = (string) $request->query('user', '');
        if ($hasUser === '1') {
            $query->whereNotNull('user_id');
        } elseif ($hasUser === '0') {
            $query->whereNull('user_id');
        }

        $sortOptions = [
            'newest' => 'Newest first',
            'oldest' => 'Oldest first',
        ];

        $sort = (string) $request->query('sort', 'newest');
        if (! isset($sortOptions[$sort])) {
            $sort = 'newest';
        }

        match ($sort) {
            'oldest' => $query->orderBy('created_at', 'asc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        $items = $query->paginate(25)->withQueryString();

        return view('admin.chatbot.queries', [
            'items' => $items,
            'search' => $search,
            'hasUser' => $hasUser,
            'sort' => $sort,
            'sortOptions' => $sortOptions,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateKnowledge(Request $request): array
    {
        return $request->validate([
            'question' => ['required', 'string', 'max:300'],
            'answer' => ['required', 'string', 'max:4000'],
            'keywords' => ['nullable', 'string', 'max:500'],
            'category' => ['nullable', 'string', 'max:120'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function mapFields(Request $request, array $validated): array
    {
        return [
            'question' => $validated['question'],
            'answer' => $validated['answer'],
            'keywords' => $validated['keywords'] ?? null,
            'category' => $validated['category'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
