<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Kholil\Nitik\Mail\NitikErrorMail;
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

it('can update is_resolved status in bulk', function () {
    $now = now();
    $err1 = NitikError::create(['hash' => 'h1', 'message' => 'm1', 'level' => 'ERROR', 'first_seen_at' => $now, 'last_seen_at' => $now, 'is_resolved' => false]);
    $err2 = NitikError::create(['hash' => 'h2', 'message' => 'm2', 'level' => 'ERROR', 'first_seen_at' => $now, 'last_seen_at' => $now, 'is_resolved' => false]);

    NitikError::whereIn('id', [$err1->id, $err2->id])->update(['is_resolved' => true]);
    expect(NitikError::where('is_resolved', true)->count())->toBe(2);

    NitikError::whereIn('id', [$err1->id, $err2->id])->update(['is_resolved' => false]);
    expect(NitikError::where('is_resolved', false)->count())->toBe(2);
});

it('sends email and discord notifications when enabled', function () {
    Mail::fake();
    Http::fake();

    config([
        'nitik.notifications.enabled' => true,
        'nitik.notifications.channels.mail.enabled' => true,
        'nitik.notifications.channels.mail.to' => 'test@example.com',
        'nitik.notifications.channels.discord.enabled' => true,
        'nitik.notifications.channels.discord.webhook_url' => 'https://discord.com/api/webhooks/123/abc',
    ]);

    Log::channel('nitik')->error('Notified Error');

    Mail::assertSent(NitikErrorMail::class, function ($mail) {
        return $mail->hasTo('test@example.com');
    });

    Http::assertSent(function ($request) {
        return $request->url() === 'https://discord.com/api/webhooks/123/abc';
    });
});
