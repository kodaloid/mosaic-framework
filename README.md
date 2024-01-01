# Mosaic CMS

Mosaic is a compact framework for building modern PHP websites, with a built-in 
login system (inc. OTP/2FA support), SASS style sheets, Twig for templating, 
TypeScript and Bootstrap out of the box.

Plenty of systems out there can be used to build modern websites, and most tailor
for modern JavaScript frameworks like React/Vue. But those features comes at the 
cost of making a lot of what PHP does best redundant.

The purpose of Mosaic is be a PHP only boiler-plate, that works a lot like
Laravel, without the bloat, and avoiding the tangled mess of JavaScript to PHP 
bootstrap techniques.

## Requirements

This framework requires the following to be available:

- PHP 8.1 or newer.
- MySQLi Compatible Database Server.

## How To Install

First create a database on your MySQLi compatible server. Then visit your website
URL and walk through the process on setting up the database, and creating a new 
admin user (if required).

That's all!


## Routes

Mosaic uses a familiar MVC (model view controller) routing system similar to 
other platforms you might have experience with like Laravel. For each URL on
your site, a route should exist, that points to a method function on a controller.

We define routes in `index.php`. For example the `/about` page has a route by
default that links to the `about()` method in the `controllers/PageController` 
class. The method we use to define a route like this:

```php
$app->route($url, $class_name, $method [, $http_methods])
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

```php
function get_user(int $user_id) {
	global $db;
	return $db->select_row($db->prepare(
		'SELECT * FROM users WHERE id = ?', 'i', [$user_id]
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

You may find a need to extend the functionality of Mosaic website. Commonly the wish is to wrap up methods into a utility that can be accessed anywhere. So Mosaic
provides a simple plugin system.

To load a plugin, we do something similar to routes. In the `index.php` file you'll
see a line that looks like this:

```php
$app->add_plugin('test', 'TestTool');
```

This instantiates the example test plugin found under `plugins/TestTool.php`, and
sets it as a property belonging to the `$app` global named `test`. Later on if we
wish to use `say_hello()` method, we could do something like this:

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

To compile both SASS and TypeScript, you can then use either `npm run dev` (startup watch on both compilers) or `npm run prod` to compile once.


## Conclusion

If you have any issues please raise using GitHub issues. There are not that many
cogs in this machine, which means solutions are likely easy to find. Further guides
on how to do other things will be added to the wiki soon.

Koda