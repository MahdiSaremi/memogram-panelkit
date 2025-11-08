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

    public function setUserBack(string $class, string $method, ?string $module = null)
    {
        $this->afterResolving(
            AreaRegister::class,
            function (AreaRegister $register) use ($class, $method, $module) {
                $register->putForNamespace(
                    'MemoGram\PanelKit\Mmb\Sections' . ($module ? '\\' . $module : ''),
                    'back',
                    [$class, $method],
                );
            },
        );
    }

    public function setAdminBack(string $class, string $method, ?string $module = null)
    {
        $this->afterResolving(
            AreaRegister::class,
            function (AreaRegister $register) use ($class, $method, $module) {
                $register->putForNamespace(
                    'MemoGram\PanelKit\Mmb\Sections\Admin' . ($module ? '\\' . $module : ''),
                    'back',
                    [$class, $method],
                );
            },
        );
    }


    protected function afterResolving(string $class, Closure $callback)
    {
        app()->afterResolving($class, $callback);

        if (app()->resolved(AreaRegister::class)) {
            $callback(app(AreaRegister::class));
        }
    }
}
