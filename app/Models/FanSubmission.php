<?php

namespace App\Models;

use App\Support\SubmissionPublisher;
use Database\Factories\FanSubmissionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FanSubmission extends Model
{
    /** @use HasFactory<FanSubmissionFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'fan_submissions';

    /**
     * Submission kinds accepted by the community queue.
     *
     * @var list<string>
     */
    public const TYPES = ['article', 'artwork', 'video', 'cosplay', 'other'];

    /**
     * Moderation states a submission can be in.
     */
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    /**
     * Publish or retract the public copy whenever moderation changes.
     */
    protected static function booted(): void
    {
        static::creating(function (FanSubmission $submission): void {
            if (! is_string($submission->slug) || $submission->slug === '') {
                $submission->slug = app(SubmissionPublisher::class)->makeSlug((string) $submission->title);
            }
        });

        static::saved(function (FanSubmission $submission): void {
            if ($submission->wasRecentlyCreated || $submission->wasChanged('status')) {
                app(SubmissionPublisher::class)->sync($submission);
            }
        });

        static::deleting(function (FanSubmission $submission): void {
            app(SubmissionPublisher::class)->retract($submission);
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'type',
        'category_id',
        'description',
        'url',
        'image',
        'status',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * Member that submitted the fan work.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Fandom bucket suggested for the submission.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Administrator that reviewed the submission.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Article created when a write-up is approved.
     */
    public function publishedArticle(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'published_article_id');
    }

    /**
     * Multimedia entry created when visual fan work is approved.
     */
    public function publishedMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'published_media_id');
    }

    /**
     * Public URL for a stored upload, or the original address when it is already absolute.
     */
    public function imageUrl(): ?string
    {
        $path = $this->image;

        if (! is_string($path) || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        return asset('storage/'.$path);
    }

    /**
     * Uploaded picture when the member sent one, otherwise a fandom still.
     */
    public function displayImage(): string
    {
        return $this->imageUrl() ?: catalogueStill($this->category?->slug, (string) $this->title);
    }

    /**
     * Filter by moderation state.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Limit to submissions a moderator has released onto the site.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }
}
