# security-module
A collection of security features for OXID eShop

[![Development](https://github.com/OXID-eSales/security-module/actions/workflows/trigger.yaml/badge.svg?branch=b-7.2.x)](https://github.com/OXID-eSales/security-module/actions/workflows/trigger.yaml)
[![Latest Version](https://img.shields.io/packagist/v/OXID-eSales/security-module?logo=composer&label=latest&include_prereleases&color=orange)](https://packagist.org/packages/oxid-esales/security-module)
[![PHP Version](https://img.shields.io/packagist/php-v/oxid-esales/security-module)](https://github.com/oxid-esales/security-module)

[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=OXID-eSales_security-module&metric=alert_status&token=0026d27eda3483728f0985d44d32714927ad2f3d)](https://sonarcloud.io/dashboard?id=OXID-eSales_security-module)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=OXID-eSales_security-module&metric=coverage&token=0026d27eda3483728f0985d44d32714927ad2f3d)](https://sonarcloud.io/dashboard?id=OXID-eSales_security-module)
[![Technical Debt](https://sonarcloud.io/api/project_badges/measure?project=OXID-eSales_security-module&metric=sqale_index&token=0026d27eda3483728f0985d44d32714927ad2f3d)](https://sonarcloud.io/dashboard?id=OXID-eSales_security-module)

## Compatibility

This module assumes you have OXID eShop CE version 7.2.0 or higher installed.

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

## Password strength

The module provides password strength estimation for any string input.
It can validate password length and character variety of passwords according
to configuration and be used for visal indication via Ajax widget. Provided in
the template is a progress bar style indicator of password strength.

### Configuration

Configurable options for password strength estimation are:
- Minimum password length
- Uppercase character requirement
- Lowercase character requirement
- Digit requirement
- Special character requirement

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
