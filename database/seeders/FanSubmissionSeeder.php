<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\FanSubmission;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class FanSubmissionSeeder extends Seeder
{
    /**
     * Seven member submissions across every type and status, each with a
     * matching activity entry; approvals/rejections also notify the author.
     */
    public function run(): void
    {
        $users = User::query()->pluck('id', 'email');
        $moderatorId = $users['moderator@fanhubplus.test'] ?? null;
        $categories = Category::query()->pluck('id', 'slug');

        $rows = [
            ['from' => 'lena@fanhubplus.test', 'type' => 'cosplay', 'cat' => 'cosplay',
                'title' => 'Foam Armor: Shoulder Build Log',
                'description' => 'A full build diary for a layered shoulder piece — patterning on paper first, heat shaping, sealing and the paint schedule that finally stopped chipping. Includes the mistakes worth repeating.',
                'url' => 'https://www.example.com/submissions/foam-armor-shoulder-build',
                'status' => FanSubmission::STATUS_APPROVED, 'note' => 'Featured on the cosplay category page — great process photos.'],

            ['from' => 'noah@fanhubplus.test', 'type' => 'artwork', 'cat' => 'tv-shows',
                'title' => 'Severance Hallway Poster (Fan Art)',
                'description' => 'A two-colour risograph poster of the east hallway — vanishing point slightly off on purpose, so the corridor never quite resolves. Prints available at the next meetup table.',
                'url' => 'https://www.example.com/submissions/severance-hallway-poster',
                'status' => FanSubmission::STATUS_APPROVED, 'note' => 'Approved and credited to the artist in the gallery caption.'],

            ['from' => 'priya@fanhubplus.test', 'type' => 'article', 'cat' => 'manga',
                'title' => 'Why I Re-Read One Piece Every Year',
                'description' => 'An essay on annual rereads — what changes when you already know the ending, and how a long series becomes a personal calendar. Roughly 1,400 words with chapter references.',
                'url' => null,
                'status' => FanSubmission::STATUS_PENDING, 'note' => null],

            ['from' => 'marcus@fanhubplus.test', 'type' => 'video', 'cat' => 'gaming',
                'title' => 'Elden Ring Route Teardown',
                'description' => 'A twelve-minute breakdown of an early-game route: what to grab first, which bosses to leave alone, and where the skips were patched out. Captions included.',
                'url' => 'https://www.example.com/submissions/elden-ring-route-teardown',
                'status' => FanSubmission::STATUS_PENDING, 'note' => null],

            ['from' => 'yuki@fanhubplus.test', 'type' => 'artwork', 'cat' => 'k-pop',
                'title' => 'Untitled Sketch Dump',
                'description' => 'A batch of stage-outfit sketches from the last comeback cycle, posted as one gallery.',
                'url' => 'https://www.example.com/submissions/untitled-sketch-dump',
                'status' => FanSubmission::STATUS_REJECTED,
                'note' => 'Please resubmit with artist credits on each piece and a higher-resolution export — the current images blur at full size.'],

            ['from' => 'sofia@fanhubplus.test', 'type' => 'other', 'cat' => 'cosplay',
                'title' => 'Convention Survival Checklist',
                'description' => 'A one-page checklist for first-time attendees: what to bring, what to leave home, how to pace panels and where the quiet corners usually are.',
                'url' => 'https://www.example.com/submissions/convention-survival-checklist',
                'status' => FanSubmission::STATUS_APPROVED, 'note' => 'Linked from the events FAQ — nice work.'],

            ['from' => 'yuki@fanhubplus.test', 'type' => 'cosplay', 'cat' => 'k-pop',
                'title' => 'Stage Outfit Construction Notes',
                'description' => 'Fabric choices, seam allowances and the rhinestone schedule for a stage replica, written for people sewing their first costume.',
                'url' => null,
                'status' => FanSubmission::STATUS_PENDING, 'note' => null],
        ];

        foreach ($rows as $row) {
            $submission = FanSubmission::query()->firstOrCreate(
                [
                    'user_id' => $users[$row['from']],
                    'title' => $row['title'],
                ],
                [
                    'type' => $row['type'],
                    'category_id' => $categories[$row['cat']] ?? null,
                    'description' => $row['description'],
                    'url' => $row['url'],
                    'image' => null,
                    'status' => $row['status'],
                    'admin_note' => $row['note'],
                    'reviewed_by' => $row['status'] === FanSubmission::STATUS_PENDING ? null : $moderatorId,
                    'reviewed_at' => $row['status'] === FanSubmission::STATUS_PENDING ? null : now()->subDays(4),
                ]
            );

            ActivityLog::query()->firstOrCreate(
                [
                    'user_id' => $users[$row['from']],
                    'action' => 'submission',
                    'subject_id' => $submission->id,
                ],
                [
                    'subject_type' => FanSubmission::class,
                    'description' => 'Submitted "'.$row['title'].'" for review.',
                    'ip_address' => fake()->ipv4(),
                ]
            );

            if ($row['status'] === FanSubmission::STATUS_APPROVED) {
                Notification::query()->firstOrCreate(
                    ['user_id' => $users[$row['from']], 'title' => 'Submission approved: '.$row['title']],
                    [
                        'body' => 'Your submission passed review and is now credited in the catalogue.',
                        'link' => '/submissions',
                        'read_at' => now()->subDays(3),
                    ]
                );
            }

            if ($row['status'] === FanSubmission::STATUS_REJECTED) {
                Notification::query()->firstOrCreate(
                    ['user_id' => $users[$row['from']], 'title' => 'Submission needs changes: '.$row['title']],
                    [
                        'body' => 'A moderator left notes on your submission — read them and resubmit when ready.',
                        'link' => '/submissions',
                        'read_at' => null,
                    ]
                );
            }
        }
    }
}
