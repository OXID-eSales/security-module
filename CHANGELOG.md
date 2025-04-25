# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0-rc.1] - 2025-04-25

### Added
- Image Captcha Protection to prevent automated bot submissions.
- Audio Captcha Support for accessibility, allowing users to hear the captcha text.
- Honeypot Captcha to detect and block bots without disrupting user experience.
- Support of PHP 8.4

### Fixed
- Password validators are no longer used when password policy is disabled
- Error in shop core settings forms

## [1.0.0] - 2024-11-27
This is the stable release of v1.0.0. No changes have been made since v1.0.0-rc.1.

## [1.0.0-rc.1] - 2024-11-06

### Added
- Password strength validators for length, uppercase, lowercase, digit and special characters
- Ajax widget for password strength visual indicator
- Frontend progress bar style indication for password strength
- Button for generating strong password
- Button for show/hide password in password fields

[1.1.0-rc.1]: https://github.com/OXID-eSales/security-module/compare/v1.0.0...v1.1.0-rc.1
[1.0.0]: https://github.com/OXID-eSales/security-module/compare/v1.0.0-rc.1...v1.0.0
[1.0.0-rc.1]: https://github.com/OXID-eSales/security-module/releases/tag/v1.0.0-rc.1
