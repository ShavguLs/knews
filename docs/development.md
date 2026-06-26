# Development Guide

## Requirements

The project expects:

- PHP 8.2 or newer.
- Composer.
- Node.js and npm.
- A database supported by the app's Laravel configuration.

Laravel configuration is controlled through `.env`, using the standard keys from `config/app.php`, `config/database.php`, `config/cache.php`, and related config files.

## Install

Install PHP and Node dependencies:

```bash
composer install
npm install
```

Create and configure `.env` if it does not already exist, then generate an app key:

```bash
php artisan key:generate
```

Run migrations:

```bash
php artisan migrate
```

Create the public storage symlink so uploaded news images can be served by the browser:

```bash
php artisan storage:link
```

Build frontend assets for production:

```bash
npm run build
```

`composer.json` also defines a `setup` script that combines dependency installation, app key generation, migrations, npm install, and asset build.

## Run Locally

The main development command is:

```bash
composer run dev
```

That script starts:

- Laravel's local server.
- The queue listener.
- Laravel Pail logs.
- Vite dev server.

You can also run the pieces manually:

```bash
php artisan serve
npm run dev
```

## Testing

Run the Laravel test suite:

```bash
composer test
```

or:

```bash
php artisan test
```

The custom feature coverage in `tests/Feature/AdminNewsAccessTest.php` verifies:

- Public news access.
- Admin route protection.
- Admin news CRUD.
- Published vs pending article visibility.
- Hero article selection and fallback behavior.
- Search behavior.

## Common Change Locations

| Change | Files to start with |
| --- | --- |
| Add a public page | `routes/web.php`, new controller method, new Blade view. |
| Add a new admin feature | `routes/web.php` admin group, controller, middleware rules if needed, Blade views. |
| Change article fields | `database/migrations`, `app/Models/News.php`, `NewsController` validation, create/edit/show/index views. |
| Change news image uploads | `NewsController`, `app/Models/News.php`, `resources/views/news/create.blade.php`, `resources/views/news/edit.blade.php`, filesystem config. |
| Change JSON news API | `routes/api.php`, `app/Http/Controllers/Api/NewsApiController.php`. |
| Change comments | `CommentController`, `app/Models/Comment.php`, article show view, comments migration. |
| Change reactions | `ReactionController`, `app/Models/Reaction.php`, article show view, reactions migration. |
| Change bookmarks | `BookmarkController`, `app/Models/Bookmark.php`, article show view, bookmarks page. |
| Change live data | Matching live-data controller and view. |
| Change styling | `resources/css/app.css` and Blade class markup. |
| Change JavaScript | `resources/js/app.js` or `resources/js/bootstrap.js`. |

## Adding an Admin User

The app uses the same `users` table for admins and normal users. To make a user an admin, set:

```text
users.is_admin = true
```

This can be done through a database client, a seeder, Tinker, or a one-off migration depending on deployment needs.

## Conventions Used Here

- Server-rendered Blade pages are preferred over client-rendered pages.
- Controllers validate request data before writes.
- Authenticated-only actions live inside the `auth` route group.
- Admin-only news management lives inside the `admin` middleware group.
- External data providers are isolated in their own controllers and exposed through `data()` methods for reuse.
- Successful external responses are cached to reduce latency and provider load.
