<?php

namespace MemoGram\PanelKit\Lock;

interface LockCondition
{
    public function show(): bool;
}