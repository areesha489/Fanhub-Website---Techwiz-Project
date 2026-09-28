<?php

namespace Database\Seeders;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Database\Seeder;

class FeedbackSeeder extends Seeder
{
    /**
     * Seven feedback threads covering every type and status, three of them
     * already answered by the moderation team.
     */
    public function run(): void
    {
        $users = User::query()->pluck('id', 'email');
        $moderatorId = $users['moderator@fanhubplus.test'] ?? null;

        $rows = [
            ['from' => 'sofia@fanhubplus.test', 'type' => 'bug',
                'subject' => 'Search drops accents on character names',
                'message' => 'Searching for an accented name returns nothing until the accent is removed. Try any character with an umlaut or an acute and the result count sits at zero until you retype it plainly.',
                'status' => Feedback::STATUS_OPEN, 'response' => null],

            ['from' => 'marcus@fanhubplus.test', 'type' => 'suggestion',
                'subject' => 'Theme toggle in the header',
                'message' => 'The theme switch only lives in settings right now. A small sun/moon control in the header would save a page load every time the room lighting changes.',
                'status' => Feedback::STATUS_OPEN, 'response' => null],

            ['from' => 'noah@fanhubplus.test', 'type' => 'query',
                'subject' => 'How do I change the email on my account?',
                'message' => 'I signed up with an address I no longer use and cannot find where to update it. Is there a verification step involved once the new address is saved?',
                'status' => Feedback::STATUS_IN_REVIEW, 'response' => null],

            ['from' => 'priya@fanhubplus.test', 'type' => 'bug',
                'subject' => 'Bookmark note not saving on first attempt',
                'message' => 'Adding a note when creating a bookmark sometimes does not stick until I edit the bookmark afterwards. Reproduced on two browsers, both times on the first save of a session.',
                'status' => Feedback::STATUS_RESOLVED,
                'response' => 'Fixed in the last deploy — note payloads are now merged into the create call. Thanks for the reproduction steps, they made the cause obvious.'],

            ['from' => 'yuki@fanhubplus.test', 'type' => 'suggestion',
                'subject' => 'Export bookmarks as CSV',
                'message' => 'I keep my watch list in a spreadsheet too. An export button on the bookmarks page with title, category and note columns would save me an hour a month.',
                'status' => Feedback::STATUS_CLOSED,
                'response' => 'Logged for the next roadmap cycle. The team likes the idea; nothing scheduled yet, so we are closing this thread to keep the inbox current.'],

            ['from' => 'lena@fanhubplus.test', 'type' => 'query',
                'subject' => 'Is there a press kit for conventions?',
                'message' => 'Our meetup wants to list Fan Hub+ as a community partner. Is there a logo pack and a short description we can use on flyers and event pages?',
                'status' => Feedback::STATUS_RESOLVED,
                'response' => 'Yes — logos and a 40-word blurb are available from the about page footer link. Feel free to use them on print and social materials.'],

            ['from' => 'marcus@fanhubplus.test', 'type' => 'bug',
                'subject' => 'Video thumbnail flickers when scrolling',
                'message' => 'On the multimedia page the thumbnails flicker briefly as they enter the viewport, mainly on a high refresh rate display. It is subtle but noticeable in a long list.',
                'status' => Feedback::STATUS_IN_REVIEW, 'response' => null],
        ];

        foreach ($rows as $row) {
            Feedback::query()->firstOrCreate(
                [
                    'user_id' => $users[$row['from']],
                    'subject' => $row['subject'],
                ],
                [
                    'type' => $row['type'],
                    'message' => $row['message'],
                    'status' => $row['status'],
                    'admin_response' => $row['response'],
                ]
            );
        }
    }
}
