# Lamens

> Speed up your Lumen with Swoole

[![Latest Stable Version](https://poser.pugx.org/corely/lamens/v/stable)](https://packagist.org/packages/corely/lamens)
[![Total Downloads](https://poser.pugx.org/corely/lamens/downloads)](https://packagist.org/packages/corely/lamens)
[![Latest Unstable Version](https://poser.pugx.org/corely/lamens/v/unstable)](https://packagist.org/packages/corely/lamens)
[![License](https://poser.pugx.org/corely/lamens/license)](https://packagist.org/packages/corely/lamens)
[![Build Status](https://travis-ci.org/corely/lamens.svg?branch=master)](https://travis-ci.org/corely/lamens)

## Requirements

| Dependency | Requirement |
| -------- | -------- |
| [PHP](https://secure.php.net/manual/en/install.php) | `>= 7.1.3` |
| [Swoole](https://www.swoole.co.uk/) | `>= 2.0.7` |
| [Lumen](https://lumen.laravel.com/) | `>= 5.6.2` |

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
php artisan lamens [start | stop | restart | status | reload | reload_task]
```
