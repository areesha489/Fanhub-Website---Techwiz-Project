<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\FanSubmission;
use App\Models\User;
use App\Support\Engagement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Community fan submissions.
 *
 * GET|POST /submit-content · GET /my-submissions
 */
class SubmissionController extends Controller
{
    /**
     * Image uploads accepted with a submission.
     *
     * @var list<string>
     */
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    /**
     * Show the submission form.
     */
    public function create(Request $request): View
    {
        return view('submissions.create', [
            'categories' => Category::query()->orderBy('position')->orderBy('name')->get(),
            'types' => FanSubmission::TYPES,
        ]);
    }

    /**
     * Store a new submission for moderator review and alert the admins.
     */
    public function store(Request $request): RedirectResponse
    {
        $categoryId = $request->input('category_id');

        if ($categoryId === null || $categoryId === '' || $categoryId === '0' || $categoryId === 0) {
            $request->merge(['category_id' => null]);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'type' => ['required', 'string', 'in:'.implode(',', FanSubmission::TYPES)],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'description' => ['required', 'string', 'min:30', 'max:5000'],
            'url' => ['nullable', 'url', 'max:500'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [], [
            'title' => 'title',
            'description' => 'description',
            'url' => 'link',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = strtolower((string) $file->extension());

            if (! in_array($extension, self::IMAGE_EXTENSIONS, true)) {
                return back()->withErrors(['image' => 'Use a JPG, PNG or WebP image.'])->withInput();
            }

            if (@getimagesize($file->getRealPath()) === false) {
                return back()->withErrors(['image' => 'That file does not look like a readable image.'])->withInput();
            }

            $stored = $file->storeAs('submissions', Str::uuid()->toString().'.'.$extension, 'public');

            if ($stored === false) {
                return back()->with('error', 'We could not store your image. Please try again.')->withInput();
            }

            $imagePath = $stored;
        }

        $user = $request->user();

        $submission = FanSubmission::query()->create([
            'user_id' => $user->getKey(),
            'title' => $validated['title'],
            'type' => $validated['type'],
            'category_id' => $validated['category_id'] ?? null,
            'description' => $validated['description'],
            'url' => $validated['url'] ?? null,
            'image' => $imagePath,
            'status' => FanSubmission::STATUS_PENDING,
        ]);

        Engagement::log($user->getKey(), 'submission', 'Submitted “'.$submission->title.'” for review', FanSubmission::class, $submission->getKey());

        Engagement::notify(
            (int) $user->getKey(),
            'Submission received',
            'Your submission "'.$submission->title.'" is in the review queue. We will tell you when a moderator decides.',
            route('submissions.index'),
        );

        try {
            User::query()->admins()->pluck('id')->each(function (mixed $adminId) use ($submission, $user): void {
                Engagement::notify(
                    (int) $adminId,
                    'New fan submission',
                    $user->name.' sent "'.$submission->title.'" and it is waiting for review.',
                    route('admin.submissions.index')
                );
            });
        } catch (\Throwable $exception) {
            report($exception);
        }

        return redirect()
            ->route('submissions.index')
            ->with('success', 'Thanks! Your submission “'.$submission->title.'” is now in the review queue.');
    }

    /**
     * Everything the member has submitted.
     */
    public function index(Request $request): View
    {
        $submissions = FanSubmission::query()
            ->with('category')
            ->where('user_id', $request->user()->getKey())
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('submissions.index', [
            'submissions' => $submissions,
        ]);
    }
}
