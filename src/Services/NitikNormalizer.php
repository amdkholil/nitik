<?php

namespace Kholil\Nitik\Services;

class NitikNormalizer
{
    /**
     * Normalize the log message by stripping dynamic values.
     */
    public static function normalize(string $message): string
    {
        // Replace UUIDs
        $message = preg_replace(
            '/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i',
            '{uuid}',
            $message
        );

        // Replace standalone numbers (IDs)
        $message = preg_replace('/\b\d+\b/', '{id}', $message);

        // Replace absolute paths
        $message = preg_replace('/\/\S+/', '{path}', $message);

        return $message;
    }
}
