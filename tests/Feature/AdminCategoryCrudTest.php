<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * One full admin CRUD cycle over a resource, plus its validation guard.
 */
class AdminCategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_can_create_read_update_and_delete_a_category(): void
    {
        $this->actingAs($this->admin);

        // Create
        $this->post(route('admin.categories.store'), [
            'name' => 'Neon Genesis Fandoms',
            'icon' => 'bi-rocket-takeoff',
            'description' => 'A test category for mecha fans.',
            'accent' => '#22D3EE',
            'position' => 12,
        ])->assertRedirect();

        $category = Category::query()->where('slug', 'neon-genesis-fandoms')->firstOrFail();

        $this->assertSame('bi-rocket-takeoff', $category->icon);
        $this->assertSame('A test category for mecha fans.', $category->description);

        // Read
        $this->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('Neon Genesis Fandoms');

        // Update
        $this->put(route('admin.categories.update', $category), [
            'name' => 'Neon Genesis Archives',
            'icon' => 'bi-rocket-takeoff',
            'description' => 'Renamed during the test cycle.',
            'accent' => '#22D3EE',
            'position' => 12,
        ])->assertRedirect(route('admin.categories.index'));

        $category->refresh();
        $this->assertSame('Neon Genesis Archives', $category->name);
        $this->assertSame('neon-genesis-archives', $category->slug);

        // Delete
        $this->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_category_store_rejects_an_invalid_accent_colour(): void
    {
        $before = Category::query()->count();

        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Broken Palette',
                'accent' => 'not-a-colour',
            ])
            ->assertSessionHasErrors('accent');

        $this->assertSame($before, Category::query()->count());
    }
}
