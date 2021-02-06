# Koriym.NullObject

Generates a NullObject from an interface.
It was created for testing and AOP.


## Installation

    composer require koriym/null-object 1.x-dev

## Usage

```php
interface FooInterface{}

$nullClass = FooInterface::class . 'Null' // add Null postfix to the interface
assert(new $nullClass instanceof FooInterface); // "new" instantiate a NullObject
```

## How it works

If the autoloader catches the interface class of `Null` Postfix, it generates the NullObject class from the interface on the fly.
