# Page Talk Track

Use this as a short script when presenting or demonstrating each page of the Knews project.

## Home / News Page

Route: `/` or `/news`

What to say:

- This is the main page of the project.
- It shows published news articles only.
- The top article is the hero article, which is chosen by the admin.
- If there is no selected hero article, the latest published article is used.
- The search bar filters the regular news list by title, body, category, or author.
- The page also shows live information blocks for weather, crypto, currency, and air quality.

Demo points:

- Open the home page.
- Show the hero article.
- Search for a news article.
- Open one article from the list.

## Single News Page

Route: `/news/{news}`

What to say:

- This page shows the full content of one news article.
- Users can read the article, see comments, and view reaction counts.
- Logged-in users can add comments, react to the article, and save it as a bookmark.
- Guests can read published articles, but they cannot comment, react, or bookmark.
- Pending articles are hidden from normal users, but admins can preview them.

Demo points:

- Open a news article.
- Show the comment section.
- Log in and add a reaction or bookmark.

## Register Page

Route: `/register`

What to say:

- This page lets a visitor create a normal user account.
- The password is stored securely using Laravel password hashing.
- After registration, the user is logged in automatically.
- New users are not admins by default.

Demo points:

- Show the registration form.
- Explain the required fields.
- Mention that validation protects the form from invalid data.

## Login Page

Route: `/login`

What to say:

- This page is for normal user login.
- Laravel checks the email and password.
- After successful login, the session is regenerated for security.
- Logged-in users can comment, react, and bookmark articles.

Demo points:

- Show the login form.
- Log in as a normal user.
- Return to an article and show user-only actions.

## Bookmarks Page

Route: `/bookmarks`

What to say:

- This page shows the articles saved by the current logged-in user.
- Bookmarks work like a toggle: clicking once saves the article, clicking again removes it.
- Only published articles are shown here.
- Each user has their own bookmark list.

Demo points:

- Bookmark an article.
- Open the bookmarks page.
- Remove the bookmark and show that it disappears.

## Profile Page

Route: `/u/{user}`

What to say:

- This is a public user profile page.
- It shows the user's recent comments on published articles.
- It also shows simple activity counts, like total comments and reactions.
- The page helps connect user activity with the news discussion system.

Demo points:

- Open a user profile.
- Show recent comments.
- Mention that only public/published article activity is displayed.

## Weather Page

Route: `/weather`

What to say:

- This page displays weather data from an external API.
- The controller requests live data and stores it in cache.
- Cache helps the page load faster and avoids sending too many API requests.
- If the API fails, the page can still handle the error without breaking the whole site.

Demo points:

- Open the weather page.
- Point out current weather values.
- Explain that this data is not manually written in the database.

## Crypto Page

Route: `/crypto`

What to say:

- This page shows cryptocurrency market data.
- The data comes from an external crypto API.
- Like the weather page, the result is cached.
- This demonstrates that the project can combine local database content with live external data.

Demo points:

- Open the crypto page.
- Show coin prices or market values.
- Mention the API plus cache pattern.

## Currency Page

Route: `/currency`

What to say:

- This page shows currency exchange information.
- The data comes from an external financial data source.
- The page is useful because users can check currency rates without leaving the news site.
- The controller keeps this logic separate from the news logic.

Demo points:

- Open the currency page.
- Show the exchange rate list.
- Explain that this is another example of external service integration.

## Air Quality Page

Route: `/air`

What to say:

- This page shows air quality information.
- The project uses an external API and cache to display the latest available data.
- This page follows the same structure as the other live data pages: route, controller, API call, cache, and Blade view.

Demo points:

- Open the air quality page.
- Show the air quality values.
- Mention code reuse in the live data pages.

## SPAR Page

Route: `/spar`

What to say:

- This page shows product or market data from an external SPAR-related source.
- It demonstrates that the project can display more than only news articles.
- The same external API and caching idea is used here too.

Demo points:

- Open the SPAR page.
- Show the displayed products or data.
- Explain why cache is useful for product data.

## Admin Login Page

Route: `/admin/login`

What to say:

- This page is separate from the normal user login route.
- Admin login still uses the users table, but it requires `is_admin = true`.
- If a normal user tries to access the admin panel, the middleware blocks them.

Demo points:

- Open the admin login page.
- Explain the difference between user login and admin login.
- Log in as an admin user.

## Admin News List Page

Route: `/admin/news`

What to say:

- This is the admin panel for managing news articles.
- Admins can see all articles, including pending articles.
- From here, the admin can create, edit, or delete articles.
- This page is protected by admin middleware.

Demo points:

- Open the admin news list.
- Show published and pending articles.
- Point out create, edit, and delete actions.

## Admin Create News Page

Route: `/admin/news/create`

What to say:

- This page lets an admin create a new article.
- The form validates fields like title, body, category, author, status, and image.
- The admin can upload an image file.
- The admin can mark an article as the hero article.
- If a new hero article is selected, the old hero article is cleared so only one hero exists.

Demo points:

- Open the create form.
- Show required fields.
- Explain pending vs done status.
- Mention image upload.

## Admin Edit News Page

Route: `/admin/news/{news}/edit`

What to say:

- This page lets an admin update an existing article.
- The admin can change the article content, status, image, and hero setting.
- Pending articles can be prepared first and published later by changing the status to done.
- This keeps content management separate from the public user experience.

Demo points:

- Open an existing article in edit mode.
- Change status or text.
- Explain how the update is saved through the controller.

## Suggested Presentation Order

1. Home page
2. Single news page
3. Register and login pages
4. Comments, reactions, and bookmarks
5. Profile page
6. Live data pages: weather, crypto, currency, air, SPAR
7. Admin login
8. Admin news list
9. Admin create and edit pages
10. Finish with the database, MVC structure, external APIs, and cache explanation

