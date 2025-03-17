# OXID Security Module
A collection of security features for OXID eShop

[![Development](https://github.com/OXID-eSales/security-module/actions/workflows/trigger.yaml/badge.svg?branch=b-7.3.x)](https://github.com/OXID-eSales/security-module/actions/workflows/trigger.yaml)
[![Latest Version](https://img.shields.io/packagist/v/OXID-eSales/security-module?logo=composer&label=latest&include_prereleases&color=orange)](https://packagist.org/packages/oxid-esales/security-module)
[![PHP Version](https://img.shields.io/packagist/php-v/oxid-esales/security-module)](https://github.com/oxid-esales/security-module)

[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=OXID-eSales_security-module&metric=alert_status&token=0026d27eda3483728f0985d44d32714927ad2f3d)](https://sonarcloud.io/dashboard?id=OXID-eSales_security-module)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=OXID-eSales_security-module&metric=coverage&token=0026d27eda3483728f0985d44d32714927ad2f3d)](https://sonarcloud.io/dashboard?id=OXID-eSales_security-module)
[![Technical Debt](https://sonarcloud.io/api/project_badges/measure?project=OXID-eSales_security-module&metric=sqale_index&token=0026d27eda3483728f0985d44d32714927ad2f3d)](https://sonarcloud.io/dashboard?id=OXID-eSales_security-module)

## Compatibility

This module assumes you have OXID eShop Compilation version 7.3.0 installed.

### Branches
* `b-7.3.x` - compatible with OXID eShop b-7.3.x branch
* 1.0.0.x versions (or b-7.2.x branch) - compatible with OXID eShop compilation 7.2.x. b-7.2.x shop compilation branches

# Development installation

To be able running the tests and other preconfigured quality tools, please install the module as a [root package](https://getcomposer.org/doc/04-schema.md#root-package).

The next section shows how to install the module as a root package by using the OXID eShop SDK.

In case of different environment usage, please adjust by your own needs.

# Development installation on OXID eShop SDK

The installation instructions below are shown for the current [SDK](https://github.com/OXID-eSales/docker-eshop-sdk)
for shop 7.3. Make sure your system meets the requirements of the SDK.

0. Ensure all docker containers are down to avoid port conflicts

1. Clone the SDK for the new project
```shell
echo MyProject && git clone https://github.com/OXID-eSales/docker-eshop-sdk.git $_ && cd $_
```

2. Clone the repository to the source directory
```shell
git clone --recurse-submodules https://github.com/OXID-eSales/security-module.git --branch=b-7.3.x ./source
```

3. Run the recipe to setup the development environment
```shell
./source/recipes/setup-development.sh
```

You should be able to access the shop with http://localhost.local and the admin panel with http://localhost.local/admin
(credentials: noreply@oxid-esales.com / admin)

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

### Running the tests and quality tools

Check the "scripts" section in the `composer.json` file for the available commands. Those commands can be executed
by connecting to the php container and running the command from there, example:

```shell
make php
composer tests-coverage
```

Commands can be also triggered directly on the container with docker compose, example:

```shell
docker compose exec -T php composer tests-coverage
```

## Testing
### Linting, syntax check, static analysis

Check the "scripts" section in the `composer.json` file for the available commands. Those commands can be executed
by connecting to the php container and running the command from there, example:

```shell
make php
composer update
composer static
```

### Unit/Integration/Acceptance tests

- Run all the tests

```shell
composer tests-all
```

- Or the desired suite

```shell
composer tests-unit
composer tests-integration
composer tests-codeception
```
