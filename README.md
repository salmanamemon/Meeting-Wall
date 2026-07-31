# DAR Stories

1. In phpMyAdmin, select the existing `darstories_danubeone_app` database and import `database.sql`.
2. Edit `config.php` with your database credentials; `config.example.php` is the safe template to keep in source control. Change the API `user_id` there when needed.
3. Create a user with the supplied `password_hash` command and SQL example.
4. Serve this folder through Apache/PHP (cURL and PDO MySQL must be enabled), then open `login.php`.

The API is called with POST and expects `data.daliysaleList` (also accepts `data.dailySaleList`). Each API object becomes one card; `OwnerName` is the title and `checkOutDocumentLink` is its image/document.
