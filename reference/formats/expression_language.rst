The Expression Syntax
=====================

The ExpressionLanguage component uses a specific syntax which is based on the
expression syntax of Twig. In this document, you can find all supported
syntaxes.

Supported Literals
------------------

The component supports:

* **strings** - single and double quotes (e.g. ``'hello'``)
* **numbers** - integers (e.g. ``103``), decimals (e.g. ``9.95``), decimals
  without leading zeros (e.g. ``.99``, equivalent to ``0.99``); all numbers
  support optional underscores as separators to improve readability (e.g.
  ``1_000_000``, ``3.14159_26535``)
* **arrays** - using JSON-like notation (e.g. ``[1, 2]``)
* **hashes** - using JSON-like notation (e.g. ``{ foo: 'bar' }``)
* **booleans** - ``true`` and ``false``
* **null** - ``null``
* **exponential** - also known as scientific (e.g. ``1.99E+3`` or ``1e-2``)

.. caution::

    A backslash (``\``) must be escaped by 3 backslashes (``\\\\``) in a string
    and 7 backslashes (``\\\\\\\\``) in a regex::

        echo $expressionLanguage->evaluate('"\\\\"'); // prints \
        $expressionLanguage->evaluate('"a\\\\b" matches "/^a\\\\\\\\b$/"'); // returns true

    Control characters (e.g. ``\n``) in expressions are replaced with
    whitespace. To avoid this, escape the sequence with a single backslash
    (e.g.  ``\\n``).

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
        public string $variety;
    }

    $apple = new Apple();
    $apple->variety = 'Honeycrisp';

    var_dump($expressionLanguage->evaluate(
        'fruit.variety',
        [
            'fruit' => $apple,
        ]
    ));

This will print out ``Honeycrisp``.

Calling Methods
~~~~~~~~~~~~~~~

The ``.`` syntax can also be used to call methods on an object, similar to
JavaScript::

    class Robot
    {
        public function sayHi(int $times): string
        {
            $greetings = [];
            for ($i = 0; $i < $times; $i++) {
                $greetings[] = 'Hi';
            }

            return implode(' ', $greetings).'!';
        }
    }

    $robot = new Robot();

    var_dump($expressionLanguage->evaluate(
        'robot.sayHi(3)',
        [
            'robot' => $robot,
        ]
    ));

This will print out ``Hi Hi Hi!``.

.. _component-expression-null-safe-operator:

Null-safe Operator
..................

Use the ``?.`` syntax to access properties and methods of objects that can be
``null`` (this is equivalent to the ``$object?->propertyOrMethod`` PHP null-safe
operator)::

    // these will throw an exception when `fruit` is `null`
    $expressionLanguage->evaluate('fruit.color', ['fruit' => '...'])
    $expressionLanguage->evaluate('fruit.getStock()', ['fruit' => '...'])

    // these will return `null` if `fruit` is `null`
    $expressionLanguage->evaluate('fruit?.color', ['fruit' => '...'])
    $expressionLanguage->evaluate('fruit?.getStock()', ['fruit' => '...'])

.. _component-expression-null-coalescing-operator:

Null-Coalescing Operator
........................

It returns the left-hand side if it exists and it's not ``null``; otherwise it
returns the right-hand side. Expressions can chain multiple coalescing operators:

* ``foo ?? 'no'``
* ``foo.baz ?? 'no'``
* ``foo[3] ?? 'no'``
* ``foo.baz ?? foo['baz'] ?? 'no'``

.. note::

    The main difference with the `null-coalescing operator in PHP`_ is that
    ExpressionLanguage will throw an exception when trying to access a
    non-existent variable.

.. _component-expression-functions:

Working with Functions
----------------------

You can also use registered functions in the expression by using the same
syntax as PHP and JavaScript. The ExpressionLanguage component comes with the
following functions by default:

* ``constant()``
* ``enum()``
* ``min()``
* ``max()``

``constant()`` function
~~~~~~~~~~~~~~~~~~~~~~~

This function will return the value of a PHP constant::

    define('DB_USER', 'root');

    var_dump($expressionLanguage->evaluate(
        'constant("DB_USER")'
    ));

This will print out ``root``.

This also works with class constants::

    namespace App\SomeNamespace;

    class Foo
    {
        public const API_ENDPOINT = '/api';
    }

    var_dump($expressionLanguage->evaluate(
        'constant("App\\\SomeNamespace\\\Foo::API_ENDPOINT")'
    ));

This will print out ``/api``.

``enum()`` function
~~~~~~~~~~~~~~~~~~~

This function will return the case of an enumeration::

    namespace App\SomeNamespace;

    enum Foo
    {
        case Bar;
    }

    var_dump(App\Enum\Foo::Bar === $expressionLanguage->evaluate(
        'enum("App\\\SomeNamespace\\\Foo::Bar")'
    ));

This will print out ``true``.

``min()`` function
~~~~~~~~~~~~~~~~~~

This function will return the lowest value of the given parameters. You can pass
different types of parameters (e.g. dates, strings, numeric values) and even mix
them (e.g. pass numeric values and strings). Internally it uses the :phpfunction:`min`
PHP function to find the lowest value::

    var_dump($expressionLanguage->evaluate(
        'min(1, 2, 3)'
    ));

This will print out ``1``.

``max()`` function
~~~~~~~~~~~~~~~~~~

This function will return the highest value of the given parameters. You can pass
different types of parameters (e.g. dates, strings, numeric values) and even mix
them (e.g. pass numeric values and strings). Internally it uses the :phpfunction:`max`
PHP function to find the highest value::

    var_dump($expressionLanguage->evaluate(
        'max(1, 2, 3)'
    ));

