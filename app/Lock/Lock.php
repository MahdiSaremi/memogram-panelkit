<?php

namespace MemoGram\PanelKit\Lock;

use Carbon\Carbon;
use MemoGram\Api\Types\Update;
use MemoGram\PanelKit\Models\LockRequire;
use MemoGram\PanelKit\Models\LockStatus;
use function MemoGram\Handle\api;

class Lock
{
    public static function push(
        bool            $is_public,
        mixed           $chat_id,
        string          $url,
        string          $title,
        ?string         $group = null,
        bool            $is_fake = false,
        null|int|Carbon $cache_pass = null,
        null|int|Carbon $accept_delay = null,
        ?int            $member_limit_until = null,
        ?Carbon         $expire_at = null,
    ): LockRequire
    {
        if ($cache_pass instanceof Carbon) {
            $cache_pass = round($cache_pass->diff(now())->totalSeconds);
        }

        if ($accept_delay instanceof Carbon) {
            $accept_delay = round($accept_delay->diff(now())->totalSeconds);
        }

        return LockRequire::create([
            'group' => $group ?? 'main',
            'is_public' => $is_public,
            'chat_id' => $chat_id,
            'url' => $url,
            'title' => $title,
            'is_fake' => $is_fake,
            'cache_pass' => $cache_pass,
            'accept_delay' => $accept_delay,
            'member_limit_until' => $member_limit_until,
            'expire_at' => $expire_at,
        ]);
    }

    /**
     * @param string $group
     * @param Update $update
     * @return LockRequire[]
     */
    public static function check(string $group, Update $update): array
    {
        $isLocked = false;
        $locks = [];

        foreach (LockRequire::where('group', $group)->get() as $require) {
            switch (static::checkOne($require, $update)) {
                case self::LOCKED:
                    $isLocked = true;
                    $locks[] = $require;
                    break;

                case self::VISIBLE_RELEASED:
                    $locks[] = $require;
                    break;
            }
        }

        foreach (
            collect(config('panelkit.lock.fixed', []))
                ->whereInStrict('group', [null, $group])
            as $info
        ) {
            switch (static::checkStatic($info['chat_id'], $update)) {
                case self::LOCKED:
                    $isLocked = true;
                    $locks[] = new LockRequire($info);
                    break;

                case self::VISIBLE_RELEASED:
                    $locks[] = new LockRequire($info);
                    break;
            }
        }

        return $isLocked ? $locks : [];
    }

    public const LOCKED = 0;
    public const RELEASED = 1;
    public const VISIBLE_RELEASED = 2;

    public static function checkOne(LockRequire $require, Update $update): int
    {
        // Expiration
        if ($require->expire_at && $require->expire_at->isPast()) {
            $require->delete();
            return self::RELEASED;
        }

        // Member limitation
        if (
            $require->member_limit_until &&
            api()->getChatMemberCount(chat_id: $require->chat_id, ignore: true) >= $require->member_limit_until
        ) {
            $require->delete();
            return self::RELEASED;
        }

        // Fake mode
        if ($require->is_fake) {
            return self::VISIBLE_RELEASED;
        }

        // Cache system
        if ($require->cache_pass) {
            $status = LockStatus::query()
                ->where('lock_require_id', $require->id)
                ->where('unique_id', $update->getUserId())
                ->firstOrCreate([
                    'lock_require_id' => $require->id,
                    'unique_id' => $update->getUserId(),
                    'passed_at' => null,
                ]);

            if ($status->passed_at?->addSectonds($require->cache_pass)->isFuture()) {
                return self::RELEASED;
            }

            if (($result = self::checkStatic($require, $update)) == self::RELEASED) {
                $status->passed_at = now();
                $status->save();
            }

            return $result;
        }

        return self::checkStatic($require->chat_id, $update);
    }

    public static function checkStatic($chatId, Update $update): int
    {
        try {
            $member = api()->getChatMember(
                chat_id: $chatId,
                user_id: $update->getUserId(),
            );

            return $member->isJoined() ? self::RELEASED : self::LOCKED;
        } catch (\Throwable) {
            return self::VISIBLE_RELEASED;
        }
    }

    public static function getCondition(string $group): ?LockCondition
    {
        $class = config('panelkit.lock.condition');

        return $class ? new $class : null;
    }
}
