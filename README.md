# Laravel Telegram WebApp package

> **Fork notice.** This is a fork of
> [micromagicman/laravel-telegram-webapp](https://github.com/micromagicman/laravel-telegram-webapp),
> branch `laravel-13`, based on upstream `v4.0.0`.
>
> **Why it exists.** Upstream caps `illuminate/support` and `illuminate/routing` at `^12.0`,
> which blocks Laravel 13 — and Laravel 13 is what Pest 5 needs, since Pest 5 requires
> `symfony/process ^8.1` while Laravel 12 only allows `^7.2`. The caps are widened here to
> `^12.0||^13.0`.
>
> Nothing in the Laravel-facing surface needed porting: the package only uses
> `ServiceProvider`, `Router::aliasMiddleware`, `Request`, `Log` and the `config`/`abort`/`__`
> helpers, none of which changed in Laravel 13. The suite passes on Laravel 13 with PHPUnit 13.
>
> **Fixes carried on top**, each covered by a test that fails without it:
>
> - initData missing `auth_date` or `hash`, or carrying an array value (`?user[]=x`), returned
>   **500** instead of the configured 403. Those keys are part of what Telegram signs, so their
>   absence means forged data and belongs in the normal rejection path.
> - The signature comparison used `===`; it now uses `hash_equals`.
> - `TelegramUser` assigned every key Telegram sent, so new ones (`photo_url`,
>   `added_to_attachment_menu`, …) became dynamic properties — deprecated since PHP 8.2, an
>   error in PHP 9. Unknown keys are now ignored.
> - Optional user fields (`last_name`, `username`, `language_code`, `is_premium`,
>   `allows_write_to_pm`) were left uninitialized, so reading one for a user who has no last
>   name or username threw instead of returning an empty value. They now default.
> - `Time::expired()` declared `: int` while returning `bool`; `json_decode` was passed
>   `JSON_OBJECT_AS_ARRAY` as its `$associative` argument; `verifyInitData(null)` dereferenced
>   null instead of falling back to the current request, as `getWebAppUser` already did.
> - Dropped two `use` statements pointing at classes that do not exist in the package.
>
> Track [upstream](https://github.com/micromagicman/laravel-telegram-webapp) and drop this fork
> once it ships Laravel 13 support of its own.

Laravel package that allows you to process commands from Telegram MiniApp with user verification according to
[Telegram MiniApp developer documentation](https://core.telegram.org/bots/webapps), as well as obtaining information
about the Telegram user who sent the request

## Requirements

| Laravel | micromagicman/laravel-telegram-webapp |
|---------|---------------------------------------|
| 10.x    | 1.x.x                                 |
| 11.x    | 2.x.x                                 |
| 12.x    | 3.x.x                                 |
| 13.x    | this fork, branch `laravel-13`        |

## Install

### Via composer

Upstream, for Laravel 12 and below:

```bash
composer require micromagicman/laravel-telegram-webapp
```

This fork, for Laravel 13 — add the repository, then require the branch:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/qweik/laravel-telegram-webapp.git"
        }
    ],
    "require": {
        "micromagicman/laravel-telegram-webapp": "dev-laravel-13 as 4.0.0"
    }
}
```

The `as 4.0.0` alias keeps the branch satisfying any `^4.0` constraint another package may
declare against it.

### Publishing

Publish to your Laravel application:

```bash
php artisan vendor:publish --provider="Micromagicman\TelegramWebApp\TelegramWebAppServiceProvider"
```

## Configure

All package configuration available in `config/telegram-webapp.php` file after `publish` command execution:

| Config name               | Description                                                                                                                                                                                                                                                                                                                                                         | Environment                               | Default value                                 |
|---------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|-------------------------------------------|-----------------------------------------------|
| `enabled`                 | Telegram MiniApp data validation switch                                                                                                                                                                                                                                                                                                                             | `TELEGRAM_WEBAPP_DATA_VALIDATION_ENABLED` | `true`                                        |
| `webAppScriptLocation`    | Path to script (.js) which initializes Telegram MiniApp on your frontend app                                                                                                                                                                                                                                                                                        | -                                         | `https://telegram.org/js/telegram-web-app.js` |
| `botToken`                | Your Telegram bot token                                                                                                                                                                                                                                                                                                                                             | `TELEGRAM_BOT_TOKEN`                      | -                                             |
| `error.status`            | HTTP status code when Telegram MiniApp data validation fails                                                                                                                                                                                                                                                                                                        | -                                         | 403 (Forbidden)                               |
| `error.message`           | Error message returned when Telegram MiniApp data validation fails                                                                                                                                                                                                                                                                                                  | -                                         | 403 (Forbidden)                               |
| `authDateLifetimeSeconds` | The lifetime of the Telegram initData auth_date parameter in seconds. The request to the server must be made within this interval, otherwise the data transmitted from Telegram will be considered invalid. The values of the parameter <= 0 imply that there is no verification of the lifetime of data from telegram and the auth_date parameter is not validated | -                                         | 0                                             |

Example in code:

## View

This package provides a root view for Telegram MiniApp frontend applications.
[Telegram WebApp script](https://telegram.org/js/telegram-web-app.js) is automatically includes to this view or its
inheritors if `telegram-webapp.enabled` switch is `true`

Example:

```php
@extends('telegram-webapp::main')

@section('lang', 'CN')

@section('head')
// some scripts, css, meta
@endsection

@section('title', 'My title')

@section('content')
    <div id="app-content">
        // My spa content
    </div>
@endsection
```

## Integration with `TelegramBot\Api\BotApi`

Our service integrates with `TelegramBot\Api\BotApi`, allowing you to access all of the methods provided by the Telegram Bot API. This integration is available either through a **Facade** or directly through the service.

You can find the repository for `TelegramBot\Api\BotApi` [here](https://github.com/TelegramBot/Api).

### Using the Facade

To use the Telegram Bot API methods, you can leverage the **TelegramFacade** facade (aliased to `TelegramWebApp`). This provides a simple and convenient way to interact with the Telegram Bot API.

Example usage with the Facade:

```php
use Micromagicman\TelegramWebApp\Facade\TelegramFacade as TelegramWebApp;

$response = TelegramWebApp::getMe();
```

This allows you to call methods like `getMe()`, `sendMessage()`, `getUpdates()`, and any other method from the `BotApi` class directly through the facade.

### Using the Service Directly

You can also interact with the Telegram Bot API directly through the service. Inject the `TelegramWebAppService` into your components, and call the Bot API methods via the service instance.

Example usage in a controller:

```php
use Micromagicman\TelegramWebApp\Service\TelegramWebAppService;

class MyController extends Controller
{
    protected $telegram;

    public function __construct(TelegramWebAppService $telegram)
    {
        $this->telegram = $telegram;
    }

    public function getBotInfo()
    {
        $response = $this->telegram->getMe();
        return response()->json($response);
    }
}
```

Both the facade and the service offer full access to the `BotApi` class methods, 
allowing you to work seamlessly with the Telegram Bot API in your Laravel application.