This will print out ``3``.

.. versionadded:: 7.1

    The ``min()`` and ``max()`` functions were introduced in Symfony 7.1.

.. tip::

    To read how to register your own functions to use in an expression, see
    ":ref:`expression-language-extending`".

.. _component-expression-arrays:

Working with Arrays
-------------------

If you pass an array into an expression, use the ``[]`` syntax to access
array keys, similar to JavaScript::

    $data = ['life' => 10, 'universe' => 10, 'everything' => 22];

    var_dump($expressionLanguage->evaluate(
        'data["life"] + data["universe"] + data["everything"]',
        [
            'data' => $data,
        ]
    ));

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

    var_dump($expressionLanguage->evaluate(
        'life + universe + everything',
        [
            'life' => 10,
            'universe' => 10,
            'everything' => 22,
        ]
    ));

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
* ``contains``
* ``starts with``
* ``ends with``

.. tip::

    To test if a string does *not* match a regex, use the logical ``not``
    operator in combination with the ``matches`` operator::

        $expressionLanguage->evaluate('not ("foo" matches "/bar/")'); // returns true

    You must use parentheses because the unary operator ``not`` has precedence
    over the binary operator ``matches``.

Examples::

    $ret1 = $expressionLanguage->evaluate(
        'life == everything',
        [
            'life' => 10,
            'everything' => 22,
        ]
    );

    $ret2 = $expressionLanguage->evaluate(
        'life > everything',
        [
            'life' => 10,
            'everything' => 22,
        ]
    );

Both variables would be set to ``false``.

Logical Operators
~~~~~~~~~~~~~~~~~

* ``not`` or ``!``
* ``and`` or ``&&``
* ``or`` or ``||``

For example::

    $ret = $expressionLanguage->evaluate(
        'life < universe or life < everything',
        [
            'life' => 10,
            'universe' => 10,
            'everything' => 22,
        ]
    );

This ``$ret`` variable will be set to ``true``.

String Operators
~~~~~~~~~~~~~~~~

* ``~`` (concatenation)

For example::

    var_dump($expressionLanguage->evaluate(
        'firstName~" "~lastName',
        [
            'firstName' => 'Arthur',
            'lastName' => 'Dent',
        ]
    ));

This would print out ``Arthur Dent``.

Array Operators
~~~~~~~~~~~~~~~

* ``in`` (contain)
* ``not in`` (does not contain)

These operators are using strict comparison. For example::

    class User
    {
        public string $group;
    }

    $user = new User();
    $user->group = 'human_resources';

    $inGroup = $expressionLanguage->evaluate(
        'user.group in ["human_resources", "marketing"]',
        [
            'user' => $user,
        ]
    );

The ``$inGroup`` would evaluate to ``true``.

.. note::

    The ``in`` and ``not in`` operators are using strict comparison.

Numeric Operators
~~~~~~~~~~~~~~~~~

* ``..`` (range)

For example::

    class User
    {
        public int $age;
    }

    $user = new User();
    $user->age = 34;

    $expressionLanguage->evaluate(
        'user.age in 18..45',
        [
            'user' => $user,
        ]
    );

This will evaluate to ``true``, because ``user.age`` is in the range from
``18`` to ``45``.

Ternary Operators
~~~~~~~~~~~~~~~~~

* ``foo ? 'yes' : 'no'``
* ``foo ?: 'no'`` (equal to ``foo ? foo : 'no'``)
* ``foo ? 'yes'`` (equal to ``foo ? 'yes' : ''``)

Other Operators
~~~~~~~~~~~~~~~

* ``?.`` (:ref:`null-safe operator <component-expression-null-safe-operator>`)
* ``??`` (:ref:`null-coalescing operator <component-expression-null-coalescing-operator>`)

Operators Precedence
~~~~~~~~~~~~~~~~~~~~

Operator precedence determines the order in which operations are processed in an
expression. For example, the result of the expression ``1 + 2 * 4`` is ``9``
and not ``12`` because the multiplication operator (``*``) takes precedence over
the addition operator (``+``).

To avoid ambiguities (or to alter the default order of operations) add
parentheses in your expressions (e.g. ``(1 + 2) * 4`` or ``1 + (2 * 4)``.

The following table summarizes the operators and their associativity from the
**highest to the lowest precedence**:

=======================================================  =============
Operators                                                associativity
=======================================================  =============
``-``, ``+`` (unary operators that add the number sign)  none
``**``                                                   right
``*``, ``/``, ``%``                                      left
``not``, ``!``                                           none
``~``                                                    left
``+``, ``-``                                             left
``..``                                                   left
``==``, ``===``, ``!=``, ``!==``,                        left
``<``, ``>``, ``>=``, ``<=``,
``not in``, ``in``, ``contains``,
``starts with``, ``ends with``, ``matches``
``&``                                                    left
``^``                                                    left
``|``                                                    left
``and``, ``&&``                                          left
``or``, ``||``                                           left
=======================================================  =============

Built-in Objects and Variables
------------------------------

When using this component inside a Symfony application, certain objects and
variables are automatically injected by Symfony so you can use them in your
expressions (e.g. the request, the current user, etc.):

* :doc:`Variables available in security expressions </security/expressions>`;
* :doc:`Variables available in service container expressions </service_container/expression_language>`;
* :ref:`Variables available in routing expressions <routing-matching-expressions>`.

.. _`null-coalescing operator in PHP`: https://www.php.net/manual/en/language.operators.comparison.php#language.operators.comparison.coalesce
