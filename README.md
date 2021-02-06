# Koriym.NullObject

Generates a NullObject from an interface.
It was created for testing and AOP.


## Installation

    composer require --dev koriym/null-object 1.x-dev

## Usage

```php
interface FooInterface
{
   public function do(): void;
}

$nullClass = FooInterface::class . 'Null' // Add Null postfix to the interface.
$foo = new $nullClass;  // Instantiate a NullObject.
assert($foo instanceof FooInterface);
$foo->do(); // Nothing's going to happen.
```

## How it works

If the autoloader catches the interface class of `Null` Postfix, it generates the NullObject class from the interface on the fly.
