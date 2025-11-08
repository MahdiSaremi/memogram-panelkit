<?php

namespace MemoGram\PanelKit\Models;

use Illuminate\Database\Eloquent\Model;

class LockStatus extends Model
{
    protected $fillable = [
        'lock_require_id',
        'unique_id',
        'passed_at',
    ];

    protected $casts = [
        'passed_at' => 'datetime',
    ];
}
