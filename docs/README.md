# KNEWS Documentation

KNEWS is a Laravel 12 news application with public articles, admin article management, user accounts, comments, reactions, bookmarks, profile pages, and several live data pages for weather, crypto, currency, air quality, and SPAR product deals.

Use these docs as the starting point for understanding the project:

- [Project Structure](project-structure.md) explains the main folders and how Laravel, Blade, Vite, models, routes, and controllers fit together.
- [Application Flow](application-flow.md) explains the user-facing and admin request flows.
- [Data Model](data-model.md) describes the database tables, Eloquent models, relationships, and important constraints.
- [External Services](external-services.md) explains the live-data integrations and caching behavior.
- [Telegram Admin Bot](telegram-bot.md) explains managing news from Telegram and how authorization works.
- [Development Guide](development.md) covers setup, common commands, tests, and typical change locations.

## High-Level Architecture

The app follows a standard Laravel MVC structure:

1. A browser request hits a route in `routes/web.php`.
2. The route calls a controller in `app/Http/Controllers`.
3. The controller reads or writes data through Eloquent models in `app/Models`.
4. The controller returns a Blade view from `resources/views`.
5. CSS and JavaScript are built through Vite from `resources/css` and `resources/js`.

Most features are server-rendered. There are no JSON API routes in the current project; the app renders Blade pages and handles form submissions with regular Laravel routes.

## Main Feature Areas

- Public news homepage and article pages.
- Search across published article dispatches.
- Admin-only news CRUD panel.
- Telegram admin bot for managing news from a chat.
- News image uploads with external image URLs as a fallback.
- User registration, login, logout, and profile pages.
- Authenticated comments, reactions, and bookmarks.
- JSON endpoint for published news at `/api/news`.
- Live data pages backed by external APIs and Laravel cache.

## Important Access Rules

- Public users can read only `done` news articles.
- Pending articles return `404` to guests and non-admin users.
- Admin users can preview pending articles.
- The admin news panel requires an authenticated user with `is_admin = true`.
- Comments, reactions, and bookmarks require authentication.
