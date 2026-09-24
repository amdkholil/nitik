# Changelog

All notable changes to `kholil/nitik` will be documented in this file.

## [1.2.0] - 2026-09-24

### Added
- **Notification Channels**: Email and Discord Webhook alerts for captured application errors (`NitikNotifier`).
- **Bulk Actions UI**: Bulk mark selected errors as resolved or unresolved with automatic dropdown closure (`deselectRecordsAfterCompletion`).
- **Security Policy**: Added `SECURITY.md` for vulnerability reporting guidelines.

### Updated
- Extended Laravel compatibility up to `laravel/framework: ^13.0`.
- Updated Filament resource bulk actions UX.

---

## [1.1.4] - 2026-09-24

### Fixed
- Updated Filament table action namespaces for compatibility.

## [1.1.3] - 2026-09-24

### Added
- Sanitization and scrubbing of sensitive parameters from logs and stack traces (`NitikNormalizer`).
