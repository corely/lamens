# Lamens

Speed up your Lumen with Swoole

## Depends On

<table>
	<tr>
		<td>php</td><td>>=7.1.3</td>
	</tr>
	<tr>
		<td>laravel/lumen</td><td>^ 5.6</td>
	</tr>
</table>

## Suggests

<table>
	<tr>
		<td>php</td><td>>=7.1.13</td>
	</tr>
	<tr>
		<td>ext-swoole</td><td>>=2.0.0</td>
	</tr>
</table>


## Install


- Add lamens to you composer.json file and run `composer update`:

```
"corely/lamens": "dev-master"
```

or just run shell command:

```shell
composer require corely/lamens
```

- register Lumen service provider, add the code to your `bootstrap/app.php`:

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

See Swoole's document:

[简体中文](http://wiki.swoole.com/wiki/page/274.html)

[English](https://cdn.rawgit.com/tchiotludo/swoole-ide-helper/dd73ce0dd949870daebbf3e8fee64361858422a1/docs/classes/swoole_server.html#method_set)
