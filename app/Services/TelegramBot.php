<?php

namespace App\Services;

use App\Models\News;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TelegramBot
{
    public function sendMessage(int $chatId, string $text): void
    {
        Http::timeout(10)->post($this->apiUrl('sendMessage'), [
            'chat_id' => $chatId,
            'text' => $text,
        ]);
    }

    public function getUpdates(int $offset): array
    {
        $response = Http::timeout(35)->get($this->apiUrl('getUpdates'), [
            'offset' => $offset,
            'timeout' => 30,
        ]);

        if (! $response->successful()) {
            return [];
        }

        return $response->json('result', []);
    }

    public function handleUpdate(array $update): void
    {
        $message = $update['message'] ?? null;

        if (! $message) {
            return;
        }

        $chatId = $message['chat']['id'] ?? null;

        if ($chatId === null || ! $this->isAuthorized($chatId)) {
            return;
        }

        try {
            $this->dispatch($message, (int) $chatId);
        } catch (\Throwable $e) {
            $this->sendMessage((int) $chatId, 'Error: ' . $e->getMessage());
        }
    }

    private function dispatch(array $message, int $chatId): void
    {
        $text = trim($message['text'] ?? '');

        if ($text === '' || ! str_starts_with($text, '/')) {
            return;
        }

        [$command, $argument] = $this->parseCommand($text);

        $reply = match ($command) {
            'start', 'help' => $this->helpText(),
            'new' => $this->createNews($argument),
            'list' => $this->listNews(),
            'publish' => $this->setStatus($argument, 'done'),
            'unpublish' => $this->setStatus($argument, 'pending'),
            'hero' => $this->makeHero($argument),
            'delete' => $this->deleteNews($argument),
            default => 'Unknown command. Send /help for the list.',
        };

        $this->sendMessage($chatId, $reply);
    }

    private function parseCommand(string $text): array
    {
        $parts = preg_split('/\s+/', $text, 2);
        $command = strtolower(explode('@', ltrim($parts[0], '/'))[0]);

        return [$command, trim($parts[1] ?? '')];
    }

    private function createNews(string $argument): string
    {
        $usage = 'Usage: /new Title | Body   (or  /new Title | Category | Author | Body)';

        if ($argument === '') {
            return $usage;
        }

        $p = array_map('trim', explode('|', $argument));
        $count = count($p);

        $input = ['status' => 'done', 'is_hero' => false];

        if ($count >= 5) {
            [$input['title'], $input['category'], $input['author'], $input['body']] = [$p[0], $p[1], $p[2], $p[3]];
            $input['image_url'] = $p[4];
        } elseif ($count === 4) {
            [$input['title'], $input['category'], $input['author'], $input['body']] = $p;
        } elseif ($count === 3) {
            [$input['title'], $input['category'], $input['body']] = $p;
            $input['author'] = config('services.telegram.default_author');
        } elseif ($count === 2) {
            [$input['title'], $input['body']] = $p;
            $input['category'] = config('services.telegram.default_category');
            $input['author'] = config('services.telegram.default_author');
        } else {
            return $usage;
        }

        if (($input['image_url'] ?? null) === '') {
            unset($input['image_url']);
        }

        $validator = Validator::make($input, News::rules());

        if ($validator->fails()) {
            return "Could not publish:\n- " . implode("\n- ", $validator->errors()->all());
        }

        $news = News::create($validator->validated());

        return "Published #{$news->id}: {$news->title}\n" . route('news.show', $news);
    }

    private function listNews(): string
    {
        $items = News::orderBy('created_at', 'desc')
            ->limit(10)
            ->get(['id', 'title', 'status', 'is_hero']);

        if ($items->isEmpty()) {
            return 'No posts yet.';
        }

        return $items->map(function ($n) {
            $hero = $n->is_hero ? ' *' : '';

            return "#{$n->id} [{$n->status}]{$hero} {$n->title}";
        })->implode("\n");
    }

    private function setStatus(string $argument, string $status): string
    {
        $news = $this->findNews($argument);

        if (! $news) {
            $command = $status === 'done' ? '/publish' : '/unpublish';

            return "Usage: {$command} <id>  (post not found)";
        }

        $news->update(['status' => $status]);

        $verb = $status === 'done' ? 'Published' : 'Unpublished';

        return "{$verb} #{$news->id}: {$news->title}";
    }

    private function makeHero(string $argument): string
    {
        $news = $this->findNews($argument);

        if (! $news) {
            return 'Usage: /hero <id>  (post not found)';
        }

        $news->setAsHero();

        return "Hero set to #{$news->id}: {$news->title}";
    }

    private function deleteNews(string $argument): string
    {
        $news = $this->findNews($argument);

        if (! $news) {
            return 'Usage: /delete <id>  (post not found)';
        }

        if ($news->image_path) {
            Storage::disk('public')->delete($news->image_path);
        }

        $id = $news->id;
        $title = $news->title;

        $news->delete();

        return "Deleted #{$id}: {$title}";
    }

    private function findNews(string $argument): ?News
    {
        $id = (int) trim($argument);

        return $id > 0 ? News::find($id) : null;
    }

    private function helpText(): string
    {
        return implode("\n", [
            'knews bot commands:',
            '/new Title | Category | Author | Body — publish a post (Title | Body also works)',
            '/list — recent posts',
            '/publish <id> — make a post live',
            '/unpublish <id> — pull a post back to draft',
            '/hero <id> — set the homepage hero',
            '/delete <id> — delete a post',
        ]);
    }

    private function isAuthorized($chatId): bool
    {
        return in_array((string) $chatId, config('services.telegram.admin_chat_ids', []), true);
    }

    private function apiUrl(string $method): string
    {
        return 'https://api.telegram.org/bot' . config('services.telegram.bot_token') . '/' . $method;
    }
}
