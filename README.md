# Koriym.NullObject

Generate a NullObject class from the interface.
It was created for testing purposes and AOP.

## Installation

    composer require koriym/null-object 1.x-dev

## Usage

    $nullObjectClass = $interface . 'Null' // add Null prefix to interface
    $nullObject = new $nullObjectClass;
    assert($nullObject instanceof $interface);

## How it works

If the autoloader catches the interface class of `Null` Postfix, it generates the NullObject class from the interface on the fly.
