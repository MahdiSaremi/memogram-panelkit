<?php

namespace MemoGram\PanelKit\Sections\Admin;

use MemoGram\Handle\Area;

class _Area extends Area
{
    public function back(): ?array
    {
        return config('panelkit.back.admin');
    }
}