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
* **exponential** - also known as scientific (e.g. ``1.99E+3`` or ``1e-2``)

.. caution::

    A backslash (``\``) must be escaped by 4 backslashes (``\\\\``) in a string
    and 8 backslashes (``\\\\\\\\``) in a regex::

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
        public $variety;
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
        public function sayHi($times)
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

.. _component-expression-functions:

Working with Functions
----------------------

You can also use registered functions in the expression by using the same
syntax as PHP and JavaScript. The ExpressionLanguage component comes with one
function by default: ``constant()``, which will return the value of the PHP
constant::

    define('DB_USER', 'root');

    var_dump($expressionLanguage->evaluate(
        'constant("DB_USER")'
    ));

This will print out ``root``.

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

For example::

    class User
    {
        public $group;
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

Built-in Objects and Variables
------------------------------

When using this component inside a Symfony application, certain objects and
variables are automatically injected by Symfony so you can use them in your
expressions (e.g. the request, the current user, etc.):

* :doc:`Variables available in security expressions </security/expressions>`;
* :doc:`Variables available in service container expressions </service_container/expression_language>`;
* :ref:`Variables available in routing expressions <routing-matching-expressions>`.
