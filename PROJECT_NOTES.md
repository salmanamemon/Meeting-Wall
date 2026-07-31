# DAR Stories — project notes

## Stack and access

- Core PHP, PDO/MySQL, cURL, HTML/CSS/JS; no PHP framework.
- Users sign in with either `username` or `email` from the `users` table.
- Passwords must be stored with `password_hash()`; never save plain-text passwords.
- Persistent login uses a hashed selector/token in `auth_tokens`. Signing out removes that token.
- The existing local user is `dpSalesOne`; manage its password only through a new PHP password hash.

## Add users

In phpMyAdmin, insert into `users`: `name`, unique `username`, unique `email`, and `password_hash`.

```bash
/Applications/AMPPS/php/bin/php -r "echo password_hash('TheirStrongPassword', PASSWORD_DEFAULT), PHP_EOL;"
```

## Login lock

- Ten failed sign-in attempts from one IP lock login for 15 minutes.
- A successful sign-in clears that IP's failures.
- The table is `login_attempts`; its creation script is [login-lock.sql](login-lock.sql).
- To immediately clear a single lock in phpMyAdmin:

```sql
DELETE FROM login_attempts WHERE ip_address = 'THE_LOCKED_USER_IP';
```

- To clear all temporary locks:

```sql
TRUNCATE TABLE login_attempts;
```

## Activity API

- Endpoint: `POST https://middleware-uat.danubeproperties.com/api/activity_list`
- The API header and JSON body live in `config.php`; change the `user_id` header there if it changes.
- Body: `{"limit":1000,"last24Hours":true}`.
- The app reads `data.daliysaleList` (and also accepts `data.dailySaleList`).
- Every API object renders as one card, even when `OwnerName` repeats.
- `OwnerName` is the card title; `Check_Out_Date_Time__c` is the date; `checkOutDocumentLink` is the card image/lightbox document.

## UI behavior

- Desktop slider: 3 cards; tablet: 2; mobile: 1.
- Controls are hidden below 3 cards. Autoplay loops only above 3 cards.
- Checkout documents open in Fancybox with zoom, fullscreen, thumbnails, and gallery controls.
- Card image height is fixed at 250px; cards use a compact title area.
- Desktop styling uses viewport-based sizing at 911px and above.

## Sharing and branding

- Open Graph and Twitter metadata are in `index.php` and `login.php`.
- Share preview: `assets/images/dar-stories-share.png`.
- It includes the Danube Properties wordmark above, left-aligned with, the DAR Stories title.
- Favicon: `favicon.png` in the project root.
