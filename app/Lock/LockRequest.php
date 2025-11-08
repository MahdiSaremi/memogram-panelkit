<?php

namespace MemoGram\PanelKit\Lock;

use MemoGram\PanelKit\Models\LockRequire;
use function MemoGram\Handle\api;
use function MemoGram\Handle\update;
use function MemoGram\Hooks\getParam;
use function MemoGram\Hooks\glassKey;
use function MemoGram\Hooks\glassMessageResponse;
use function MemoGram\Hooks\refresh;
use function MemoGram\Hooks\stopPage;
use function MemoGram\Hooks\useState;

class LockRequest
{
    public string $group;
    protected array $locks;

    protected function checkLocks(bool $force = false)
    {
        if (!isset($this->locks)) {
            if (($this->condition ?? Lock::getCondition($this->group))?->show() === false) {
                $this->locks = [];
            }

            $this->locks = Lock::check($this->group, $this->update);
        }
    }

    public function main()
    {
        $this->group = useState(getParam('group', 'main'))->value;

        $this->checkLocks();
        $submit = __('panelkit::lock.submit');

        return glassMessageResponse()
            ->message(__('panelkit::lock.error', ['submit' => $submit]))
            ->schema([
                ...array_map(function (LockRequire $lock) {
                    return [glassKey($lock->title, url: $lock->url)];
                }, $this->locks),
                [glassKey($submit, 'submit')->then(function () {
                    if ($this->locks) {
                        api()->answerCallbackQuery(
                            callback_query_id: update()->callback_query->id,
                            text: __('panelkit::lock.submit_invalid'),
                            alert: true,
                            ignore: true,
                        );
                        refresh();
                    } else {
                        api()->deleteMessage(
                            chat_id: update()->getChatId(),
                            message_id: update()->callback_query->message->message_id,
                            ignore: true,
                        );
                    }
                })],
            ]);
    }

    public function useLock(string $group = 'main')
    {
        $passed = useState(false);

        return function () use ($passed, $group) {
            if ($passed->value) {
                return;
            }

            $submit = __('panelkit::lock.submit');
            $requires = Lock::check($group, update());

            yield glassMessageResponse()
                ->message(__('panelkit::lock.error', ['submit' => $submit]))
                ->schema([
                    ...array_map(function (LockRequire $lock) {
                        return [glassKey($lock->title, url: $lock->url)];
                    }, $requires),
                    [glassKey($submit, 'submit')->then(function () use ($requires, $passed) {
                        if ($requires) {
                            api()->answerCallbackQuery(
                                callback_query_id: update()->callback_query->id,
                                text: __('panelkit::lock.submit_invalid'),
                                alert: true,
                                ignore: true,
                            );
                        } else {
                            api()->deleteMessage(
                                chat_id: update()->getChatId(),
                                message_id: update()->callback_query->message->message_id,
                                ignore: true,
                            );

                            $passed->value = true;
                        }

                        refresh();
                    })],
                ]);

            yield stopPage();
        };
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
