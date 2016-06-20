# SeaLion

[![Travis CI Status](https://travis-ci.org/Toobo/SeaLion.svg?branch=master)](https://travis-ci.org/Toobo/SeaLion)

-----

### Table of Contents

 - [Introduction](#introduction)
 - [A New Approach: a Router for CLI](#a-new-approach-a-router-for-cli)
 - [Some Jargon](#some-jargon)
 - [Expectations](#expectations)
   - [Add Expectations](#add-expectations)
   - [Simple Example](#simple-example)
 - [Handlers](#handlers)
 - [Validate Expectations](#validate-expectations)
 - [Full Usage Example](#full-usage-example)
 - [Multiple Routes on Same Command](#multiple-routes-on-same-command)
 - [Input Classes](#input-classes)
 - [Custom Dispatchers](#custom-dispatchers)

---

 - [Requirements](#requirements)
 - [Installation](#installation)
 - [Unit Tests](#unit-tests)
 - [License](#license)

-----

# Introduction

PHP development for CLI is usually treated very differently from development for web.
However, from an higher level (and abstract) point of view they work pretty the same:

 1. User gives input
 2. Something is done by application
 3. A response is given back to user
 
Given that either points **1.** and **3.** are pretty different beetween web and CLI development,
what happen on point **2.**, that is the application business logic, in theory should not care
about the form of input and output, so should not be any difference in building applications for web or for CLI.

The main aim of this package it to provide a way to do for CLI what we can do for web since years:
build applications decoupling any of the 3 *steps* described above, without relying on large packages
that forces specific (and limited) application structure.

# A New Approach: a Router for CLI

Since years for web development we use "routers" as a way to map user input to application logic.
Why we can just do same thing for CLI?

SeaLion is just that: a router that map CLI input (arguments, options..) to *something*.
What that *something* is, or should be, is up to library consumers.

In this way is possible to write CLI applications **completely decoupled** by any library or framework.

# Some Jargon

In SeaLion input from CLI is defined by:

 - **Command** is always the **first argument** provided in CLI input (after the file path).
  Every CLI input has **only one** command.

 - **Arguments** are **positional params** for the command. 
  In fact, it is possible to distinguish one argument from another only by position, and not by name.

 - **Options** are **named params** for the command. They are passed to input by prefixing their name
  with a double hyphen `--`.

  Passing a value is optional (when omitted it is considered `true`).
  
  To pass a value, option name must be followed by an equal sign and the value itself: `--foo=bar`.
  
  It is also possible to wrap value with quotes: `--foo="bar"`. That is actually required
  to pass values that contain spaces: `--foo='bar bar bar'`.
  
 - **Flags** works **exactly the same of options**, but they are passed to input prefixing name with a single hyphen `-`, e.g.: `-bar="baz!"`.
 
_The "flags" / "options" names are used instead of [PHP "options" / "long options"](http://php.net/manual/en/function.getopt.php) names because I find the latter pretty confusing:
actually is not the option that is "long", just the literal._

For example, typing in CLI the text:

```
php app.php greet good morning -to="Giuseppe" --yell
```

SeaLion will recognize:

 - `'greet'` as the command
 - `'good'` and `'morning'` as two arguments
 - `'to'` as a flag with the value of `'Giuseppe'`
 - `'yell'` as an option with the value of `true`
 
Note that order of input only matters for command, that must be the first argument after file name,
for anything that follows command order doesn't matter and anything starts with `--` will be an option, anything that starts with `-` will be a flag and all the rest will be arguments.

# Expectations

Just like routers for HTTP requests, SeaLion works by setting expectations on the user input.
Expectations can be set on command (required), arguments, options and flags.

Expectations for arguments, options and flags can be set via:

 - **exact match**: to validate the expectation the param in the input must be exactly equal to the required string
 - **regex match**: to validate the expectation the param in the input must be match a given regex
 - **bool match**: to validate the expectation the param must be true or false. Note that an option or flag with no value is considered true,
   and a non provided param is considered false (so, set an expectation to `true` makes a param mandatory)
 - **callback match**: value provided in input is passed to a given callable, and the expectation validates if the callback returns true.
   
Expectation for commands can be set only via exact match.

Callback match is, of course, the most flexible of the options, and allows to do anything, e.g. you may accept and validate JSON input form console.

## Add Expectations

Most of the times, the only SeaLion object you'll need to interact with is the `Toobo\SeaLion\Router`.

Expectations are added via `Router::addCommand()` method, that returns an instance of `Toobo\SeaLion\Route\Route` class, on which is possible to call the methods:

 - `withArguments()`
 - `withOptions()`
 - `withFlags()`
 
to add expectations for, respectively, arguments, options and flags.

## Simple Example

```php
class_alias('Toobo\SeaLion\Router', 'Router');

$router = new Router();

$router->addCommand('greet', 'handler0')
    ->withArguments([0 => 'R{/^g[\w]+/i}', 1 => true])
    ->withFlags(['to' => 'Giuseppe']);
    ->withOptions(['yell' => function($yell) {
        return in_array($yell, ['', true, false], true);
    }]);
```

The expectations added above will be satisfied when:

 - the first argument (key `0`) matches the regex `/^g[\w]+/i`, because the syntax `"R{$regex}"` is used in SeaLion
   to add a regex expectations
 - the 2nd argument (key `1`) is provided and has a non-empty value
 - the flag `to` will be exactly "Giuseppe"
 - the option `yell` will be either an empty string, true, false.
   An option (or a flag)
     - is **true** when passed with no value: `--yell`
     - is **false** when not provided at all
     - is **an empty string** when set as so: `--yell=''` or when value is omitted, but equal sign provided: `--yell=`
     
For example, the following input validates all the expectations above:

```
php app.php greet --yell Good Morning -to=Giuseppe
```
 
# Handlers

I call **"Route"** the combination of command and param expectations. And I say "a route matched" when all its expectations are satisfied.

If more routes match, only the first (in order of addition) will be returned by router.

But what happen when a route matches?

Part of router response will be the *handler* that can be... whatever.

In the example above, the handler is the string `"handler0"`, but it can be a callable, an array, an object...

How to implement application flow is left to application.

SeaLion is just a router that maps some CLI input to some output: what that output should be and
how it has to be used is beyond SeaLion scope.


# Validate Expectations

To parse the added routes and get the matching route, the only thing needed is to *execute* the Router.
In fact, Router object is a *functor*, i.e. it has an `__invoke()` method that allows to call it just like it was a callback.

```php
$result = $router();
```

`$result` variable above will contain an array with 4 elements:

 - 1st element is **`true`** or **`false`** if the router has, respectively, found a matching route or not.
 - 2nd element is:
   - the **handler** if the a route matched. Handler may actually be whatever
   - if no route matched, it is a **bitmask of binary flags** that gives information why no match was found
 - 3rd element is the **command** that matched or `false` if no command matched.
   Note that a command may match even if no route matched, because of param expectations. 
 - 4th element is an array of **all** the input arguments user given, where
   - element with key `Router::ARGUMENTS` is an array of all arguments used in input
   - element with key `Router::OPTIONS` is an array of all options used in input
   - element with key `Router::FLAGS` is an array of all flags used in input
   

# Full Usage Example

This is a trivial, but complete usage example of SeaLion

```php
require 'vendor/autoload.php';

use Toobo\SeaLion\Router;

/**
 * An helper function to output some text in the console
 **/
function writeLine($text) {
  $f = fopen('php://stdout', 'w');
  fwrite($f, $text.PHP_EOL);
  fclose($f);
}

// In this trivial example we just have an array of callbacks
// where the one to execute is choosed based on the route
$handlers = [
    'handler0' => function($command, array $input) {
        writeLine('Command executed: '.$command);
        writeLine('Arguments used: '.json_encode($input[Router::ARGUMENTS]));
        writeLine('Options used: '.json_encode($input[Router::OPTIONS]));
        writeLine('Flags used: '.json_encode($input[Router::FLAGS]));
    },
    'handler1' => function($command, $input) {
        // do something interesting
    },
    'error' => function($errorBitmask, $command) {
        writeLine('Something gone wrong.');
        // let's use bitmask of error constants to output error message
        if ($command === false) {
            writeLine('No or invalid command was used.');
        } else {
            writeLine('The command '.$command.' was not used properly:');
            if ($errorBitmask & Router::ARGS_NOT_MATCHED) {
                writeLine('Arguments used were not valid.');
            }
            if ($errorBitmask & Router::OPTIONS_NOT_MATCHED) {
                writeLine('Options used were not valid.');
            }
            if ($errorBitmask & Router::FLAGS_NOT_MATCHED) {
                writeLine('Options used were not valid.');
            }
        }
    }
];

$router = new Router();

// add some commands and respective handlers
$router->addCommand('com1', 'handler0')->withArguments([true]); // 1st arg is required
$router->addCommand('com2', 'handler1');

$routeInfo = $router(); // execute the router

if ($routeInfo[0]) { // $routeInfo[0] is true when a route matched
    $handler = $routeInfo[1];
    call_user_func($handlers[$handler], $routeInfo[2], $routeInfo[3]);
} else {
    call_user_func($handlers['error'], $routeInfo[1], $routeInfo[2]);
}
```

Assuming the code above is saved in a file `app.php`, by running in console

```
php app.php com1 Hello! --test -a -b --foo="bar"
```

the output in console will be:

```
Command executed: com1
Arguments used: ["Hello!"]
Options used: {"test":true,"foo":"bar"}
Flags used: {"a":true,"b":true}
```

on the contrary, using:

```
php app.php com1 --test -a -b --foo="bar"
```

the output in console will be:

```
Something gone wrong.
The command com1 was not used properly:
Arguments used were not valid.
```

because first argument was required but not provided.


# A Better Output

How to use information provided by SeaLion is up to applications that use it.
However, very likely you want to output some text to console as a response.

The super-simple `fwrite` used in previous example just do it, however, may be fine being able to
format output, e.g. with some colors.

That's beyond SeaLion scope, but nothing prevent to use via Composer any library that do the trick,
and, of course, you can write your own code that does it.

Surely, [Symfony Console Component](http://symfony.com/doc/current/components/console/introduction.html) may be an option, but if there are alternatives, e.g. the lightweight and easy to use [ConsoleKit](https://github.com/maximebf/ConsoleKit) by [Maxime Bouroumeau-Fuseau](http://maximebf.com/).

Assuming you installed it via Composer, it's very simple to use it to output colored messages, e.g.:

```php
use ConsoleKit\Colors;

//...

$handlers = [
    'handler0' => function($command, array $input) {
        writeLine(
            Colors::cyan("Command executed: ")
            .Colors::colorize($command, Colors::GREEN|Colors::BOLD)
        );
        writeLine(
            Colors::magenta("Arguments used: ")
            .Colors::yellow(json_encode($input[Router::ARGUMENTS]))
        );
        writeLine(
            Colors::cyan("Options used: ")
            .Colors::green(json_encode($input[Router::OPTIONS]))
        );
        writeLine(
            Colors::magenta("Flags used: ")
            .Colors::yellow(json_encode($input[Router::FLAGS]))
        );
    },
    
    //...
]
```

Preview:

![Console colors preview](http://www.zoomlab.it/public/console_colors.gif)


# Multiple Routes on Same Command

In examples above, there is always one route per command. That's not a rule, in fact, it's possible to
have more routes on same command, using different param expectations.

```php
$router->addCommand('com1', 'handler0')->withFlags(['choose' => 'A']);
$router->addCommand('com1', 'handler1')->withFlags(['choose' => 'B']);
$router->addCommand('com1', 'handler2')->withFlags(['choose' => 'C']);
```

Using code above there are 3 routes for the `'com1'` command.

When it used with flag `choose` set to `'A'` first route matches and returned handler is `'handler0'`.
When the same flag has the value of `'B'` the second route matches (and returned handler is `'handler1'`), and finally the third route matches when the flag has the value of `'C'`.


# Input Classes

For any reason, e.g. for tests, may be desirable simulate console input to be parsed by SeaLion Router.

That can be done using an object that implements `Toobo\SeaLion\Input\InputInterface` interface.

SeaLion ships with 2 of these objects:

 - `Toobo\SeaLion\Input\ArgvInput`
 - `Toobo\SeaLion\Input\StringInput`
 
The first accepts an array of argument in `$_SERVER['argv']` format, the second accepts an input
as string.

Router constructor accepts an instance of Input object as first argument, when provided it is used to *simulate* console input.

Example:

```php
use Toobo\SeaLion\Router;
use Toobo\SeaLion\Input\StringInput;

$input = new StringInput('greet Good Morning --yell -name="Giuseppe"');
$router = new Router($input);
```

Same result of above, can be obtained with `ArgvInput` class:

```php
use Toobo\SeaLion\Router;
use Toobo\SeaLion\Input\ArgvInput;

$input = new ArgvInput(['greet', 'Good', 'Morning', '--yell', '-name="Giuseppe"']);
$router = new Router($input);
```

You can even write custom Input objects by extending `Toobo\SeaLion\Input\InputInterface` interface.


# Custom Dispatchers

As explained above, when router is executed it returns an array with information on matched route or
on the reason no route matched.

However, that is the *default* behaviour. In fact, SeaLion uses a class: `Toobo\SeaLion\Dispatcher\Dispatcher`
to return that results. It's always possible to write custom dispatcher classes by extending 
`Toobo\SeaLion\Dispatcher\DispatcherInterface` interface.

That is a [very simple interface](https://github.com/Toobo/SeaLion/blob/master/src/Dispatcher/DispatcherInterface.php)
with just 2 methods: `success()` and `error()`.

Implementing those 2 methods is possible to customize SeaLion behavior when a route matched and when not.

E.g. you may want to thrown an exception or run a default routine when no route matched; or
you may want only accept specific type of handlers, e.g. callbacks to be immediately executed.

It's really up to you.

To use a custom dispatcher you need to pass an instance of it as second param for router constructor.

Once first arguments is for custom input classes, you need to use `null` as first argument if you want
to override default Dispatcher but not Input, e.g.

```php
$dispatcher = new MyCustomDispatcher();
$router = new Router(null, $dispatcher);
```

-----

# Requirements

- PHP 5.4+
- Composer to install

# Installation

SeaLion is a Composer package available on Packagist and can be installed by running

```
composer require toobo/sealion:~0.1
```


# Unit Tests

SeaLion repository contains some unit tests written for [PHPUnit](https://phpunit.de/).

To run tests, navigate to repo folder from console and run:

```
phpunit
```

# License

SeaLion is released under MIT, see LICENSE file for more info.
