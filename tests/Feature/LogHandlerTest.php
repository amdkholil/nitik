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
