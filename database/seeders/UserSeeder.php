<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Demo accounts: one administrator, one moderator and six members with
     * varied interests. All are verified except one deliberately unverified
     * demo account.
     */
    public function run(): void
    {
        $people = [
            [
                'name' => 'Ayesha Rahman',
                'email' => 'admin@fanhubplus.test',
                'password' => 'FanHub!Admin2026',
                'role' => User::ROLE_ADMIN,
                'verified' => true,
                'bio' => 'Platform administrator. Seasonal anime watcher, tabletop tinkerer and the person who merges everyone else\'s pull requests.',
            ],
            [
                'name' => 'Marco Devlin',
                'email' => 'moderator@fanhubplus.test',
                'password' => 'FanHub!Mod2026',
                'role' => User::ROLE_USER,
                'verified' => true,
                'bio' => 'Community moderator. Keeps the submissions queue tidy and the spoiler tags honest.',
            ],
            [
                'name' => 'Priya Nair',
                'email' => 'priya@fanhubplus.test',
                'password' => 'Password123!',
                'role' => User::ROLE_USER,
                'verified' => true,
                'bio' => 'Manga collector with a weakness for weekly cliffhangers. Ask me about panel composition.',
            ],
            [
                'name' => 'Marcus Hale',
                'email' => 'marcus@fanhubplus.test',
                'password' => 'Password123!',
                'role' => User::ROLE_USER,
                'verified' => true,
                'bio' => 'Co-op RPG veteran and speedrun spectator. Currently documenting every boss skip I can find.',
            ],
            [
                'name' => 'Lena Vogt',
                'email' => 'lena@fanhubplus.test',
                'password' => 'Password123!',
                'role' => User::ROLE_USER,
                'verified' => true,
                'bio' => 'Cosplay builder specialising in armour and weathering tricks. Convention floor regular since 2018.',
            ],
            [
                'name' => 'Yuki Tanaka',
                'email' => 'yuki@fanhubplus.test',
                'password' => 'Password123!',
                'role' => User::ROLE_USER,
                'verified' => true,
                'bio' => 'K-Pop fan projector and lightstick collector. If there is a comeback stage, I have a calendar entry for it.',
            ],
            [
                'name' => 'Noah Brooks',
                'email' => 'noah@fanhubplus.test',
                'password' => 'Password123!',
                'role' => User::ROLE_USER,
                'verified' => false,
                'bio' => 'Fresh account — still confirming my email. Horror comics and prestige TV are my thing.',
            ],
            [
                'name' => 'Sofia Almeida',
                'email' => 'sofia@fanhubplus.test',
                'password' => 'Password123!',
                'role' => User::ROLE_USER,
                'verified' => true,
                'bio' => 'Film festival volunteer who reviews everything with a score out of ten and a very long note.',
            ],
        ];

        foreach ($people as $person) {
            $user = User::query()->updateOrCreate(
                ['email' => $person['email']],
                [
                    'name' => $person['name'],
                    'password' => Hash::make($person['password']),
                    'role' => $person['role'],
                    'status' => User::STATUS_ACTIVE,
                    'bio' => $person['bio'],
                    'avatar' => null,
                    'email_verified_at' => $person['verified'] ? now()->subDays(30) : null,
                ]
            );

            UserPreference::query()->firstOrCreate(
                ['user_id' => $user->id],
                UserPreference::defaults()
            );
        }
    }
}
