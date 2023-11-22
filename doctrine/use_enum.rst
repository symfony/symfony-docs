Use Enums
=========

PHP 8.1 added native Enumerations, which can be used to define a custom type
and its possible values. Those can be used in association with Doctrine in 
order to define a limited set of values availables for an entity property.

First step is to create an enum::

    // src/Enum/Suit.php
    namespace App\Enum;

    enum Suit: string {
    case Hearts = 'H';
    case Diamonds = 'D'; 
    case Clubs = 'C';
    case Spades = 'S';
}

.. note::

    Only backed enums can be used with properties as Doctrine use the scalar
    equivalent of each value for storing.

When the enum is created, you can use the ``enumType`` parameter of 
``#[ORM\Column]`` attribute or use it directly for a more typed property::

    // src/Entity/Card.php
    namespace App\Enum;

    #[Column(type: Types::TEXT, enumType: Suit::class)]
    public string $suit;

    // or for a more typed property
    #[Column(type: Types::TEXT)]
    public Suit $suit;

.. caution::

    If you use the Symfony Maker bundle to create or update your entities,
    there is no EnumType available. It still can be used to generate property
    with getter and setter but you will need to update declaration according
    to your needs.