<?php

namespace MemoGram\PanelKit\Targets\Aim;

use Illuminate\Contracts\Database\Query\Builder;
use MemoGram\PanelKit\PanelKit;

class TgAllAim implements TgAim
{
    public function getQuery(): Builder
    {
        return (PanelKit::getUserClass())::query()->orderBy('created_at');
    }
}
