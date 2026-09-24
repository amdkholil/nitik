# Nitik

![Nitik Dashboard Preview](art/preview.png)

Nitik is a robust error tracking package for Laravel and Filament. It aggregates unique errors into a database, provides a sleek dashboard to manage them, and delivers instant notification alerts (Email & Discord).

## Features

- **Unique Error Aggregation**: Combines similar errors into a single record with a count of occurrences.
- **Sensitive Data Scrubbing**: Automatically scrubs sensitive parameters (passwords, tokens, keys) from log messages and stack traces.
- **Smart Stack Trace**: Captures and filters stack traces for better readability, skipping internal baggage.
- **Filament Integration**: Ready-to-use Filament v4 & v5 resource page to monitor, filter, and manage errors.
- **Bulk Actions**: Mark multiple errors as resolved/unresolved or delete them in bulk with auto-closing dropdowns.
- **Notification Channels**: Instant alerts via **Email** and **Discord Webhooks** on error occurrences.
- **Infinite Loop Protection**: Prevents recursive logging loops if the database or notification delivery fails.

## Requirements

- PHP: `^8.1`
- Laravel: `^10.0 | ^11.0 | ^12.0 | ^13.0`
- Filament: `^4.0 | ^5.0`

## Installation

```bash
composer require kholil/nitik
php artisan migrate
```

## Configuration

### 1. Publish Config and Migrations

```bash
php artisan vendor:publish --tag=nitik
```

### 2. Add Log Channel

Add the `nitik` channel to your `config/logging.php`:

```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'nitik'],
        'ignore_exceptions' => false,
    ],

    'nitik' => [
        'driver' => 'nitik',
        'level' => 'debug',
    ],
    // ...
],
```

### 3. Configure Notification Channels

Add notification variables to your `.env` file:

```env
NITIK_NOTIFICATIONS_ENABLED=true
NITIK_NOTIFY_ONLY_FIRST_OCCURRENCE=true

# Email Notification
NITIK_MAIL_NOTIFICATION_ENABLED=true
NITIK_MAIL_TO=admin@example.com

# Discord Webhook Notification
NITIK_DISCORD_NOTIFICATION_ENABLED=true
NITIK_DISCORD_WEBHOOK_URL=https://discord.com/api/webhooks/...
```

### 4. Register Filament Plugin

Add the `NitikPlugin` to your Filament Panel Provider:

```php
use Kholil\Nitik\NitikPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugins([
            NitikPlugin::make(),
        ]);
}
```

## Artisan Commands

Clean up your error records periodically using these commands:

### Clear Resolved Errors
Hapus semua error yang sudah ditandai sebagai 'Resolved'.
```bash
php artisan nitik:clear-resolved
```

### Prune Old Errors
Hapus error lama berdasarkan jumlah hari (default: 30 hari).
```bash
php artisan nitik:prune --days=30
```

## Configuration Options

Edit `config/nitik.php` to customize behavior:

- `log_levels`: Array of levels (e.g., `error`, `critical`) to capture.
- `ignore_exceptions`: List of exception classes to skip (e.g., `NotFoundHttpException`).
- `navigation_group`: Label for Filament navigation grouping (default: `null`).
- `notifications`: Configure Email & Discord channels, enabled status, and recipient settings.

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for more information on what has changed recently.

## Security

Please see [SECURITY.md](SECURITY.md) for reporting security vulnerabilities.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
