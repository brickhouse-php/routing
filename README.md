# Brickhouse Routing

This library is a regex-based routing mechanism for PHP applications. It is heavily based on nikic' [FastRoute](https://github.com/nikic/FastRoute) project.

## Installation

To install the library, you need to require the package via composer:

```bash
composer require brickhouse/routing
```

## Usage

To add routes to the dispatcher, you can create a `RouteCollector` like so:

```php
use Brickhouse\Routing;

$collector = new RouteCollector();
$collector->addRoute('GET', '/', fn() => 'Hello World!');
$collector->addRoute('GET', '/posts/:id', fn() => render_post());
$collector->addRoute('POST', '/posts', fn() => create_post());
```

The collector can then be passed to a dispatcher, which can match the routes:

```php
$dispatcher = new Dispatcher($collector);

$route = $dispatcher->dispatch('GET', '/');
```

When a route is matched, `$route` will be an array of the route handler, as well as the parameters in the route. If no route was matched, `$route` is `null`.

### Defining routes

#### Required parameters

Parameters can be defined by adding a colon to the beginning of a route segment, like so:

```php
$collector->addRoute('GET', '/posts/:id', '...');
```

When the route is matched, the captured parameter value will be returned in the dispatched route:

```php
[$handler, $parameters] = $dispatcher->dispatch('GET', '/posts/routing');

echo $parameters;

// ['id' => 'routing']
```

You may define as many parameters in a single route as required:

```php
$collector->addRoute('GET', '/users/:user/posts/:post', '...');
```

Route parameters names consist of alphanumeric (`[A-Za-z0-9]`) characters and underscores (`_`).

#### Optional parameters

Parameters can also be marked as optional, allowing the route to be dispatched by multiple routes. Optional parameters are defined by placing a `?`-mark after the colon:

```php
$collector->addRoute('GET', '/user/:?id', '...');
```

When the parameter is omitted from the matched, it will be set as `null` in the matched route:

```php
[$handler, $parameters] = $dispatcher->dispatch('GET', '/user');

echo $parameters;

// ['id' => null]
```

#### Catch-all parameters

Parameters can be extended to catch all subsequent segments in the path. Catch-all parameters are defined by replacing the colon with an asterisk (`*`):

```php
$collector->addRoute('GET', '/posts/*slug', '...');
```

When using a catch-all parameter, all subsequent segments are returned as a single string.

```php
$dispatcher->dispatch('GET', '/posts/a');
// $parameters => ['slug' => 'a']

$dispatcher->dispatch('GET', '/posts/a/b');
// $parameters => ['slug' => 'a/b']

$dispatcher->dispatch('GET', '/posts/a/b/c');
// $parameters => ['slug' => 'a/b/c']
```

Catch-all parameters also require a value to be given before matching:

```php
$route = $dispatcher->dispatch('GET', '/posts');
// $route => null
```

To allow the catch-all to match routes without giving the parameter, you can mark it as optional:

```php
$collector->addRoute('GET', '/posts/*?slug', '...');

// ...

$dispatcher->dispatch('GET', '/posts');
// $parameters => ['slug' => null]

$dispatcher->dispatch('GET', '/posts/a');
// $parameters => ['slug' => 'a']
```

#### Custom pattern constraints

You may write your own constraints for route parameters using regular expressions. Constraints are defined per-argument and can be written like so:

```php
$collector->addRoute('GET', '/user/:user_id', '...', ['user_id' => '\d+']);
```

This limits which values can be matched with the route. If the route doesn't match, it'll continue looking for one that does.

```php
$route = $dispatcher->dispatch('GET', '/user/423');
// $route => ['...', ['user_id' => '423']]

$route = $dispatcher->dispatch('GET', '/user/admin');
// $route => null
```

## Performance

Performance is measured using [PHPBench](https://github.com/phpbench/phpbench), running the benchmarks in [bench](./bench/).

All setups are different, but this bench was run on a Mac Mini M4 (16GB, Sequoia 15.3), with PHP 8.4.1 where both XDebug and OPCache disabled.

| benchmark               | subject                | mean    | revs  | total  |
| :---------------------- | :--------------------- | :------ | :---- | :----  |
| `MicroDynamicBenchmark` | `benchDispatchFirst`   | 0.652μs | 20000 | 0.013s |
| `MicroDynamicBenchmark` | `benchDispatchLast`    | 6.322μs | 20000 | 0.126s |
| `MicroDynamicBenchmark` | `benchDispatchUnknown` | 5.530μs | 20000 | 0.111s |
| `MicroStaticBenchmark`  | `benchDispatchFirst`   | 0.307μs | 30000 | 0.009s |
| `MicroStaticBenchmark`  | `benchDispatchLast`    | 4.394μs | 30000 | 0.132s |
| `MicroStaticBenchmark`  | `benchDispatchUnknown` | 4.724μs | 30000 | 0.142s |

## License

Brickhouse Routing is open-sourced software licensed under the [MIT license](LICENSE.md).