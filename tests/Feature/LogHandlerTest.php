<?php

use Illuminate\Support\Facades\Log;
use Kholil\Nitik\Models\NitikError;

it('captures errors and saves to database', function () {
    Log::channel('nitik')->error('Test error message', [
        'exception' => new Exception('Test exception')
    ]);

    expect(NitikError::count())->toBe(1);
    
    $error = NitikError::first();
    expect($error->message)->toBe('Test error message');
    expect($error->level)->toBe('ERROR');
    expect($error->exception_class)->toBe('Exception');
});

it('aggregates same errors', function () {
    Log::channel('nitik')->error('Duplicate error');
    Log::channel('nitik')->error('Duplicate error');

    expect(NitikError::count())->toBe(1);
    expect(NitikError::first()->count)->toBe(2);
});

it('does not capture ignored levels', function () {
    config(['nitik.log_levels' => ['error']]);
    
    Log::channel('nitik')->warning('Test warning');

    expect(NitikError::count())->toBe(0);
});

it('scrubs sensitive values from logs and stack traces', function () {
    // Test helper directly
    $dirtyTrace = "some_function('my-secret-token')\n/path/to/file.php: password=secret_password";
    $cleanTrace = \Kholil\Nitik\Services\NitikNormalizer::sanitize($dirtyTrace);
    expect($cleanTrace)->toContain('password=********');
    expect($cleanTrace)->not->toContain('secret_password');

    Log::channel('nitik')->error('Failed login attempt for password="secret_password" and key=secret_key');

    expect(NitikError::count())->toBe(1);
    
    $error = NitikError::first();
    expect($error->message)->toContain('password="********"');
    expect($error->message)->toContain('key=********');
});
