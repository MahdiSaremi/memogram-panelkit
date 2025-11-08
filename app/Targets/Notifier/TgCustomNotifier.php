<?php

namespace MemoGram\PanelKit\Targets\Notifier;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Laravel\SerializableClosure\SerializableClosure;

class TgCustomNotifier implements TgNotifier
{
    public SerializableClosure $callback;

    public function __construct(
        Closure $callback,
    )
    {
        $this->callback = new SerializableClosure($callback);
    }

    public function notify(Model $record): bool
    {
        return (bool)$this->callback->__invoke($record);
    }
}
