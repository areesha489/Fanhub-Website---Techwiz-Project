<?php

namespace Database\Seeders;

use App\Models\ChatbotKnowledge;
use Illuminate\Database\Seeder;

class ChatbotKnowledgeSeeder extends Seeder
{
    /**
     * The assistant's knowledge base: 38 active FAQs tagged by topic so the
     * matcher can answer without any AI provider configured.
     */
    public function run(): void
    {
        $rows = [
            // ---- Platform ----
            ['question' => 'What is Fan Hub+?',
                'answer' => 'Fan Hub+ is a fandom platform covering eight buckets — Anime, Gaming, Movies, TV Shows, K-Pop, Comics, Manga and Cosplay. The home page collects trending titles, featured characters, latest articles, multimedia, events, releases and merchandise, and Explore lets you filter everything by category.',
                'keywords' => 'about, what is, fan hub, platform, introduction, site',
                'category' => 'platform'],

            ['question' => 'How do I browse by category?',
                'answer' => 'Open Explore or click any category tile on the home page. Each category page lists that bucket\'s titles with counts for characters, events, articles, merchandise and releases, and the sidebar keeps every other category one click away.',
                'keywords' => 'category, categories, explore, browse, sidebar, anime, gaming',
                'category' => 'platform'],

            ['question' => 'How does search work?',
                'answer' => 'The search bar in the header matches titles, characters, articles, multimedia, events, merchandise and releases at once, grouped by type. It ignores punctuation, so "spider verse" and "spider-verse" find the same things.',
                'keywords' => 'search, find, query, lookup, header, filter results',
                'category' => 'platform'],

            ['question' => 'What do the sort and filter controls do?',
                'answer' => 'Every listing has a filter panel: sort by popularity, rating, views or newest, and narrow by category, genre, type or status. Filters combine, and the page keeps your choices in the URL so you can share or bookmark the exact view.',
                'keywords' => 'sort, filter, order, newest, rating, views, url',
                'category' => 'platform'],

            ['question' => 'Is there a mobile app?',
                'answer' => 'No separate app — Fan Hub+ is a responsive site, so the same pages work in a phone browser with the bottom navigation appearing on small screens. Install it to your home screen if you want an icon.',
                'keywords' => 'mobile, app, ios, android, phone, responsive, install',
                'category' => 'platform'],

            ['question' => 'How often is content added?',
                'answer' => 'Articles and multimedia drop through the week, the release calendar updates as dates are confirmed, and events appear as organisers announce them. Drafts exist in the catalogue too — they only show once published.',
                'keywords' => 'update, new, schedule, frequency, when, add content',
                'category' => 'platform'],

            ['question' => 'Who do I contact for help or feedback?',
                'answer' => 'Use the feedback form in the footer — pick bug, suggestion or query and it lands in the moderation inbox. For anything account-related, the contact email is listed in the settings the team publishes on the about page.',
                'keywords' => 'contact, help, support, feedback, email, report problem',
                'category' => 'platform'],

            ['question' => 'Can I talk to a human instead of the assistant?',
                'answer' => 'Yes — send a query through the feedback form and a moderator replies to your notifications. The assistant is fastest for "how does this work" questions; people handle account and moderation issues.',
                'keywords' => 'human, person, staff, agent, moderator, real support',
                'category' => 'platform'],

            // ---- Account ----
            ['question' => 'How do I create an account?',
                'answer' => 'Select Sign up in the header, give a name, email and a password of at least eight characters, and confirm it. You are signed in immediately with a personal dashboard, and a verification email arrives shortly after.',
                'keywords' => 'register, sign up, create account, join, password, email',
                'category' => 'account'],

            ['question' => 'I forgot my password — what now?',
                'answer' => 'Choose "Forgot password" on the sign-in page and enter your email. If the address exists, a reset link arrives; open it within the expiry window and choose a new password. Old sessions are signed out afterwards.',
                'keywords' => 'forgot, password, reset, login, cannot sign in, email link',
                'category' => 'account'],

            ['question' => 'Why was I signed out?',
                'answer' => 'Sessions end when the token expires or after you select Sign out, and changing your password invalidates every existing session. Sign in again — your bookmarks, ratings and preferences are untouched.',
                'keywords' => 'signed out, logout, session, expired, token, kicked out',
                'category' => 'account'],

            ['question' => 'How do I edit my profile?',
                'answer' => 'Open Profile to change your display name, email and bio, and pick favourite categories. Preferences nearby cover theme, font scale, reduced motion and notification toggles — everything saves as you change it.',
                'keywords' => 'profile, edit, name, bio, display name, avatar, settings',
                'category' => 'account'],

            ['question' => 'How do I change my theme or font size?',
                'answer' => 'Preferences has a dark/light theme toggle, a font scale from 75% to 150% and a reduced-motion switch. The choices stick to your account, so they apply again on your next visit.',
                'keywords' => 'theme, dark, light, font, size, accessibility, motion, preferences',
                'category' => 'account'],

            ['question' => 'How do I adjust notifications?',
                'answer' => 'Preferences splits notifications into email, content and event alerts plus an optional newsletter — toggle each independently. Unread items always collect in the notification centre regardless of the email switches.',
                'keywords' => 'notification, alerts, email, toggle, preferences, unread',
                'category' => 'account'],

            // ---- Privacy ----
            ['question' => 'What data does Fan Hub+ keep?',
                'answer' => 'Your name, email, bio, preferences and the things you choose to save — bookmarks, ratings, submissions and messages you send. Viewing activity is stored in aggregate to power trending lists.',
                'keywords' => 'privacy, data, gdpr, information, stored, collect',
                'category' => 'privacy'],

            ['question' => 'How do I delete my account?',
                'answer' => 'Send an account-deletion request through the feedback form and confirm when a moderator replies. Bookmarks, ratings and submissions are removed with the account; anonymised statistics may remain.',
                'keywords' => 'delete, account, remove, close, erasure, forget me',
                'category' => 'privacy'],

            // ---- Bookmarks ----
            ['question' => 'How do bookmarks work?',
                'answer' => 'Select the bookmark icon on any card or detail page to save it — the icon fills in and the item appears in Dashboard → Bookmarks instantly. Bookmarks are private to your account and can carry a personal note.',
                'keywords' => 'bookmark, save, favourite, note, icon, dashboard',
                'category' => 'bookmarks'],

            ['question' => 'Where do I find my saved items?',
                'answer' => 'The Dashboard shows your bookmark count plus the most recent saves, and the Bookmarks page lists everything with a type filter. Guests are prompted to sign in — bookmarks only exist on an account.',
                'keywords' => 'saved, bookmarks, dashboard, library, list, where',
                'category' => 'bookmarks'],

            ['question' => 'Can I add a note to a bookmark?',
                'answer' => 'Yes — create the bookmark with a note, or edit the note later from your bookmarks list. Notes are plain text up to a thousand characters and stay private to you.',
                'keywords' => 'note, annotate, comment, personal, bookmark, edit',
                'category' => 'bookmarks'],

            ['question' => 'How do I remove a bookmark?',
                'answer' => 'Select the filled bookmark icon again, or use Remove on the bookmarks list. The item disappears immediately and the activity log records the removal.',
                'keywords' => 'remove, delete, unsave, unbookmark, bookmark icon',
                'category' => 'bookmarks'],

            // ---- Content ----
            ['question' => 'What do the content ratings mean?',
                'answer' => 'Ratings run from 1 to 10 and come from member votes, shown to one decimal place on cards and detail pages. Averages update as new ratings arrive — no editorial override.',
                'keywords' => 'rating, score, out of 10, vote, average, review',
                'category' => 'content'],

            ['question' => 'Why does a title say Draft?',
                'answer' => 'Drafts are works in progress — written but not yet published, so they appear only in admin listings and never in public feeds. Once published they join the catalogue like everything else.',
                'keywords' => 'draft, unpublished, status, hidden, preview, admin',
                'category' => 'content'],

            ['question' => 'Which fandoms are covered?',
                'answer' => 'Eight categories: Anime, Gaming, Movies, TV Shows, K-Pop, Comics, Manga and Cosplay. Each one carries its own catalogue of titles, characters, articles, events and releases.',
                'keywords' => 'fandoms, categories, covered, anime, gaming, kpop, comics, manga, cosplay',
                'category' => 'content'],

            ['question' => 'What is on a title detail page?',
                'answer' => 'The description and synopsis, category and genre, rating, views and release date, related characters with their roles, related titles, a gallery, and matching multimedia — everything needed to decide what to watch, read or play next.',
                'keywords' => 'detail, page, synopsis, characters, related, gallery',
                'category' => 'content'],

            // ---- Events ----
            ['question' => 'What events are coming up?',
                'answer' => 'Events lists every upcoming convention, expo, meetup, tournament, screening, workshop and online event, sorted so the soonest dates come first. Each entry shows venue, city, timezone-friendly time and a ticket link.',
                'keywords' => 'events, upcoming, calendar, convention, expo, meetup, soon',
                'category' => 'events'],

            ['question' => 'Do tickets link to official sellers?',
                'answer' => 'Yes — every event page carries a ticket button that opens the organiser\'s sales page in a new tab. Fan Hub+ never processes payments itself; the link is the handoff.',
                'keywords' => 'ticket, tickets, buy, purchase, link, official, seller',
                'category' => 'events'],

            ['question' => 'Can I filter events by city?',
                'answer' => 'Yes — the events page filters by city, type and status, and results carrying coordinates show their venue on a map. Switch the status filter to Completed to browse past gatherings.',
                'keywords' => 'city, location, venue, near, map, coordinates, filter',
                'category' => 'events'],

            ['question' => 'What does a completed event mean?',
                'answer' => 'The dates have passed — the page stays up as an archive with its venue details and coverage. Completed events sort below anything still upcoming.',
                'keywords' => 'completed, past, finished, archive, happened, ended',
                'category' => 'events'],

            ['question' => 'An event I wanted is cancelled — what now?',
                'answer' => 'Cancelled events keep their page but are marked clearly and sorted out of the default upcoming view. Ticket refunds are handled by the organiser through the original purchase link.',
                'keywords' => 'cancelled, canceled, refund, called off, event',
                'category' => 'events'],

            // ---- Releases ----
            ['question' => 'Where is the release calendar?',
                'answer' => 'Releases collects everything dated — anime arcs, games, films, seasons, comics and merchandise drops — with upcoming dates first. Filter by type to follow only the categories you care about.',
                'keywords' => 'release, calendar, upcoming, launch, date, when',
                'category' => 'releases'],

            ['question' => 'What does a delayed release mean?',
                'answer' => 'The date moved. Delayed entries keep the old context with the revised date and a note about why — usually production or certification — so you are not waiting on a date that already changed.',
                'keywords' => 'delayed, postponed, moved, delay, new date',
                'category' => 'releases'],

            ['question' => 'How do I see what already came out?',
                'answer' => 'Switch the releases status filter to Released. Those entries drop below upcoming dates on the default view, so the calendar always leads with what is still ahead.',
                'keywords' => 'released, out now, available, past, already, published',
                'category' => 'releases'],

            // ---- Merchandise ----
            ['question' => 'Does Fan Hub+ sell merchandise?',
                'answer' => 'No — Fan Hub+ only showcases merchandise. We never sell, ship or take payment for products, and there is no checkout anywhere on the site. Every product page links to the official store instead.',
                'keywords' => 'sell, buy, shop, store, checkout, purchase, merchandise policy',
                'category' => 'merchandise'],

            ['question' => 'Why can\'t I add a product to a cart?',
                'answer' => 'Because there is no cart — the catalogue is a gallery of fandom products with prices and release dates for reference. Check the product page for the official store link and current availability.',
                'keywords' => 'cart, checkout, order, payment, add to cart, buy product',
                'category' => 'merchandise'],

            ['question' => 'What does the upcoming flag mean on products?',
                'answer' => 'The item is announced but not yet available — the displayed date is the expected release. Once it ships, the flag clears and the price reflects what stores are asking.',
                'keywords' => 'upcoming, preorder, release date, product, not out, announced',
                'category' => 'merchandise'],

            ['question' => 'A product shows no price — is it free?',
                'answer' => 'No, it means we do not have a reliable figure — limited runs and archive pieces often ship without a public price. Treat it as "ask the official store" rather than free.',
                'keywords' => 'price, null, free, no price, missing, cost',
                'category' => 'merchandise'],

            // ---- Ratings & engagement ----
            ['question' => 'How do I rate something?',
                'answer' => 'Open any title, character, article or media page and use the rating control — values run 1 to 10. Your vote is counted once, and you can change it later by rating again.',
                'keywords' => 'rate, rating, stars, vote, score, 1 to 10',
                'category' => 'engagement'],

            ['question' => 'Where do my notifications live?',
                'answer' => 'The bell in the header shows unread counts, and the notification centre lists everything newest first. Mark items as read from the list — read state syncs across devices.',
                'keywords' => 'notifications, bell, unread, centre, center, alerts list',
                'category' => 'engagement'],

            // ---- Submissions ----
            ['question' => 'How do I submit fan content?',
                'answer' => 'Open Submit content, choose a type (article, artwork, video, cosplay or other), add a title and description, optionally link or upload an image, and send. Everything enters the moderation queue as pending.',
                'keywords' => 'submit, fan content, contribute, upload, artwork, cosplay, send',
                'category' => 'submissions'],

            ['question' => 'How long does a submission take to review?',
                'answer' => 'Most submissions are reviewed within a few days. You will get a notification when the status changes to approved or rejected, and the moderator\'s note explains any changes they asked for.',
                'keywords' => 'review, pending, approve, moderator, how long, wait',
                'category' => 'submissions'],

            ['question' => 'Why was my submission rejected?',
                'answer' => 'The rejection note in your notifications gives the reason — usually missing credits, an off-topic category or an incomplete description. Fix the noted points and resubmit; nothing is penalised.',
                'keywords' => 'rejected, denied, feedback, admin note, refused, resubmit',
                'category' => 'submissions'],

            ['question' => 'Can I edit a submission after sending it?',
                'answer' => 'While it is pending you can withdraw and resend with corrections. Once approved or rejected the record is locked — send a fresh submission with the updated material instead.',
                'keywords' => 'edit, modify, withdraw, change, resubmit, pending',
                'category' => 'submissions'],
        ];

        foreach ($rows as $row) {
            ChatbotKnowledge::query()->updateOrCreate(
                ['question' => $row['question']],
                [
                    'answer' => $row['answer'],
                    'keywords' => $row['keywords'],
                    'category' => $row['category'],
                    'is_active' => true,
                ]
            );
        }
    }
}
