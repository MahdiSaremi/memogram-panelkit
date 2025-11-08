<?php

namespace MemoGram\PanelKit\Models;

use Illuminate\Database\Eloquent\Model;

class LockRequire extends Model
{
    protected $fillable = [
        'group',
        'is_public',
        'chat_id',
        'url',
        'title',
        'is_fake',
        'cache_pass',
        'accept_delay',
        'member_limit_until',
        'expire_at',
    ];

    protected $casts = [
        'expire_at' => 'datetime',
    ];
}
