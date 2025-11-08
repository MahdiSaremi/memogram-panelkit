<?php

namespace MemoGram\PanelKit\Targets\Aim;

use Illuminate\Contracts\Database\Query\Builder;

interface TgAim
{
    public function getQuery(): Builder;
}
