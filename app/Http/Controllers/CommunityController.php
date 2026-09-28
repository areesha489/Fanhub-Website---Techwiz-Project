<?php

namespace App\Http\Controllers;

use App\Models\FanSubmission;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Public fan posts that a moderator has approved.
 */
class CommunityController extends Controller
{
    /**
     * Cards rendered per page.
     */
    private const PER_PAGE = 12;

    /**
     * Approved fan posts, newest decision first.
     */
    public function index(Request $request): View
    {
        $type = (string) $request->query('type', '');

        if (! in_array($type, FanSubmission::TYPES, true)) {
            $type = '';
        }

        $posts = FanSubmission::query()
            ->approved()
            ->with(['user:id,name', 'category:id,name,slug'])
            ->when($type !== '', fn ($query) => $query->where('type', $type))
            ->orderByDesc('reviewed_at')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('community.index', [
            'posts' => $posts,
            'type' => $type,
            'types' => FanSubmission::TYPES,
        ]);
    }

    /**
     * One approved post. Pending and rejected submissions stay private.
     */
    public function show(string $slug): View
    {
        $post = FanSubmission::query()
            ->approved()
            ->with(['user:id,name', 'category:id,name,slug', 'publishedArticle', 'publishedMedia'])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('community.show', [
            'post' => $post,
        ]);
    }
}
