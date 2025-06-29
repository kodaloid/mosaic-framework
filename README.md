# Mosaic Framework

Mosaic is a compact boiler-plate for building PHP applications, with MySQL & 
SQLite access, a built-in login system with OTP/2FA support, SASS/CSS, Twig 
page templates, TypeScript and Bootstrap out of the box.

I built this over time whilst getting fed up with other frameworks like SlimPHP
and Laravel due to how bloated they can be. You don't have to go out of your way
to code 100 files to get a website up and running, this just works out of the
box, and has a tiny footprint making it easy to learn.

## Requirements

This project requires that you have PHP 8.2 or newer installed somewhere on
your system, [Composer](https://getcomposer.org/) for dependencies, 
[NodeJS](https://nodejs.org/en/download) (optional) if you want to use the 
SASS & TypeScript features, and access 
to a terminal. 

## How To Install

Boot up your terminal, then follow these instructions:

```shell
# make a directory
mkdir my-app
cd my-app

# clone this repo
git clone https://github.com/kodaloid/mosaic-framework .

# get composer to prepare dependencies
composer install

# also prepare the node dependencies (if using SASS or TypeScript)
npm install

# start the project (uses built-in php live server)
composer start
```

By default the live server will launch at http://localhost:8080 however this can
be reconfigured in `composer.json` if needed.

The first thing you should do is visit the live server link, which will present
the setup screen. The setup screen is used to build a `config.php` file with all
the needed info like website name, time zone info, and database credentials.

That's all!

## Login System & OTP Authentication

Implementing authentication is an important part of building a website that's
anything other than static. But it's also a tedious process that if not done
right can cause many problems down the road. So to remove the headache, Mosaic
comes with a modern login system out of the box.

When you first setup the website, you'll be presented with a QR code that can be
scanned with an Authenticator mobile app (I recommend Google Authenticator,
but others like Authy will work as well). This code will be required along side
user & password when logging in.

OTP Authentication can be turned off, including the login system if you do not
need them,

## Routes

Mosaic uses a familiar MVC (model view controller) routing system similar to 
other platforms you might have experience with like Laravel. For each URL on
your site, a route should exist, that points to a method function on a controller.

We define routes in `index.php`. For example the `/about` page has a route by
default that links to the `about()` method in the `controllers/PageController` 
class. The method we use to define a route like this:

```php
$app->route(string $url, string $class, string $func, array $http_methods)

// example: $app->route('/about', 'PageController', 'about')
```

The optional `$http_methods` variable defaults to `['GET']` which means you can 
only use the HTTP GET technique to view that route. If you wished to include the 
POST technique, you could specify `['GET', 'POST']` instead. Mosaic supports any 
supported HTTP method filter you wish to use.




## Controller & Views

Controllers are classes in the `controller` folder that inherit from the `Controller` class, and host routing destination methods. Every method in a controller that is 
linked by a route automatically gets the main `$app` instance passed as an argument.
Each method is also expected to return some form of output.

The `about()` method in `PageController.php` looks like this:

```php
function about(App $app) {
	$data = array();
	return $this->view('about', $data);
}
```

This method is fairly simple as it uses the built in function `$this->view([view], [data])` to render a view, passing an array of data, and return the HTML result. The `about`
view is a twig file that can be found in `templates/about.twig`.

Controllers by default return HTML via the `view()` method, however controllers
also have a `$this->json([success], [data])` method too if you wish to provide
an API exclusive endpoint.

## Database Access

Database communication in Mosaic is deliberately simple. There is a single utility
under the global `$db` which works similar to the system you see in WordPress. Here
are 4 examples to demonstrate everything you probably wish to know about database
access:

### Selecting Data

```php
function list_people(App $app) {
	global $db;
	return $this->view('people_list', [
		'people' => $db->select('SELECT * FROM people')
	]);
}
```

### Selecting Single Row

The database is implemented using PDO, which means you can use ? notation when
preparing statements like this:

```php
function get_user(int $user_id) {
	global $db;
	return $db->select_row($db->prepare(
		'SELECT * FROM users WHERE id = ?', 'i', [$user_id]
	));
}
```

Or you can also use magic markers with a keyed array of data like this:

```php
function get_user(int $user_id) {
	global $db;
	return $db->select_row($db->prepare(
		'SELECT * FROM users WHERE id = :id', null, [':id' => $user_id]
	));
}
```


### Update Query

```php
function update_user_email(int $user_id, string $new_email) {
	global $db;
	return $db->exec($db->prepare(
		'UPDATE users SET email = ? WHERE id = ?', 'si', [$new_email, $user_id]
	));
}
```

### Insert Query

```php
function update_user_email(string $name, int $age) {
	global $db;
	$db->exec($db->prepare(
		'INSERT INTO people (`name`, age, date_created) VALUES (?, ?, ?)', 'sis',
		[$name, $age, $db->now()]
	));
	return $db->insert_id();
}
```

## Plugins

You may find a need to extend the functionality of your application. Commonly 
the wish is to wrap up methods into a utility that can be accessed anywhere. So 
I've provided a simple plugin system.

To load a plugin, we do something similar to routes. In the `index.php` file 
you'll see a line that looks like this:

```php
$app->add_plugin('test', 'TestTool');
```

This instantiates the example test plugin found under `plugins/TestTool.php`, 
and sets it as a property belonging to the `$app` global named `test`. Later on 
if we wish to use `say_hello()` method, we could do something like this:

```php
global $app;

$app->plugin('test')->say_hello();
```

Or in a twig template you could do this:

```twig
{% app.plugin('test').say_hello() %}
```

## Assets (CSS, SASS, JavaScript & TypeScript)

The assets folder contains a `src` folder, and a `dist` folder. Most of your CSS, 
JavaScript, and other assets like images should reside in the `dist` folder. The
`src` folder is used for storing un-compiled SASS and TypeScript files.

This project includes a `package.json` file with a number of useful scripts. To
use them first make sure you have NodeJS installed, then in the terminal type `npm install` (only needs to be done once) to download the necessary dependencies.

To compile both SASS and TypeScript, you can then use either `npm run dev` (start-up watch on both compilers) or `npm run prod` to compile once.


## Conclusion

If you have any issues please raise using GitHub issues. There are not that many
cogs in this machine, which means solutions are likely easy to find. Further guides
on how to do other things will be added to the wiki soon.

Koda
