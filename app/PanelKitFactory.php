<?php

namespace MemoGram\PanelKit;

use Closure;

class PanelKitFactory
{
    protected string $userClass;

    public function setUserClass(string $class)
    {
        $this->userClass = $class;
    }

    public function getUserClass()
    {
        return $this->userClass ?? throw new \Exception("User model is not defined");
    }
}
