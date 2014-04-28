.. index::
    single: Syntax; ExpressionLanguage

The Expression Syntax
=====================

The ExpressionLanguage component uses a specific syntax which is based on the
expression syntax of Twig. In this document, you can find all supported
syntaxes.

Supported Literals
------------------

The component supports:

* **strings** - single and double quotes (e.g. ``'hello'``)
* **numbers** - e.g. ``103``
* **arrays** - using JSON-like notation (e.g. ``[1, 2]``)
* **hashes** - using JSON-like notation (e.g. ``{ foo: 'bar' }``)
* **booleans** - ``true`` and ``false``
* **null** - ``null``

.. _component-expression-objects:

Working with Objects
--------------------

When passing objects into an expression, you can use different syntaxes to
access properties and call methods on the object.

Accessing Public Properties
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Public properties on objects can be accessed by using the ``.`` syntax, similar
to JavaScript::

    class Apple
    {
        public $variety;
    }

    $apple = new Apple();
    $apple->variety = 'Honeycrisp';

    echo $language->evaluate(
        'fruit.variety',
        array(
            'fruit' => $apple,
        )
    );

This will print out ``Honeycrisp``.

Calling Methods
~~~~~~~~~~~~~~~

The ``.`` syntax can also be used to call methods on an object, similar to
JavaScript::

    class Robot
    {
        public function sayHi($times)
        {
            $greetings = array();
            for ($i = 0; $i < $times; $i++) {
                $greetings[] = 'Hi';
            }

            return implode(' ', $greetings).'!';
        }
    }

    $robot = new Robot();

    echo $language->evaluate(
        'robot.sayHi(3)',
        array(
            'robot' => $robot,
        )
    );

This will print out ``Hi Hi Hi!``.

.. _component-expression-functions:

Working with Functions
----------------------

You can also use registered functions in the expression by using the same
syntax as PHP and JavaScript. The ExpressionLanguage component comes with one
function by default: ``constant()``, which will return the value of the PHP
constant::

    define('DB_USER', 'root');

    echo $language->evaluate(
        'constant("DB_USER")'
    );

This will print out ``root``.

.. tip::

    To read how to register your own functions to use in an expression, see
    ":doc:`/components/expression_language/extending`".

.. _component-expression-arrays:

Working with Arrays
-------------------

If you pass an array into an expression, use the ``[]`` syntax to access
array keys, similar to JavaScript::

    $data = array('life' => 10, 'universe' => 10, 'everything' => 22);

    echo $language->evaluate(
        'data["life"] + data["universe"] + data["everything"]',
        array(
            'data' => $data,
        )
    );

This will print out ``42``.

Supported Operators
-------------------

The component comes with a lot of operators:

Arithmetic Operators
~~~~~~~~~~~~~~~~~~~~

* ``+`` (addition)
* ``-`` (subtraction)
* ``*`` (multiplication)
* ``/`` (division)
* ``%`` (modulus)
* ``**`` (pow)

For example::

    echo $language->evaluate(
        'life + universe + everything',
        array(
            'life' => 10,
            'universe' => 10,
            'everything' => 22,
        )
    );

This will print out ``42``.

Bitwise Operators
~~~~~~~~~~~~~~~~~

* ``&`` (and)
* ``|`` (or)
* ``^`` (xor)

Comparison Operators
~~~~~~~~~~~~~~~~~~~~

* ``==`` (equal)
* ``===`` (identical)
* ``!=`` (not equal)
* ``!==`` (not identical)
* ``<`` (less than)
* ``>`` (greater than)
* ``<=`` (less than or equal to)
* ``>=`` (greater than or equal to)
* ``matches`` (regex match)

.. tip::

    To test if a string does *not* match a regex, use the logical ``not``
    operator in combination with the ``matches`` operator::

        $language->evaluate('not ("foo" matches "/bar/")'); // returns true

    You must use parenthesis because the unary operator ``not`` has precedence
    over the binary operator ``matches``.

Examples::

    $ret1 = $language->evaluate(
        'life == everything',
        array(
            'life' => 10,
            'universe' => 10,
            'everything' => 22,
        )
    );

    $ret2 = $language->evaluate(
        'life > everything',
        array(
            'life' => 10,
            'universe' => 10,
            'everything' => 22,
        )
    );

Both variables would be set to ``false``.

Logical Operators
~~~~~~~~~~~~~~~~~

* ``not`` or ``!``
* ``and`` or ``&&``
* ``or`` or ``||``

For example::

    $ret = $language->evaluate(
        'life < universe or life < everything',
        array(
            'life' => 10,
            'universe' => 10,
            'everything' => 22,
        )
    );

This ``$ret`` variable will be set to ``true``.

String Operators
~~~~~~~~~~~~~~~~

* ``~`` (concatenation)

For example::

    echo $language->evaluate(
        'firstName~" "~lastName',
        array(
            'firstName' => 'Arthur',
            'lastName' => 'Dent',
        )
    );

This would print out ``Arthur Dent``.

Array Operators
~~~~~~~~~~~~~~~

* ``in`` (contain)
* ``not in`` (does not contain)

For example::

    class User
    {
        public $group;
    }

    $user = new User();
    $user->group = 'human_resources';

    $inGroup = $language->evaluate(
        'user.group in ["human_resources", "marketing"]',
        array(
            'user' => $user
        )
    );

The ``$inGroup`` would evaluate to ``true``.

Numeric Operators
~~~~~~~~~~~~~~~~~

* ``..`` (range)

For example::

    class User
    {
        public $age;
    }

    $user = new User();
    $user->age = 34;

    $language->evaluate(
        'user.age in 18..45',
        array(
            'user' => $user,
        )
    );

This will evaluate to ``true``, because ``user.age`` is in the range from
``18`` to ``45``.

Ternary Operators
~~~~~~~~~~~~~~~~~

* ``foo ? 'yes' : 'no'``
* ``foo ?: 'no'`` (equal to ``foo ? foo : 'no'``)
* ``foo ? 'yes'`` (equal to ``foo ? 'yes' : ''``)
