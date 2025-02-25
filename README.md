# OXID Security Module
A collection of security features for OXID eShop

[![Development](https://github.com/OXID-eSales/security-module/actions/workflows/trigger.yaml/badge.svg?branch=b-7.3.x)](https://github.com/OXID-eSales/security-module/actions/workflows/trigger.yaml)
[![Latest Version](https://img.shields.io/packagist/v/OXID-eSales/security-module?logo=composer&label=latest&include_prereleases&color=orange)](https://packagist.org/packages/oxid-esales/security-module)
[![PHP Version](https://img.shields.io/packagist/php-v/oxid-esales/security-module)](https://github.com/oxid-esales/security-module)

[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=OXID-eSales_security-module&metric=alert_status&token=0026d27eda3483728f0985d44d32714927ad2f3d)](https://sonarcloud.io/dashboard?id=OXID-eSales_security-module)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=OXID-eSales_security-module&metric=coverage&token=0026d27eda3483728f0985d44d32714927ad2f3d)](https://sonarcloud.io/dashboard?id=OXID-eSales_security-module)
[![Technical Debt](https://sonarcloud.io/api/project_badges/measure?project=OXID-eSales_security-module&metric=sqale_index&token=0026d27eda3483728f0985d44d32714927ad2f3d)](https://sonarcloud.io/dashboard?id=OXID-eSales_security-module)

## Compatibility

This module assumes you have OXID eShop Compilation version 7.2.0 installed.

## Development installation

To install from github as a source, first clone the repository.

```bash
$ git clone https://github.com/OXID-eSales/security-module ./dev-packages/security-module
```
Set the repository up in composer.json

```bash
$ composer config repositories.oxid-esales/security-module \
  --json '{"type":"path", "url":"./dev-packages/security-module", "options": {"symlink": true}}'
```

Ensure you're in the shop root directory (the file `composer.json` and the directories `source/` and `vendor/` are located there) and require the module.

```bash
$ composer require oxid-esales/security-module
```

## Password strength and Captcha protection

This module provides password strength estimation for any string input.
It can validate password length and character variety based on configurable settings.
It also includes a visual password strength indicator with a progress bar for real-time feedback via an Ajax widget.

Additionally, the module features Image Captcha protection to prevent automated bot submissions.
Users must enter the text displayed in the captcha image, with an audio captcha option available for accessibility.
A honeypot captcha is also implemented as a hidden field to detect and block bots without affecting the user experience.

### Configuration

The module configurations provide an option to Enable/Disable any of the features -
Password strength estimation, Image Captcha protection, Honeypot Captcha protection.

Configurable options for password strength estimation are:
- Enable/Disable password strength estimation
- Minimum password length
- Uppercase character requirement
- Lowercase character requirement
- Digit requirement
- Special character requirement

Configurable options for Captcha protection are:
- Enable/Disable Image Captcha protection
- Enable/Disable Honeypot Captcha protection
- Image Captcha lifetime

## Testing
### Linting, syntax check, static analysis

```bash
$ composer update
$ composer static
```

### Unit/Integration/Acceptance tests

- Install this module in a running OXID eShop
- Reset the shop's database

```bash
$ bin/oe-console oe:database:reset --db-host=db-host --db-port=db-port --db-name=db-name --db-user=db-user --db-password=db-password --force
```

- Run all the tests

```bash
$ composer tests-all
```

- Or the desired suite

```bash
$ composer tests-unit
$ composer tests-integration
$ composer tests-codeception
```
