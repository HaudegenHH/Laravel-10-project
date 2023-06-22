# Laravel 10 (06/2023)

- looking into new Laravel features 
- trying out OpenAI PHP client for image/avatar generation
- building a simple ticket system
- deploying on MezoHub

---

- make sure php (8.1 and above) and composer is installed
```sh
composer create-project laravel/laravel laravel-10-project 
```
- open in vscode
```sh
php artisan serve
```

add package for authentication:
```sh
composer require laravel/breeze --dev
```

- with that you have a new artisan command that you execute right away to install the breeze controllers and resources for authentication
```sh
php artisan breeze:install
``` 
- Stack: Blade
- PHPUnit instead of Pest

```sh
php artisan migrate
``` 
..and laravel will not only create the database (taken the DB_NAME from the .env), it additionally creates 5 tables that are related to the registration & authentication of users 
- these tables are based on migrations in the database folder
- mysql server (or xampp) should run on port:3306

**Eloquent**

- one of the big advantages of using eloquent instead of the normal queries (DB facade) or via Query Builder, is the use of mutators and accessor, demonstrated with an accessor that (like a getter) should return the fetched name in uppercase and a mutator that (like a setter) hashing the password before putting it into the database.

---

**User Avatar Field**

- Add new column "avatar" to user table
- therefore you need to create a new migration
```sh
php artisan make:migration update_user_table_add_avatar_field --table=users
```
- make sure its users! (the plural of the Model name User)
- inside up() you add the column, inside down() you remove it

- then you can run the migrate command
```sh
php artisan migrate
```
---

