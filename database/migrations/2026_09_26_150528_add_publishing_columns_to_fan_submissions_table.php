<?php

use App\Models\FanSubmission;
use App\Support\SubmissionPublisher;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fan_submissions', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title');
            $table->foreignId('published_article_id')->nullable()->after('reviewed_at')->constrained('articles')->nullOnDelete();
            $table->foreignId('published_media_id')->nullable()->after('published_article_id')->constrained('media')->nullOnDelete();
        });

        $used = [];

        DB::table('fan_submissions')->orderBy('id')->select(['id', 'title', 'slug'])->get()->each(function (object $row) use (&$used): void {
            if (is_string($row->slug) && $row->slug !== '') {
                $used[$row->slug] = true;

                return;
            }

            $base = Str::slug((string) $row->title);
            $base = $base !== '' ? $base : 'submission-'.$row->id;
            $slug = $base;
            $suffix = 2;

            while (isset($used[$slug])) {
                $slug = $base.'-'.$suffix++;
            }

            $used[$slug] = true;
            DB::table('fan_submissions')->where('id', $row->id)->update(['slug' => $slug]);
        });

        Schema::table('fan_submissions', function (Blueprint $table) {
            $table->unique('slug');
        });

        FanSubmission::query()
            ->where('status', FanSubmission::STATUS_APPROVED)
            ->orderBy('id')
            ->each(function (FanSubmission $submission): void {
                app(SubmissionPublisher::class)->sync($submission);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fan_submissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('published_media_id');
            $table->dropConstrainedForeignId('published_article_id');
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
