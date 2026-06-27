<?php

namespace App\Console\Commands;

use App\Services\TelegramBot;
use Illuminate\Console\Command;

class TelegramListen extends Command
{
    protected $signature = 'telegram:listen';

    protected $description = 'Long-poll Telegram for admin commands to manage news (local/dev; use a webhook in production)';

    public function handle(TelegramBot $bot): int
    {
        if (! config('services.telegram.bot_token')) {
            $this->error('TELEGRAM_BOT_TOKEN is not set. Add it to .env, then run php artisan config:clear.');

            return self::FAILURE;
        }

        if (empty(config('services.telegram.admin_chat_ids'))) {
            $this->error('TELEGRAM_ADMIN_CHAT_IDS is not set. Add your numeric chat id to .env, then run php artisan config:clear.');

            return self::FAILURE;
        }

        $this->info('Listening for Telegram updates. Press Ctrl+C to stop.');

        $offset = 0;

        while (true) {
            foreach ($bot->getUpdates($offset) as $update) {
                $offset = ($update['update_id'] ?? $offset) + 1;
                $bot->handleUpdate($update);
            }
        }

        return self::SUCCESS;
    }
}
