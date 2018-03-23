# Lamens

Speed up your Lumen with Swoole

## Requirements

| Dependency | Requirement |
| -------- | -------- |
| [PHP](https://secure.php.net/manual/en/install.php) | `>= 7.1.3` |
| [Swoole](https://www.swoole.co.uk/) | `>= 2.0.3` `The Newer The Better` |
| [Lumen](https://lumen.laravel.com/) | `>= 5.6` |

## Install


- Add lamens to you composer.json file and run `composer update`:

```
"corely/lamens": "dev-master"
```

or just run shell command:

```shell
composer require corely/lamens
```

- Register Lumen service provider, add the code to your `bootstrap/app.php`:

```
$app->register(\Lamens\Providers\LamensServiceProvider::class);
```

- Publish configuration

```
php artisan vendor:publish --provider="Lamens\Providers\LamensServiceProvider"
```

## Usage

```shell
php artisan lamens [start | stop | reload | reload_task | restart | quit]
```
