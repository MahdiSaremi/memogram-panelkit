<?php

namespace MemoGram\PanelKit;

use Closure;
use function MemoGram\Handle\handleAs;

class PanelKitFactory
{
    protected string $userClass;

    public function setUserClass(string $class): void
    {
        $this->userClass = $class;
    }

    public function getUserClass(): string
    {
        return $this->userClass ?? throw new \Exception("User model is not defined");
    }

    public function runWithDefaultApi(Closure $callback): void
    {
        handleAs(app(config('panelkit.api.instance')), $callback);
    }
}
