<?php

namespace MemoGram\PanelKit;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void setUserClass(string $class)
 * @method static string getUserClass()
 * @method static void setUserBack(string $class, string $method, ?string $module = null)
 * @method static void setAdminBack(string $class, string $method, ?string $module = null)
 */
class PanelKit extends Facade
{
    protected static function getFacadeAccessor()
    {
        return PanelKitFactory::class;
    }
}
