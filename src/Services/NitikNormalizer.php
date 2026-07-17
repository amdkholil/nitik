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

    /**
     * Scrub sensitive values from a string (e.g. stack trace or log message).
     */
    public static function sanitize(?string $text): ?string
    {
        if (null === $text) {
            return null;
        }

        $sensitiveKeys = ['password', 'secret', 'token', 'key', 'authorization', 'signature', 'cookie', 'credential', 'cert', 'passwd'];
        
        foreach ($sensitiveKeys as $key) {
            // 1. JSON/Array-like: 'key' => 'val', "key": "val", 'key' = 'val'
            $pattern = '/([\'"]?' . $key . '[\'"]?\s*[:=]>?\s*[\'"])(.*?)([\'"])/i';
            $text = preg_replace($pattern, '$1********$3', $text);
            
            // 2. Query/URL parameters or basic key=value: key=value
            $patternUrl = '/\b(' . $key . ')=([^&\s"\']+)/i';
            $text = preg_replace($patternUrl, '$1=********', $text);
        }

        // 3. Mask Authorization Bearer tokens
        $text = preg_replace('/(Authorization:\s*Bearer\s+)(\S+)/i', '$1********', $text);

        return $text;
    }
}
