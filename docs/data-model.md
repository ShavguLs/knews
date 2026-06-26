# Data Model

The application stores its core content and user interactions in the Laravel database.

## Tables

### `users`

Created by Laravel's default user migration and extended with:

| Column | Purpose |
| --- | --- |
| `is_admin` | Boolean flag that grants access to the admin panel. Defaults to `false`. |

The `User` model also casts:

- `password` as `hashed`.
- `is_admin` as `boolean`.
- `email_verified_at` as `datetime`.

### `news`

Created by `2026_05_01_164537_create_news_table.php` and extended by later migrations.

| Column | Purpose |
| --- | --- |
| `id` | Primary key. |
| `title` | Article title. |
| `category` | Article category label. |
| `author` | Author display name. |
| `body` | Full article body. |
| `image_url` | Optional external image URL. |
| `image_path` | Optional uploaded image path on the public filesystem disk. |
| `published_at` | Optional publication timestamp. |
| `status` | Publication state. Valid controller values are `pending` and `done`. |
| `is_hero` | Marks the article as the homepage hero. |
| `created_at`, `updated_at` | Laravel timestamps. |

`NewsController` keeps hero selection unique at the application level by clearing existing heroes when a new hero is saved.

Uploaded article images are stored in `storage/app/public/news-images`. Laravel serves them through the public storage symlink created by `php artisan storage:link`.

### `comments`

| Column | Purpose |
| --- | --- |
| `id` | Primary key. |
| `news_id` | Article being commented on. Cascades on article delete. |
| `user_id` | Comment author. Cascades on user delete. |
| `body` | Comment text. |
| `created_at`, `updated_at` | Laravel timestamps. |

### `reactions`

| Column | Purpose |
| --- | --- |
| `id` | Primary key. |
| `news_id` | Article being reacted to. Cascades on article delete. |
| `user_id` | Reacting user. Cascades on user delete. |
| `type` | Reaction key. Must match `Reaction::TYPES` in controller validation. |
| `created_at`, `updated_at` | Laravel timestamps. |

Constraint:

```text
unique(news_id, user_id)
```

This ensures a user can have only one reaction per article.

### `bookmarks`

| Column | Purpose |
| --- | --- |
| `id` | Primary key. |
| `user_id` | Bookmark owner. Cascades on user delete. |
| `news_id` | Saved article. Cascades on article delete. |
| `created_at`, `updated_at` | Laravel timestamps. |

Constraint:

```text
unique(user_id, news_id)
```

This ensures a user cannot save the same article more than once.

## Eloquent Relationships

### `User`

| Method | Relationship |
| --- | --- |
| `comments()` | `hasMany(Comment::class)` |
| `reactions()` | `hasMany(Reaction::class)` |
| `bookmarks()` | `hasMany(Bookmark::class)` |
| `bookmarkedNews()` | `belongsToMany(News::class, 'bookmarks')->withTimestamps()` |

### `News`

| Method | Relationship |
| --- | --- |
| `comments()` | `hasMany(Comment::class)` |
| `reactions()` | `hasMany(Reaction::class)` |
| `bookmarks()` | `hasMany(Bookmark::class)` |

### `Comment`

| Method | Relationship |
| --- | --- |
| `user()` | `belongsTo(User::class)` |
| `news()` | `belongsTo(News::class)` |

### `Reaction`

| Method | Relationship |
| --- | --- |
| `user()` | `belongsTo(User::class)` |
| `news()` | `belongsTo(News::class)` |

### `Bookmark`

| Method | Relationship |
| --- | --- |
| `user()` | `belongsTo(User::class)` |
| `news()` | `belongsTo(News::class)` |

## Deletion Behavior

Deleting a user cascades to that user's:

- Comments.
- Reactions.
- Bookmarks.

Deleting a news article cascades to its:

- Comments.
- Reactions.
- Bookmarks.

This keeps interaction tables from retaining orphaned records.
