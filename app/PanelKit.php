<?php

namespace MemoGram\PanelKit;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void setUserClass(string $class)
 * @method static string getUserClass()
 */
class PanelKit extends Facade
{
    protected static function getFacadeAccessor()
    {
        return PanelKitFactory::class;
    }
}
