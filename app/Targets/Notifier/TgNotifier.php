<?php

namespace MemoGram\PanelKit\Targets\Notifier;

use Illuminate\Database\Eloquent\Model;

interface TgNotifier
{
    /**
     * Fire notification for an entity
     *
     * @param Model $record
     * @return bool
     */
    public function notify(Model $record): bool;
}
