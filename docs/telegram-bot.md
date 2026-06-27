# Telegram Admin Bot

The Telegram bot is a second admin channel for managing news. Instead of opening the web admin panel, an authorized operator can create, publish, feature, and delete articles by sending commands to a Telegram bot. The web admin panel remains the place to edit existing article text.

## How It Works

The integration has two parts plus a shared validation layer:

1. `App\Services\TelegramBot` holds all bot logic: talking to the Telegram Bot API, authorizing the sender, parsing commands, and reading or writing `News` records.
2. `App\Console\Commands\TelegramListen` (`telegram:listen`) long-polls Telegram for new messages and hands each one to the service.
3. `News::rules()` is the single source of validation. The web admin (`NewsController`) and the bot both validate against it, so the two channels cannot drift apart.

Telegram is only the transport. Messages flow in over the Telegram Bot API, the service turns them into ordinary Eloquent operations, and articles appear on the site exactly as if they were created through the web panel.

## Authorization

There is no Laravel session behind a Telegram message, so authorization is an allowlist of numeric chat IDs.

- Every incoming update's `message.chat.id` must appear in `services.telegram.admin_chat_ids`.
- Any message from a chat ID outside the allowlist is ignored silently, with no reply. This avoids revealing the bot to strangers.

This is the bot equivalent of `AdminMiddleware` for the web panel.

## Configuration

Settings live in `config/services.php` under the `telegram` key and are populated from environment variables:

```text
TELEGRAM_BOT_TOKEN        Bot token from @BotFather
TELEGRAM_ADMIN_CHAT_IDS   Comma-separated numeric chat IDs allowed to control the site
TELEGRAM_DEFAULT_CATEGORY Category used when /new omits one (default: Dispatch)
TELEGRAM_DEFAULT_AUTHOR   Author used when /new omits one (default: Desk)
```

`admin_chat_ids` is parsed from the comma-separated string into an array at config load.

### One-time setup

1. Create a bot with `@BotFather` and copy its token.
2. Get your numeric chat ID from `@userinfobot`, then send your new bot any message so Telegram will deliver updates to it.
3. Fill `TELEGRAM_BOT_TOKEN` and `TELEGRAM_ADMIN_CHAT_IDS` in `.env`.
4. Reload config:

```bash
php artisan config:clear
```

## Running the Listener

Start the long-poll listener and leave it running:

```bash
php artisan telegram:listen
```

The command fails fast with a clear message if the bot token or admin chat IDs are missing. It runs in the foreground; press `Ctrl+C` to stop. The bot writes directly to the database, so it works whether or not the web server is running.

## Commands

| Command | Action |
| --- | --- |
| `/help` or `/start` | List the available commands |
| `/new ...` | Create and immediately publish an article (`status = done`) |
| `/list` | Show the 10 most recent posts as `#id [status] title` (a `*` marks the hero) |
| `/publish <id>` | Set an article's status to `done` |
| `/unpublish <id>` | Set an article's status to `pending` |
| `/hero <id>` | Make an article the homepage hero, clearing any previous hero |
| `/delete <id>` | Delete an article and its uploaded image, if any |

Every command replies with a confirmation or an error message. Unknown commands return a hint to send `/help`.

### `/new` input formats

Fields are separated by a pipe (`|`). The number of fields decides how they map:

```text
/new Title | Body
/new Title | Category | Body
/new Title | Category | Author | Body
/new Title | Category | Author | Body | image_url
```

Missing `Category` or `Author` fall back to `TELEGRAM_DEFAULT_CATEGORY` and `TELEGRAM_DEFAULT_AUTHOR`. A trailing field is treated as `image_url`. The reply includes the new article's id and its public link.

Posts created through `/new` go live immediately. Use `/unpublish <id>` to pull one back to draft.

## Reused Application Logic

- Validation uses `News::rules()`, the same rules the web admin form applies (the form adds only the `image_file` upload rule on top).
- Hero selection uses `News::setAsHero()`, which clears `is_hero` on all other rows before setting it on the target. Both the bot and the web panel call it.
- Deletion mirrors `NewsController@destroy`, removing the stored image from the `public` disk when present.

## Out of Scope

- Editing an existing article's title or body. Use the web admin panel for edits.
- Uploading Telegram photos. Images are set by URL only; featured-image uploads stay on the web side.
- Webhook delivery. The listener uses long polling, which needs no public URL. The service's `handleUpdate()` is already webhook-ready, so a production webhook route can be added later without reworking the bot logic.
- Guided multi-message conversations. Commands are single-line and stateless.

## Operational Notes

- Long polling (`telegram:listen`) and a Telegram webhook are mutually exclusive. If a webhook was ever set on the bot, delete it before polling, or the listener receives nothing.
- A failed message never stops the listener. The service wraps command handling in a try/catch and replies with the error text.
- To allow more than one operator, add additional chat IDs to `TELEGRAM_ADMIN_CHAT_IDS`, comma-separated.

## Files

```text
app/Services/TelegramBot.php            Bot logic and Telegram API calls
app/Console/Commands/TelegramListen.php telegram:listen long-poll command
config/services.php                     telegram config block
app/Models/News.php                     News::rules() and setAsHero()
tests/Feature/TelegramBotTest.php       Feature tests for the bot
```

## Tests

`TelegramBotTest` covers the bot end to end without network access. It fakes the Telegram API with `Http::fake()` and calls `TelegramBot::handleUpdate()` directly, verifying that authorized chats can publish, that config defaults fill in, that unauthorized chats are ignored, and that publish, unpublish, hero, and delete behave correctly.

```bash
php artisan test --filter TelegramBotTest
```
