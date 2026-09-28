<?php

namespace Database\Factories;

use App\Models\ChatbotKnowledge;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChatbotKnowledge>
 */
class ChatbotKnowledgeFactory extends Factory
{
    /**
     * Sample questions the assistant knows how to answer.
     *
     * @var list<string>
     */
    protected const QUESTIONS = [
        'How do I bookmark a title?',
        'Where can I change my theme?',
        'How are ratings calculated?',
        'Can I submit my own artwork?',
        'How do I reset my password?',
        'Where do I find upcoming releases?',
        'How do I follow a community event?',
        'What happens when my submission is rejected?',
    ];

    /**
     * Sample answers written for the demo knowledge base.
     *
     * @var list<string>
     */
    protected const ANSWERS = [
        'Open any detail page and use the bookmark button in the card header. Saved items appear under Bookmarks, where you can attach a personal note.',
        'Head to Settings and pick the theme, font scale and motion options you prefer. Changes apply instantly and stay with your account.',
        'Ratings run from one to ten and average across every member who rated the item. Your own rating can be changed at any time.',
        'Use the Submit content form, choose the matching type, add a short description and a link. Moderators review every entry before it goes live.',
        'Choose Forgot password on the login screen and follow the emailed link. The link stays valid for sixty minutes.',
        'The Releases page lists every upcoming launch with its date and status. Filter by type to narrow the calendar down.',
        'Open the event page and save it with the bookmark button so it shows up in your dashboard reminders.',
        'Rejections include a moderator note explaining what to adjust. You are welcome to send a revised version afterwards.',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question' => fake()->randomElement(self::QUESTIONS),
            'answer' => fake()->randomElement(self::ANSWERS),
            'keywords' => implode(', ', fake()->randomElements([
                'bookmark', 'save', 'favourite', 'theme', 'settings', 'rating', 'submit',
                'moderation', 'password', 'account', 'releases', 'calendar', 'event', 'reminders',
            ], 4)),
            'category' => fake()->randomElement(['account', 'content', 'events', 'preferences', 'community']),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the entry is hidden from the assistant.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
