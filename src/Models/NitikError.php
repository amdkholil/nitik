<?php

namespace Kholil\Nitik\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class NitikError extends Model
{
    protected $table = 'nitik_errors';

    protected $fillable = [
        'hash',
        'level',
        'exception_class',
        'message',
        'file',
        'line',
        'stack_trace',
        'count',
        'first_seen_at',
        'last_seen_at',
        'is_resolved',
    ];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'is_resolved' => 'boolean',
        'count' => 'integer',
        'line' => 'integer',
    ];

    /**
     * Create a new error record or aggregate into an existing one.
     */
    public static function createOrAggregate(array $data): void
    {
        $error = static::where('hash', $data['hash'])->first();

        if ($error) {
            $error->increment('count', 1, [
                'last_seen_at' => Carbon::now(),
                'is_resolved' => false, // Re-open if it was resolved
            ]);
        } else {
            static::create(array_merge($data, [
                'count' => 1,
                'first_seen_at' => Carbon::now(),
                'last_seen_at' => Carbon::now(),
                'is_resolved' => false,
            ]));
        }
    }
}
