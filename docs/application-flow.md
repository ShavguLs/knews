# Application Flow

All browser-facing routes are registered in `routes/web.php`.

## Public News Flow

The homepage is served by `NewsController@index` through both `/` and `/news`.

The controller:

1. Finds a published hero article where `status = done` and `is_hero = true`.
2. Falls back to the latest published article when no hero exists.
3. Loads the remaining published articles as dispatches.
4. Applies `search` only to dispatches, not to the hero.
5. Pulls live ticker data from weather, crypto, currency, and air-quality controllers.
6. Renders `resources/views/news/index.blade.php`.

The single article page is served by `NewsController@show` at `/news/{news}`.

Important behavior:

- Guests and normal users cannot open pending articles.
- Admins can open pending articles for preview.
- Comments are loaded newest first with their users.
- Reaction counts are grouped by reaction type.
- Logged-in users get their current reaction and bookmark state.

## Authentication Flow

User authentication is handled by `AuthController`.

| Route | Controller | Purpose |
| --- | --- | --- |
| `GET /register` | `showRegister` | Show registration form. |
| `POST /register` | `register` | Create a non-admin user, log them in, and redirect home. |
| `GET /login` | `showLogin` | Show user login form. |
| `POST /login` | `login` | Authenticate credentials and regenerate the session. |
| `POST /logout` | `logout` | Log out, invalidate the session, and regenerate the CSRF token. |

Passwords are hashed automatically by the `User` model's `password` cast.

## Admin Flow

Admin authentication is separate in route shape but uses the same Laravel `users` table and session guard.

| Route | Controller | Purpose |
| --- | --- | --- |
| `GET /admin/login` | `AdminLoginController@showLoginForm` | Show admin login form. |
| `POST /admin/login` | `AdminLoginController@login` | Authenticate and require `is_admin = true`. |
| `POST /admin/logout` | `AdminLoginController@logout` | End the admin session. |
| `/admin/news/*` | `NewsController` | Admin news CRUD resource. |

The `admin` middleware:

1. Redirects guests to `admin.login`.
2. Aborts with `403` for authenticated non-admin users.
3. Allows users with `is_admin = true`.

The admin news resource uses `NewsController` methods:

- `index` lists all articles, including pending articles.
- `create` shows the creation form.
- `store` validates and creates articles.
- `edit` shows the edit form.
- `update` validates and updates articles.
- `destroy` deletes articles.

When an article is saved as the hero, the controller clears `is_hero` from other articles so only one article is marked as hero.

## Comments Flow

Comment routes are inside the `auth` middleware group.

| Route | Controller | Purpose |
| --- | --- | --- |
| `POST /news/{news}/comments` | `CommentController@store` | Add a comment to an article. |
| `DELETE /comments/{comment}` | `CommentController@destroy` | Delete a comment. |

Rules:

- Comments require a logged-in user.
- Normal users cannot comment on pending articles.
- Comment body is required and limited to 2000 characters.
- Users can delete their own comments.
- Admins can delete any comment.

## Reactions Flow

Reactions are handled by `ReactionController@store`.

The route is:

```text
POST /news/{news}/reactions
```

Rules:

- Reactions require a logged-in user.
- Normal users cannot react to pending articles.
- Only reaction types defined in `Reaction::TYPES` are valid.
- One user can have only one reaction per article.
- Posting the same reaction twice removes it.
- Posting a different reaction updates the existing one.

The database unique key on `news_id` and `user_id` enforces one reaction per user per article.

## Bookmarks Flow

Bookmarks are handled by `BookmarkController`.

| Route | Controller | Purpose |
| --- | --- | --- |
| `GET /bookmarks` | `index` | Show the current user's saved published articles. |
| `POST /news/{news}/bookmark` | `toggle` | Add or remove an article bookmark. |

Rules:

- Bookmarks require a logged-in user.
- Normal users cannot bookmark pending articles.
- Bookmarking the same article again removes the bookmark.
- The bookmarks page shows only articles with `status = done`.

## Profile Flow

Public profiles are served by:

```text
GET /u/{user}
```

`ProfileController@show` displays:

- The user's latest 20 comments on published articles.
- Total comment count on published articles.
- Total reaction count.

## Live Data Pages

These routes render server-side pages backed by external HTTP requests and Laravel cache:

| Route | Controller |
| --- | --- |
| `/weather` | `WeatherController@index` |
| `/crypto` | `CryptoController@index` |
| `/currency` | `CurrencyController@index` |
| `/air` | `AirQualityController@index` |
| `/spar` | `SparController@index` |

See [External Services](external-services.md) for details.

