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

## Tinker

- command line interface that allows interaction with the application (sort of a PHP/Laravel shell/REPL, like the javascript repl in the browsers)
```sh
php artisan tinker
```
for example:
```sh
> strtoupper('my name')
= "MY NAME"
```

- but you could also do laravel specific things like:
```sh 
> $user = User::find(1)
= App\Models\User {
  id: 1,
  name: "Bob",
  email: "bob@gmail.com",
  avatar: null,
  ...
}
```

- now could as well update the avatar field for this specific user 
```sh
$user->avatar = 'abcdef'
= "abcdef"
```
- to save the changes in the DB:
```sh
$user->save();
```
- to do this in one go there is another option
- like before find a user by id save it in $user
- and then use the update method instead
```sh
$user->update(['avatar' => 'new value with update method'])
```
- may be you have to restart tinker
- and most important: make sure the "avatar" is a fillable field (see: mass assignment with fillable and guarded In User class)

---

## creating form for uploading an avatar

- after adding the column and knowing how to update the users avatar field, you can start creating the UI for it

- then you will have to define a route and a controller / method 
- To avoid cluttering the controller method, outsource the validation of the request:
```sh
php artisan make:request UpdateAvatarRequest
``` 

- after storing the absolute path to the stored image in the db, you might want to show the avatar image right away after redirecting
- since the controller which is responsible for returning the profile page as well returns the $request->user, you have access to the path where the image is stored, but: its the absolute path for the server location of the image, so its useless to have an img-tag with a src that points to that location
- only accessible folder on the client is the public folder, therefore the avatar images that are stored under /storage/app/avatars/..  cannot be reached
- that means you have to create a sym link from the storage to the public directory
```sh
php artisan storage:link
```

"The [C:\....\projectName\public\storage] link has been connected to [C:\Users\projectName\storage\app/public]"

- still not looking great, like i commented in the AvatarController: the store method has a 2nd optionally parameter with which you can define the disk, meaning: the root 
- now you can store the relative paths which points to the public/storage/avatars  thanks to the symbolic link

- deleting existing/old avatars with the Storage Facade
- its also helpful to store the avatars in a more convenient and readable manner
```sh
$path = Storage::disk('public')->put('avatars', $request->file('avatar'));
```

---

Open AI - PHP Client

- first install OpenAI via composer package manager:
```sh
composer require openai-php/laravel
```
- publish the configuration file next:
```sh
php artisan vendor:publish --provider="OpenAI\Laravel\ServiceProvider"
```

This will create a **config/openai.php** configuration file in your project, which you can modify to your needs using environment variables:
OPENAI_API_KEY=....

- then interact with the api by using the OpenAI facade:
```sh
use OpenAI\Laravel\Facades\OpenAI;

$client = OpenAI::client('YOUR_API_KEY');
$result = $client->completions()->create([
    'model' => 'text-davinci-003',
    'prompt' => 'PHP is'
]);
echo $result['choices'][0]['text'];
``` 

- to get the api key visit:
 openai.com 
- sign in with google
- create "new secret key"
- copy that key and paste it into .env


- in the left sidebar you find "Usage" 
- free trial usage: $5.00
- but: instead of "completions", we want openai to create images for us:
  
```sh
$result = OpenAI::images()->create([
  "prompt" => "create avatar for user with name " . auth()->user()->name,
  "n"      => 1,
  "size"   => "256x256",
]);
```

## integrate image/avatar generation on profile page

- create a form with just one button for generating the avatar
- create the route, controller and method for it (AvatarController)

---

## Login with github or google
- for that you will need the package: "Socialite"
"In addition to typical, form based authentication, Laravel also provides a simple, convenient way to authenticate with OAuth providers using Laravel Socialite.
Socialite currently supports authentication via Facebook, Twitter, LinkedIn, Google, GitHub, GitLab and Bitbucket."

- install the package:
```sh
composer require laravel/socialite
```
- like described in the docs (https://laravel.com/docs/10.x/socialite)
you need to place some credentials in your "config/services.php" depending on the providers your app requires, eg:
```sh
'github' => [
    'client_id' => env('GITHUB_CLIENT_ID'),
    'client_secret' => env('GITHUB_CLIENT_SECRET'),
    'redirect' => env('GITHUB_REDIRECT_URL'),
],
```

- then you have to create 2 routes for redirect and callback which you register in the web.php
- next is the registration of a new OAuth App in your Github Account
(homepage-url: http://localhost:8000, callback-url: http://localhost:8000/auth/callback)
- copy the client ID and generate a client secret and put both into the .env
- try out the route "localhost:8000/auth/redirect" manually (since we have no button yet for sigin with github), and you should see the authorize screen, where you can choose your github-account to sign in with. Then the "/auth/callback" should be triggered - the callback function that runs after successfully authenticate the user. 
- if you have problems with that its probably cache related, try out:
```sh
php artisan cache:clear
```
and
```sh
composer dump-autoload
```
otherwise look at: https://laravel.com/docs/10.x/socialite#authentication-and-storage

- in the callback fn you have (after successful authentication with the choosen github-account) the github user information 
- with that you can make use of the eloquent method "updateOrCreate" - so you can either create a user entry in your mysql database if it doesnt exist or you can update the existing user in the DB.
- in short: 
  - first get the user from github
  - then using the eloquent user model check if a user is avaibable in the DB with an email that match the email from the github-account
  - if there is a user with that email then update name and password (provided in the 2nd array) if not then just create a new user with name, password and email
- another way would be to utilize the "firstOrCreate" method which returns the found user without updating.\
  
**Note**: its not neccessary for a github account to have a name saved, but there is always a nickname, thats why i made use of the null coalescing operator (ternary that checks for null: 'name' => $githubUser->name ?? $githubUser->nickname )

- finally create a button for signing in with github wrapped with a form that leads to the redirect route defined in the web.php
