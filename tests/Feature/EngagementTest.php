<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\Content;
use App\Models\ContentView;
use App\Models\FanSubmission;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Member feedback loop: ratings, the public feedback form, fan submissions
 * and detail-page view tracking.
 */
class EngagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Content $content;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->content = Content::factory()->create();
    }

    public function test_member_can_rate_a_title(): void
    {
        $this->actingAs($this->user)
            ->postJson(route('rate'), [
                'type' => 'content',
                'id' => $this->content->id,
                'value' => 9,
            ])
            ->assertOk()
            ->assertJson(['ok' => true, 'value' => 9]);

        $this->assertDatabaseHas('ratings', [
            'user_id' => $this->user->id,
            'rateable_type' => Content::class,
            'rateable_id' => $this->content->id,
            'value' => 9,
        ]);
    }

    public function test_rating_a_title_twice_updates_the_same_row(): void
    {
        $payload = ['type' => 'content', 'id' => $this->content->id, 'value' => 7];

        $this->actingAs($this->user)->postJson(route('rate'), $payload)->assertOk();
        $this->actingAs($this->user)->postJson(route('rate'), [...$payload, 'value' => 10])->assertOk();

        $this->assertDatabaseCount('ratings', 1);
        $this->assertDatabaseHas('ratings', ['value' => 10]);
    }

    public function test_guest_rating_is_rejected(): void
    {
        $payload = ['type' => 'content', 'id' => $this->content->id, 'value' => 8];

        $this->postJson(route('rate'), $payload)->assertUnauthorized();
        $this->post(route('rate'), $payload)->assertRedirect(route('login'));

        $this->assertDatabaseCount('ratings', 0);
    }

    public function test_feedback_submission_stores_a_ticket(): void
    {
        $response = $this->post(route('feedback.store'), [
            'type' => 'bug',
            'subject' => 'The hero slider skips a slide',
            'message' => 'The first slide jumps to the third one when I swipe on mobile.',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('feedback', [
            'type' => 'bug',
            'subject' => 'The hero slider skips a slide',
            'status' => 'open',
        ]);
    }

    public function test_newsletter_signup_lands_in_the_feedback_inbox(): void
    {
        $this->from(route('home'))
            ->post(route('newsletter.store'), ['email' => 'fan@fanhubplus.test'])
            ->assertRedirect(route('home'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('feedback', [
            'type' => 'query',
            'subject' => 'Newsletter signup',
            'status' => 'open',
        ]);
    }

    public function test_newsletter_signup_rejects_a_bad_email(): void
    {
        $this->from(route('home'))
            ->post(route('newsletter.store'), ['email' => 'not-an-email'])
            ->assertRedirect(route('home'))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('feedback', 0);
    }

    public function test_feedback_form_rejects_an_incomplete_message(): void
    {
        $this->post(route('feedback.store'), [
            'type' => 'bug',
            'subject' => 'Hi',
            'message' => 'Too short.',
        ])->assertSessionHasErrors(['subject', 'message']);

        $this->assertDatabaseCount('feedback', 0);
    }

    public function test_fan_submission_is_stored_for_review(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)->post(route('submissions.store'), [
            'title' => 'My hand-painted mecha diorama',
            'type' => 'artwork',
            'category_id' => $category->id,
            'description' => 'A three-week build documenting the weathering process, primer coats and the LED wiring inside the chest cavity.',
        ]);

        $response->assertRedirect(route('submissions.index'));

        $this->assertDatabaseHas('fan_submissions', [
            'user_id' => $this->user->id,
            'title' => 'My hand-painted mecha diorama',
            'type' => 'artwork',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user->id,
            'title' => 'Submission received',
        ]);
    }

    public function test_submission_without_a_fandom_is_stored_and_notifies_the_member(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($this->user)->post(route('submissions.store'), [
            'title' => 'A sketch with no fandom picked',
            'type' => 'artwork',
            'category_id' => 0,
            'description' => 'A drawing of the cast, submitted without choosing a fandom from the list.',
        ])->assertRedirect(route('submissions.index'));

        $this->assertDatabaseHas('fan_submissions', [
            'user_id' => $this->user->id,
            'title' => 'A sketch with no fandom picked',
            'category_id' => null,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user->id,
            'title' => 'Submission received',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'title' => 'New fan submission',
        ]);
    }

    public function test_approving_a_submission_notifies_the_member(): void
    {
        $admin = User::factory()->admin()->create();
        $submission = FanSubmission::factory()->create([
            'user_id' => $this->user->id,
            'status' => FanSubmission::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.submissions.status', $submission), [
                'status' => FanSubmission::STATUS_APPROVED,
            ])
            ->assertRedirect(route('admin.submissions.index'));

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user->id,
            'title' => 'Submission approved',
        ]);

        $this->actingAs($this->user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Submission approved');
    }

    public function test_approving_a_write_up_publishes_it_on_the_site(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $submission = FanSubmission::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'type' => 'article',
            'title' => 'Night market field notes',
            'description' => 'A walk through the stalls with a <script>alert(1)</script> note that should stay plain text on the page.',
            'status' => FanSubmission::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.submissions.status', $submission), [
                'status' => FanSubmission::STATUS_APPROVED,
            ])
            ->assertRedirect(route('admin.submissions.index'));

        $submission->refresh();
        $article = Article::query()->findOrFail($submission->published_article_id);

        $this->assertSame(Article::STATUS_PUBLISHED, $article->status);
        $this->assertStringStartsWith('/images/catalogue/', (string) $article->cover_image);
        $this->assertStringNotContainsString('<script>', $article->body);

        $this->get(route('articles.show', $article->slug))
            ->assertOk()
            ->assertSee('Night market field notes')
            ->assertDontSee('<script>alert(1)</script>', false);

        $this->get(route('community.show', $submission->slug))
            ->assertOk()
            ->assertSee('Night market field notes')
            ->assertDontSee('<script>alert(1)</script>', false);

        $this->get(route('articles.index'))->assertOk()->assertSee('Night market field notes');
        $this->get(route('community.index'))->assertOk()->assertSee('Night market field notes');
        $this->get(route('category.show', $category->slug))->assertOk()->assertSee('Night market field notes');
    }

    public function test_approving_artwork_publishes_it_in_the_multimedia_library(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $submission = FanSubmission::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'type' => 'artwork',
            'title' => 'Lantern corridor sketch',
            'description' => 'Ink study of a lantern corridor, posted so the gallery has something to hang after review.',
            'status' => FanSubmission::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.submissions.status', $submission), [
                'status' => FanSubmission::STATUS_APPROVED,
            ])
            ->assertRedirect(route('admin.submissions.index'));

        $submission->refresh();

        $this->assertDatabaseHas('media', [
            'id' => $submission->published_media_id,
            'title' => 'Lantern corridor sketch',
            'media_type' => 'gallery',
            'status' => Media::STATUS_PUBLISHED,
        ]);

        $this->assertStringStartsWith(
            '/images/catalogue/',
            (string) Media::query()->findOrFail($submission->published_media_id)->thumbnail,
        );

        $this->get(route('media.index', ['type' => 'gallery']))->assertOk()->assertSee('Lantern corridor sketch');
        $this->get(route('media.show', Media::query()->findOrFail($submission->published_media_id)->slug))
            ->assertOk()
            ->assertSee('Lantern corridor sketch');
        $this->get(route('community.index'))->assertOk()->assertSee('Lantern corridor sketch');
    }

    public function test_rejecting_a_live_submission_removes_it_from_the_site(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $submission = FanSubmission::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'type' => 'article',
            'title' => 'Draft that should leave the site',
            'description' => 'This write-up is approved first and then rejected, so the public pages must drop it.',
            'status' => FanSubmission::STATUS_PENDING,
        ]);

        $this->actingAs($admin)->patch(route('admin.submissions.status', $submission), [
            'status' => FanSubmission::STATUS_APPROVED,
        ])->assertRedirect(route('admin.submissions.index'));

        $submission->refresh();
        $slug = $submission->slug;
        $articleSlug = Article::query()->findOrFail($submission->published_article_id)->slug;

        $this->actingAs($admin)->patch(route('admin.submissions.status', $submission), [
            'status' => FanSubmission::STATUS_REJECTED,
            'admin_note' => 'Needs a credit line.',
        ])->assertRedirect(route('admin.submissions.index'));

        $this->assertSame(Article::STATUS_ARCHIVED, Article::query()->findOrFail($submission->published_article_id)->status);
        $this->get(route('community.show', $slug))->assertNotFound();
        $this->get(route('articles.show', $articleSlug))->assertNotFound();
        $this->get(route('community.index'))->assertOk()->assertDontSee('Draft that should leave the site');
    }

    public function test_a_pending_submission_stays_off_the_community_page(): void
    {
        $submission = FanSubmission::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Still waiting in the queue',
            'description' => 'This note has not been approved, so visitors must not be able to open it.',
            'status' => FanSubmission::STATUS_PENDING,
        ]);

        $this->get(route('community.index'))->assertOk()->assertDontSee('Still waiting in the queue');
        $this->get(route('community.show', $submission->slug))->assertNotFound();
    }

    public function test_guest_cannot_submit_fan_content(): void
    {
        $this->post(route('submissions.store'), [
            'title' => 'A guest submission attempt',
            'type' => 'artwork',
            'description' => 'Guests are not allowed to queue content for review, so this must never be stored in the database.',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('fan_submissions', 0);
    }

    public function test_detail_page_visit_records_a_view_row_within_the_ip_column_limit(): void
    {
        $before = (int) $this->content->views_count;

        $this->get(route('content.show', $this->content))->assertOk();

        $this->assertDatabaseCount('views', 1);
        $this->assertSame($before + 1, (int) $this->content->fresh()->views_count);

        $stored = ContentView::query()->firstOrFail()->ip;

        // views.ip is a 45-character column (the longest IP literal); a value
        // longer than that is rejected by strict MySQL, silently losing the row.
        $this->assertNotNull($stored);
        $this->assertLessThanOrEqual(45, strlen($stored));
    }
}
