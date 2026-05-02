<?php

namespace Tests\Feature;

use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNewsAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_news_index(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_guest_can_view_single_news_article(): void
    {
        $news = News::factory()->create(['status' => 'done']);

        $response = $this->get(route('news.show', $news));
        $response->assertStatus(200);
    }

    public function test_guest_cannot_access_admin_news_panel(): void
    {
        $response = $this->get('/admin/news');
        $response->assertRedirect(route('admin.login'));
    }

    public function test_guest_cannot_access_admin_news_create(): void
    {
        $response = $this->get('/admin/news/create');
        $response->assertRedirect(route('admin.login'));
    }

    public function test_guest_cannot_submit_post_to_admin_news(): void
    {
        $response = $this->post('/admin/news', [
            'title' => 'Test',
            'category' => 'Test',
            'author' => 'Test',
            'body' => 'Test body',
        ]);
        $response->assertRedirect(route('admin.login'));
    }

    public function test_non_admin_user_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get('/admin/news');
        $response->assertStatus(403);
    }

    public function test_admin_user_can_access_admin_panel(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/admin/news');
        $response->assertStatus(200);
    }

    public function test_admin_user_can_create_news(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post('/admin/news', [
            'title' => 'Test News',
            'category' => 'Test Category',
            'author' => 'Test Author',
            'body' => 'Test body content',
            'status' => 'done',
        ]);

        $response->assertRedirect(route('admin.news.index'));
        $this->assertDatabaseHas('news', ['title' => 'Test News']);
    }

    public function test_admin_user_can_update_news(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $news = News::factory()->create();

        $response = $this->actingAs($admin)->put("/admin/news/{$news->id}", [
            'title' => 'Updated Title',
            'category' => $news->category,
            'author' => $news->author,
            'body' => $news->body,
            'status' => 'done',
        ]);

        $response->assertRedirect(route('admin.news.index'));
        $this->assertDatabaseHas('news', ['id' => $news->id, 'title' => 'Updated Title']);
    }

    public function test_admin_user_can_delete_news(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $news = News::factory()->create();

        $response = $this->actingAs($admin)->delete("/admin/news/{$news->id}");

        $response->assertRedirect(route('admin.news.index'));
        $this->assertDatabaseMissing('news', ['id' => $news->id]);
    }

    public function test_public_news_index_shows_done_articles(): void
    {
        News::factory()->create(['status' => 'done', 'title' => 'Done Article']);
        News::factory()->create(['status' => 'pending', 'title' => 'Pending Article']);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Done Article');
        $response->assertDontSee('Pending Article');
    }

    public function test_public_news_index_does_not_show_pending_articles(): void
    {
        News::factory()->create(['status' => 'pending', 'title' => 'Hidden Article']);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertDontSee('Hidden Article');
    }

    public function test_guest_gets_404_when_opening_pending_article_directly(): void
    {
        $news = News::factory()->create(['status' => 'pending']);

        $response = $this->get(route('news.show', $news));
        $response->assertStatus(404);
    }

    public function test_admin_can_preview_pending_article(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $news = News::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($admin)->get(route('news.show', $news));
        $response->assertStatus(200);
    }

    public function test_admin_can_create_news_with_pending_status(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post('/admin/news', [
            'title' => 'Pending News',
            'category' => 'Test Category',
            'author' => 'Test Author',
            'body' => 'Test body content',
            'status' => 'pending',
        ]);

        $response->assertRedirect(route('admin.news.index'));
        $this->assertDatabaseHas('news', ['title' => 'Pending News', 'status' => 'pending']);
    }

    public function test_admin_can_update_news_from_pending_to_done(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $news = News::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($admin)->put("/admin/news/{$news->id}", [
            'title' => $news->title,
            'category' => $news->category,
            'author' => $news->author,
            'body' => $news->body,
            'status' => 'done',
        ]);

        $response->assertRedirect(route('admin.news.index'));
        $this->assertDatabaseHas('news', ['id' => $news->id, 'status' => 'done']);
    }

    public function test_admin_panel_lists_both_pending_and_done_articles(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        News::factory()->create(['status' => 'done', 'title' => 'Done Article']);
        News::factory()->create(['status' => 'pending', 'title' => 'Pending Article']);

        $response = $this->actingAs($admin)->get('/admin/news');
        $response->assertStatus(200);
        $response->assertSee('Done Article');
        $response->assertSee('Pending Article');
    }

    public function test_hero_article_shows_on_homepage_when_done(): void
    {
        News::factory()->create(['status' => 'done', 'title' => 'Older Article', 'created_at' => now()->subDay()]);
        $hero = News::factory()->create(['status' => 'done', 'title' => 'Hero Article', 'is_hero' => true]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Hero Article');
    }

    public function test_homepage_falls_back_to_latest_done_when_no_hero(): void
    {
        $older = News::factory()->create(['status' => 'done', 'title' => 'Older Article', 'created_at' => now()->subDay()]);
        $newer = News::factory()->create(['status' => 'done', 'title' => 'Newer Article']);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Newer Article');
    }

    public function test_pending_hero_does_not_appear_publicly(): void
    {
        News::factory()->create(['status' => 'pending', 'title' => 'Pending Hero', 'is_hero' => true]);
        News::factory()->create(['status' => 'done', 'title' => 'Fallback Article']);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertDontSee('Pending Hero');
        $response->assertSee('Fallback Article');
    }

    public function test_setting_new_hero_clears_previous_hero(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $oldHero = News::factory()->create(['status' => 'done', 'title' => 'Old Hero', 'is_hero' => true]);
        $newHero = News::factory()->create(['status' => 'done', 'title' => 'New Hero', 'is_hero' => false]);

        $this->actingAs($admin)->put("/admin/news/{$newHero->id}", [
            'title' => $newHero->title,
            'category' => $newHero->category,
            'author' => $newHero->author,
            'body' => $newHero->body,
            'status' => 'done',
            'is_hero' => '1',
        ]);

        $this->assertEquals(0, $oldHero->fresh()->is_hero);
        $this->assertEquals(1, $newHero->fresh()->is_hero);
    }

    public function test_creating_article_as_hero_clears_previous_hero(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $oldHero = News::factory()->create(['status' => 'done', 'title' => 'Old Hero', 'is_hero' => true]);

        $this->actingAs($admin)->post('/admin/news', [
            'title' => 'New Hero',
            'category' => 'Test',
            'author' => 'Test Author',
            'body' => 'Test body',
            'status' => 'done',
            'is_hero' => '1',
        ]);

        $this->assertEquals(0, $oldHero->fresh()->is_hero);
        $this->assertDatabaseHas('news', ['title' => 'New Hero', 'is_hero' => 1]);
    }

    public function test_dispatches_exclude_hero_article(): void
    {
        $hero = News::factory()->create(['status' => 'done', 'title' => 'Hero Article', 'is_hero' => true]);
        $other = News::factory()->create(['status' => 'done', 'title' => 'Other Article']);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Hero Article');
        $response->assertSee('Other Article');
    }

    public function test_search_filters_dispatches_by_title(): void
    {
        $hero = News::factory()->create(['status' => 'done', 'title' => 'Hero Article', 'is_hero' => true]);
        News::factory()->create(['status' => 'done', 'title' => 'Match Found Here']);
        News::factory()->create(['status' => 'done', 'title' => 'Unrelated Article']);

        $response = $this->get('/?search=Match');
        $response->assertSee('Match Found Here');
        $response->assertDontSee('Unrelated Article');
        $response->assertSee('Hero Article');
    }

    public function test_search_does_not_affect_hero(): void
    {
        $hero = News::factory()->create(['status' => 'done', 'title' => 'Hero Article', 'is_hero' => true]);
        News::factory()->create(['status' => 'done', 'title' => 'Something Else']);

        $response = $this->get('/?search=Hero');
        $response->assertSee('Hero Article');
    }

    public function test_pending_articles_not_in_search_results(): void
    {
        News::factory()->create(['status' => 'done', 'title' => 'Done Article', 'is_hero' => true, 'body' => 'Unique XYZ']);
        News::factory()->create(['status' => 'pending', 'title' => 'Pending Article', 'body' => 'Unique ABC']);

        $response = $this->get('/?search=Unique');
        $response->assertDontSee('Pending Article');
    }
}