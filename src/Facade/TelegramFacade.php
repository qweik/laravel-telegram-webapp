<?php

namespace Micromagicman\TelegramWebApp\Facade;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Micromagicman\TelegramWebApp\Dto\TelegramUser;
use Micromagicman\TelegramWebApp\Service\TelegramWebAppService;

/**
 * A static proxy to {@link TelegramWebAppService} service
 * @method static ?TelegramUser getWebAppUser( ?Request $request = null )
 * @method static bool verifyInitData( ?Request $request = null )
 * @method static void abortWithError( array $errorMessageParams = [] )
 */
class TelegramFacade extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return TelegramWebAppService::class;
    }
}