<?php

namespace Kholil\Nitik\Services;

use Monolog\LogRecord;

class NitikHasher
{
    /**
     * Generate a unique hash for the log record.
     */
    public static function make(LogRecord $record, string $normalizedMessage): string
    {
        $exception = $record->context['exception'] ?? null;

        if ($exception instanceof \Throwable) {
            $data = [
                get_class($exception),
                $normalizedMessage,
                $exception->getFile(),
                $exception->getLine(),
            ];
        } else {
            $data = [
                $record->level->getName(),
                $normalizedMessage,
            ];
        }

        return md5(implode('|', $data));
    }
}
