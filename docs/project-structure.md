# Project Structure

This repository is a Laravel application with Vite-managed frontend assets.

## Root Files

| Path | Purpose |
| --- | --- |
| `artisan` | Laravel command-line entry point. |
| `composer.json` | PHP dependencies, autoloading, and Laravel scripts. |
| `package.json` | Node/Vite frontend dependencies and scripts. |
| `phpunit.xml` | PHPUnit and Laravel test configuration. |
| `vite.config.js` | Vite integration for Laravel assets. |

## Application Folders

| Folder | Purpose |
| --- | --- |
| `app/Http/Controllers` | Request handlers for pages and form submissions. |
| `app/Http/Middleware` | Custom request guards, currently the admin guard. |
| `app/Models` | Eloquent models for database tables. |
| `bootstrap` | Laravel application bootstrapping. |
| `config` | Laravel framework and service configuration. |
| `database/migrations` | Schema definitions for users, news, comments, reactions, bookmarks, cache, and jobs. |
| `database/factories` | Test data factories for users and news. |
| `database/seeders` | Database seed entry point. |
| `public` | Web server document root. `public/index.php` boots Laravel. |
| `resources/css` | App CSS entry point. |
| `resources/js` | App JavaScript entry point and bootstrap file. |
| `resources/views` | Blade templates grouped by feature. |
| `routes` | Web and console route definitions. |
| `storage` | Laravel-generated files, logs, sessions, cache, and compiled views. |
| `tests` | Feature and unit tests. |

## MVC Layout

The core application code is organized around Laravel MVC:

- Routes in `routes/web.php` map URLs to controller actions.
- Controllers prepare data, validate forms, enforce feature-specific checks, and return views.
- Models define fillable fields, casts, and relationships.
- Blade templates render HTML.
- Migrations define the database structure used by the models.

## View Structure

The current Blade view groups are:

| Folder | Pages |
| --- | --- |
| `resources/views/news` | Public index/show pages and admin create/edit forms. |
| `resources/views/admin` | Admin login and admin news index. |
| `resources/views/auth` | User login and registration pages. |
| `resources/views/bookmarks` | Authenticated saved articles page. |
| `resources/views/profile` | Public user profile page. |
| `resources/views/weather` | Weather data page. |
| `resources/views/crypto` | Crypto market data page. |
| `resources/views/currency` | Currency exchange data page. |
| `resources/views/air` | Air quality data page. |
| `resources/views/spar` | SPAR product/deals page. |
| `resources/views/layouts` | Shared application layout. |

## Request Lifecycle

For a typical page request:

```text
Browser
  -> public/index.php
  -> Laravel router
  -> routes/web.php
  -> controller action
  -> Eloquent models or external HTTP services
  -> Blade view
  -> HTML response
```

For a typical form submission:

```text
Browser POST/PUT/DELETE form
  -> route middleware
  -> controller validation
  -> Eloquent write
  -> redirect with flash message
```

