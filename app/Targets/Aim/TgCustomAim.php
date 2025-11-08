<?php

namespace MemoGram\PanelKit\Targets\Aim;

use Closure;
use Illuminate\Contracts\Database\Query\Builder;
use Laravel\SerializableClosure\SerializableClosure;

class TgCustomAim implements TgAim
{
    public SerializableClosure $callback;

    public function __construct(
        Closure $callback,
    )
    {
        $this->callback = new SerializableClosure($callback);
    }

    public function getQuery(): Builder
    {
        return $this->callback->__invoke();
    }
}
