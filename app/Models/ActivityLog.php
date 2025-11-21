<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'module',
        'action',
        'description',
        'data',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public static function record(string $module, string $action, ?string $description = null, $data = null): void
    {
        $request = request();

        static::create([
            'user_id'    => optional(auth()->user())->id,
            'module'     => $module,
            'action'     => $action,
            'description'=> $description,
            'data'       => $data ? (is_array($data) ? $data : (array) $data) : null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}
