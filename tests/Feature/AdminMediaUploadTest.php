<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Admin media form: uploaded videos play on the public page.
 */
class AdminMediaUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_a_video_file_and_the_page_plays_it(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.media.create'))
            ->assertOk()
            ->assertSee('Video file')
            ->assertSee('Featured media');

        $this->actingAs($admin)
            ->post(route('admin.media.store'), [
                'title' => 'Manual Clip',
                'category_id' => $category->id,
                'media_type' => 'video',
                'status' => 'published',
                'video' => UploadedFile::fake()->create('clip.mp4', 120, 'video/mp4'),
            ])
            ->assertRedirect();

        $media = Media::query()->where('title', 'Manual Clip')->firstOrFail();

        $this->assertIsString($media->url);
        $this->assertStringStartsWith('media/videos/', $media->url);
        Storage::disk('public')->assertExists($media->url);

        $this->get(route('media.show', $media->slug))
            ->assertOk()
            ->assertSee('<video', false)
            ->assertSee($media->url, false);
    }
}
