<?php

namespace Kholil\Nitik\Handlers;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Kholil\Nitik\Services\NitikNormalizer;
use Kholil\Nitik\Services\NitikHasher;
use Kholil\Nitik\Models\NitikError;
use Throwable;

class NitikLogHandler extends AbstractProcessingHandler
{
    protected function write(LogRecord $record): void
    {
        try {
            $configLevels = (array) config('nitik.log_levels', ['error', 'critical', 'emergency']);
            
            // Check if level should be logged
            if (!in_array(strtolower($record->level->getName()), $configLevels)) {
                return;
            }

            $exception = $record->context['exception'] ?? null;
            $exceptionClass = $exception instanceof Throwable ? get_class($exception) : null;

            // 3. IGNORE LIST
            $ignoredExceptions = (array) config('nitik.ignore_exceptions', []);
            if ($exceptionClass && in_array($exceptionClass, $ignoredExceptions)) {
                return;
            }

            // 2. SELF-FILTERING (by namespace)
            if ($exceptionClass && str_contains($exceptionClass, 'Kholil\\Nitik')) {
                return;
            }

            $normalizedMessage = NitikNormalizer::normalize($record->message);
            $hash = NitikHasher::make($record, $normalizedMessage);

            $file = null;
            $line = null;
            $trace = null;

            if ($exception instanceof Throwable) {
                $file = $exception->getFile();
                $line = $exception->getLine();
                $trace = $exception->getTraceAsString();
            } else {
                // No exception object - capture current call stack
                $exceptionClass = $exceptionClass ?? (in_array(strtolower($record->level->getName()), ['error', 'critical', 'emergency', 'alert']) ? 'Error' : null);
                
                $fullTrace = (new \Exception())->getTraceAsString();
                $lines = explode("\n", $fullTrace);
                
                // Filter internal calls from Monolog, Laravel Log baggage, and Nitik's own internal handling
                $filteredLines = array_filter($lines, function ($line) {
                    return !str_contains($line, 'vendor/monolog') && 
                           !str_contains($line, 'Monolog/') &&
                           !str_contains($line, 'Kholil/Nitik/Handlers') && 
                           !str_contains($line, 'Kholil/Nitik/Services') &&
                           !str_contains($line, 'kholil/nitik/src/Handlers') &&
                           !str_contains($line, 'kholil/nitik/src/Services') &&
                           !str_contains($line, 'Illuminate/Log') &&
                           !str_contains($line, 'Illuminate/Support/Facades');
                });
                
                $trace = implode("\n", array_values($filteredLines));

                // Extract file and line from the first line of filtered trace
                if (!empty($filteredLines)) {
                    $firstFrame = reset($filteredLines);
                    if (preg_match('/#\d+ (.*?)\((\d+)\):/', $firstFrame, $matches)) {
                        $file = $matches[1];
                        $line = (int) $matches[2];
                    }
                }
            }

            // Fallback for file/line from context if still missing
            $file = $file ?? ($record->context['file'] ?? null);
            $line = $line ?? ($record->context['line'] ?? null);

            // 2. SELF-FILTERING (by file path)
            if ($file && (str_contains($file, 'kholil/nitik') || str_contains($file, 'Kholil/Nitik'))) {
                return;
            }

            $data = [
                'hash' => $hash,
                'level' => $record->level->getName(),
                'message' => $normalizedMessage,
                'exception_class' => $exceptionClass,
                'file' => $file,
                'line' => $line,
                'stack_trace' => $trace,
            ];

            NitikError::createOrAggregate($data);
        } catch (Throwable $e) {
            // 1. INFINITE LOOP PROTECTION
            // Silently fail to prevent log-triggered recursion (e.g., if DB fails)
        }
    }
}
