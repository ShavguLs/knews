<?php

namespace Tests\Feature;

use App\Models\News;
use App\Services\TelegramBot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramBotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.telegram' => [
            'bot_token' => 'TESTTOKEN',
            'admin_chat_ids' => ['111'],
            'default_category' => 'Dispatch',
            'default_author' => 'Desk',
        ]]);

        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true, 'result' => []], 200),
        ]);
    }

    private function update(int $chatId, string $text): array
    {
        return [
            'update_id' => 1,
            'message' => [
                'chat' => ['id' => $chatId],
                'text' => $text,
            ],
        ];
    }

    public function test_authorized_chat_can_publish_news(): void
    {
        app(TelegramBot::class)->handleUpdate(
            $this->update(111, '/new Telegram works | Tech | Desk | Posted from my phone')
        );

        $this->assertDatabaseHas('news', [
            'title' => 'Telegram works',
            'category' => 'Tech',
            'author' => 'Desk',
            'status' => 'done',
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/sendMessage')
                && $request['chat_id'] === 111
                && str_contains($request['text'], 'Published');
        });
    }

    public function test_two_field_new_uses_config_defaults(): void
    {
        app(TelegramBot::class)->handleUpdate(
            $this->update(111, '/new Quick post | Just the body')
        );

        $this->assertDatabaseHas('news', [
            'title' => 'Quick post',
            'category' => 'Dispatch',
            'author' => 'Desk',
            'status' => 'done',
        ]);
    }

    public function test_unauthorized_chat_is_ignored(): void
    {
        app(TelegramBot::class)->handleUpdate(
            $this->update(999, '/new Sneaky | Tech | Nobody | Should not exist')
        );

        $this->assertDatabaseMissing('news', ['title' => 'Sneaky']);
        Http::assertNothingSent();
    }

    public function test_publish_and_unpublish_toggle_status(): void
    {
        $news = News::factory()->create(['status' => 'pending']);

        $bot = app(TelegramBot::class);

        $bot->handleUpdate($this->update(111, "/publish {$news->id}"));
        $this->assertDatabaseHas('news', ['id' => $news->id, 'status' => 'done']);

        $bot->handleUpdate($this->update(111, "/unpublish {$news->id}"));
        $this->assertDatabaseHas('news', ['id' => $news->id, 'status' => 'pending']);
    }

    public function test_hero_command_clears_previous_hero(): void
    {
        $oldHero = News::factory()->create(['status' => 'done', 'is_hero' => true]);
        $target = News::factory()->create(['status' => 'done', 'is_hero' => false]);

        app(TelegramBot::class)->handleUpdate($this->update(111, "/hero {$target->id}"));

        $this->assertEquals(0, $oldHero->fresh()->is_hero);
        $this->assertEquals(1, $target->fresh()->is_hero);
    }

    public function test_delete_command_removes_post(): void
    {
        $news = News::factory()->create();

        app(TelegramBot::class)->handleUpdate($this->update(111, "/delete {$news->id}"));

        $this->assertDatabaseMissing('news', ['id' => $news->id]);
    }

    public function test_invalid_new_reports_validation_error(): void
    {
        app(TelegramBot::class)->handleUpdate($this->update(111, '/new'));

        $this->assertDatabaseCount('news', 0);

        Http::assertSent(function ($request) {
            return str_contains($request['text'], 'Usage:');
        });
    }
}
