<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notification';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'url',
        'type',
        'created_at',
        'read_at',
        'email_sent_at',
    ];

    protected $attributes = [
        'type' => 'info',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'created_at' => 'integer',
        'read_at' => 'integer',
        'email_sent_at' => 'integer',
    ];

    public static function unreadCount(int $userId): int
    {
        return (int) static::where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    // En Yii, user_id nulo fallaba la validación y devolvía null; alumnos sin
    // cuenta de portal se saltan aquí para no interrumpir el envío masivo.
    public static function createForUser(?int $userId, string $title, string $message, ?string $url = null, string $type = 'info'): ?self
    {
        if ($userId === null) {
            return null;
        }

        try {
            $duplicate = static::where('user_id', $userId)
                ->where('title', $title)
                ->where('message', $message)
                ->where('url', $url)
                ->where('created_at', '>=', time() - 86400)
                ->latest('id')
                ->first();

            if ($duplicate) {
                return $duplicate;
            }

            return static::create([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'url' => $url,
                'type' => $type,
                'created_at' => time(),
            ]);
        } catch (\Throwable) {
            return null;
        }
    }
}
