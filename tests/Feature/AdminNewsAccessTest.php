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
        $news = News::factory()->create();

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
}