# PortaOne Billing API datetime helper class

## Purpose
This package intended to simplify datetime and data fields management when work 
with Portabilling API.

PortaDateTime class exptends standatd DateTimeImmutable class to provide specific methods. 
All the datatime fields of API has MySQL format and always UTC timezone. This helper 
class allows easy manipulation between billing UTC datetime strings and desired-timezone
DateTime-like objects, unlocking the full power of DateTie class in your biling-related 
application.

It also support PSR-4 autoload to root namespace (\) and PSR-11 DI containers for 
default timezone definition storage.

## Usage
I suppose you should be familiar with PortaOne billing API. Start with 
[example](https://github.com/portabilling/datetime/blob/master/examples/UsageExample.php), 
browse [PHPdoc](https://portabilling.github.io/datetime/). Use sources to understand 
how it works, it is very simple.

## Installation
In the Composer storage. Just add proper require section:
```
"require": {
    "porta/datetime": "^0.1"
}
```
Please, review [changelog](https://github.com/portabilling/datetime/blob/master/CHANGELOG.MD) before to change used version.

## Dependencies
- php: ^7.4|^8.0|^8.1|^8.2
- psr/container: "*"


## Testing
Tested with PHPUnit 9.6 on php 7.4 to 8.2, 100% coverage, which does not mean everything will work as intended.
Current testing and compatibility status check on [Github flows](https://github.com/portabilling/datetime/actions/workflows/Compatibility.yml)

To test, install in dev mode and use: 
- `composer test` command from library root for run all standard modular tests
