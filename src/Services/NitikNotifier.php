<?php

namespace Kholil\Nitik\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Kholil\Nitik\Mail\NitikErrorMail;
use Kholil\Nitik\Models\NitikError;
use Throwable;

class NitikNotifier
{
    public static function send(NitikError $error, bool $isNew): void
    {
        try {
            if (!config('nitik.notifications.enabled', false)) {
                return;
            }

            $onlyFirst = config('nitik.notifications.only_first_occurrence', true);
            if ($onlyFirst && !$isNew) {
                return;
            }

            static::sendMail($error);
            static::sendDiscord($error);
        } catch (Throwable $e) {
            // Silently swallow errors to prevent infinite log loops
        }
    }

    protected static function sendMail(NitikError $error): void
    {
        $mailConfig = config('nitik.notifications.channels.mail', []);
        if (empty($mailConfig['enabled']) || empty($mailConfig['to'])) {
            return;
        }

        Mail::to($mailConfig['to'])->send(new NitikErrorMail($error));
    }

    protected static function sendDiscord(NitikError $error): void
    {
        $discordConfig = config('nitik.notifications.channels.discord', []);
        $webhookUrl = $discordConfig['webhook_url'] ?? null;

        if (empty($discordConfig['enabled']) || empty($webhookUrl)) {
            return;
        }

        $color = match (strtoupper($error->level)) {
            'EMERGENCY', 'CRITICAL', 'ERROR' => 15158332, // Red
            'WARNING' => 15105570, // Orange
            default => 3447003, // Blue
        };

        Http::post($webhookUrl, [
            'embeds' => [
                [
                    'title' => "🚨 [{$error->level}] {$error->exception_class}",
                    'description' => substr($error->message ?? '', 0, 2000),
                    'color' => $color,
                    'fields' => [
                        [
                            'name' => 'File',
                            'value' => "{$error->file}:{$error->line}",
                            'inline' => true,
                        ],
                        [
                            'name' => 'Occurrences',
                            'value' => (string) $error->count,
                            'inline' => true,
                        ],
                    ],
                    'timestamp' => now()->toISOString(),
                ]
            ]
        ]);
    }
}
