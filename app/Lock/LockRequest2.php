<?php

namespace MemoGram\PanelKit\Lock;

use MemoGram\PanelKit\Models\LockRequire;
use Mmb\Action\Section\Attributes\FixedDialog;
use Mmb\Action\Section\Controllers\CallbackControl;
use Mmb\Action\Section\Dialog;
use Mmb\Action\Section\Section;
use Mmb\Action\Update\UpdateHandling;
use Mmb\Context;
use Mmb\Core\Updates\Update;
use Mmb\Support\Format\KeyFormatter;
use Mmb\Support\Telegram\Keys;

class LockRequest2 extends Section implements UpdateHandling
{
    use CallbackControl;

    protected LockCondition $condition;

    /**
     * Set the condition
     *
     * @param LockCondition|string $condition
     * @return $this
     */
    public function withCondition(LockCondition|string $condition)
    {
        $this->condition = is_string($condition) ? new $condition : $condition;
        return $this;
    }


    public string $group;

    protected array|false $locks;

    public function isRequired(): bool
    {
        $this->checkLocks(true);

        if ($this->locks !== false) {
            return true;
        }

        return false;
    }

    protected function checkLocks(bool $force = false)
    {
        if (!isset($this->locks)) {
            if (($this->condition ?? Lock::getCondition($this->group))?->show() === false) {
                $this->locks = false;
            }

            $this->locks = Lock::check($this->group, $this->update);
        }
    }

    public function main()
    {
        $this->dialog('mainDialog')->response();
    }

    #[FixedDialog('lock:{group:slug}')]
    public function mainDialog(Dialog $dialog)
    {
        $dialog->with('group');

        $this->checkLocks();
        $submit = __('panelkit::lock.submit');

        if ($dialog->isCreating()) {
            $dialog
                ->schema(
                    KeyFormatter::value(
                        function () use ($dialog) {
                            if ($this->locks) {
                                /** @var LockRequire $lock */
                                foreach ($this->locks as $lock) {
                                    yield [Keys::url($lock->title, $lock->url)];
                                }
                            }
                        },
                    ),
                );
        }

        $dialog
            ->message(fn() => __('panelkit::lock.error', ['submit' => $submit]))
            ->schema(
                [
                    [$dialog->keyId($submit, 'submit')],
                ],
            )
            ->on(
                'submit',
                function () use ($dialog) {
                    if ($this->locks) {
                        $this->tell(__('panelkit::lock.submit_invalid'), alert: true);
                        $dialog->reload();
                    } else {
                        $this->update->getMessage()?->delete(ignore: true);
                    }
                },
            );
    }

    public static function for(Context $context, string $group)
    {
        $instance = static::make($context);
        $instance->group = $group;

        return $instance;
    }

    public function handleUpdate(Context $context, Update $update)
    {
        if ($this->isRequired()) {
            $this->main();
        } else {
            $update->skipHandler();
        }
    }

    public function required()
    {
        if ($this->isRequired()) {
            $this->main();
            $this->update->stopHandling();
        }
    }
}
