<?php

namespace MemoGram\PanelKit\Lock;

use MemoGram\PanelKit\Models\LockRequire;
use function MemoGram\Handle\api;
use function MemoGram\Handle\update;
use function MemoGram\Hooks\getParam;
use function MemoGram\Hooks\glassKey;
use function MemoGram\Hooks\glassMessageResponse;
use function MemoGram\Hooks\open;
use function MemoGram\Hooks\refresh;
use function MemoGram\Hooks\stopPage;
use function MemoGram\Hooks\useState;

class LockRequest
{
    protected array $locks;

    public function openRequest(string $group = 'main'): bool
    {
        if ($this->locks = Lock::check($group, update())) {
            open([$this, 'main'], ['group' => $group]);
            return true;
        }

        return false;
    }

    public function main()
    {
        $group = useState(getParam('group', 'main'));
        $requires = $this->locks ?? Lock::check($group->value, update());

        $submit = __('panelkit::lock.submit');

        return glassMessageResponse()
            ->message(__('panelkit::lock.error', ['submit' => $submit]))
            ->schema([
                ...array_map(function (LockRequire $lock) {
                    return [glassKey($lock->title, url: $lock->url)];
                }, $requires),
                [glassKey($submit, 'submit')->then(function () use ($requires) {
                    if ($requires) {
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
}
