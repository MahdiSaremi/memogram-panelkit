<?php

namespace MemoGram\PanelKit;

use Illuminate\Support\Facades\Facade;

class PanelKit extends Facade
{
    protected static function getFacadeAccessor()
    {
        return PanelKitFactory::class;
    }
}
