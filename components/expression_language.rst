.. index::
    single: Expressions
    Single: Components; Expression Language

The ExpressionLanguage Component
================================

    The ExpressionLanguage component provides an engine that can compile and
    evaluate expressions. An expression is a one-liner that returns a value
    (mostly, but not limited to, Booleans).

Installation
------------

.. code-block:: terminal

    $ composer require symfony/expression-language

.. include:: /components/require_autoload.rst.inc

How can the Expression Engine Help Me?
--------------------------------------

The purpose of the component is to allow users to use expressions inside
configuration for more complex logic. For some examples, the Symfony Framework
uses expressions in security, for validation rules and in route matching.

Besides using the component in the framework itself, the ExpressionLanguage
component is a perfect candidate for the foundation of a *business rule engine*.
The idea is to let the webmaster of a website configure things in a dynamic
way without using PHP and without introducing security problems:

.. _component-expression-language-examples:

.. code-block:: text

    # Get the special price if
    user.getGroup() in ['good_customers', 'collaborator']

    # Promote article to the homepage when
    article.commentCount > 100 and article.category not in ["misc"]

    # Send an alert when
    product.stock < 15

Expressions can be seen as a very restricted PHP sandbox and are immune to
external injections as you must explicitly declare which variables are available
in an expression.

Usage
-----

The ExpressionLanguage component can compile and evaluate expressions.
Expressions are one-liners that often return a Boolean, which can be used
by the code executing the expression in an ``if`` statement. A simple example
of an expression is ``1 + 2``. You can also use more complicated expressions,
such as ``someArray[3].someMethod('bar')``.

The component provides 2 ways to work with expressions:

* **evaluation**: the expression is evaluated without being compiled to PHP;
* **compile**: the expression is compiled to PHP, so it can be cached and
  evaluated.

The main class of the component is
:class:`Symfony\\Component\\ExpressionLanguage\\ExpressionLanguage`::

    use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

    $expressionLanguage = new ExpressionLanguage();

    var_dump($expressionLanguage->evaluate('1 + 2')); // displays 3

    var_dump($expressionLanguage->compile('1 + 2')); // displays (1 + 2)

.. _expression-language-syntax:

.. index::
    single: Syntax; ExpressionLanguage

Expression Syntax
-----------------

The ExpressionLanguage component uses a specific syntax which is based on the
expression syntax of Twig. In this document, you can find all supported
syntaxes.

Supported Literals
~~~~~~~~~~~~~~~~~~

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

.. versionadded:: 6.1

    Support for decimals without leading zeros and underscore separators were
    introduced in Symfony 6.1.

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
~~~~~~~~~~~~~~~~~~~~

When passing objects into an expression, you can use different syntaxes to
access properties and call methods on the object.

Accessing Public Properties
...........................

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
...............

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

.. versionadded:: 6.1

    The null safe operator was introduced in Symfony 6.1.

.. _component-expression-functions:

Working with Functions
~~~~~~~~~~~~~~~~~~~~~~

You can also use registered functions in the expression by using the same
syntax as PHP and JavaScript. The ExpressionLanguage component comes with the
following functions by default:

* ``constant()``
* ``enum()``

``constant()`` function
.......................

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
...................

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

.. versionadded:: 6.3

    The ``enum()`` function was introduced in Symfony 6.3.

.. tip::

    To read how to register your own functions to use in an expression, see
    ":doc:`/components/expression_language/extending`".

.. _component-expression-arrays:

Working with Arrays
~~~~~~~~~~~~~~~~~~~

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
~~~~~~~~~~~~~~~~~~~

The component comes with a lot of operators:

Arithmetic Operators
....................

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
.................

* ``&`` (and)
* ``|`` (or)
* ``^`` (xor)

Comparison Operators
....................

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

.. versionadded:: 6.1

    The ``contains``, ``starts with`` and ``ends with`` operators were introduced
    in Symfony 6.1.

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
.................

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
................

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
...............

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
.................

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
.................

* ``foo ? 'yes' : 'no'``
* ``foo ?: 'no'`` (equal to ``foo ? foo : 'no'``)
* ``foo ? 'yes'`` (equal to ``foo ? 'yes' : ''``)

Null Coalescing Operator
........................

This is the same as the PHP `null-coalescing operator`_, which combines
the ternary operator and ``isset()``. It returns the left hand-side if it exists
and it's not ``null``; otherwise it returns the right hand-side. Note that you
can chain multiple coalescing operators.

* ``foo ?? 'no'``
* ``foo.baz ?? 'no'``
* ``foo[3] ?? 'no'``
* ``foo.baz ?? foo['baz'] ?? 'no'``

.. versionadded:: 6.2

    The null-coalescing operator was introduced in Symfony 6.2.

Passing in Variables
--------------------

You can also pass variables into the expression, which can be of any valid
PHP type (including objects)::

    use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

    $expressionLanguage = new ExpressionLanguage();

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
    )); // displays "Honeycrisp"

When using this component inside a Symfony application, certain objects and
variables are automatically injected by Symfony so you can use them in your
expressions (e.g. the request, the current user, etc.):

* :doc:`Variables available in security expressions </security/expressions>`;
* :doc:`Variables available in service container expressions </service_container/expression_language>`;
* :ref:`Variables available in routing expressions <routing-matching-expressions>`.

.. caution::

    When using variables in expressions, avoid passing untrusted data into the
    array of variables. If you can't avoid that, sanitize non-alphanumeric
    characters in untrusted data to prevent malicious users from injecting
    control characters and altering the expression.

.. index::
    single: Caching; ExpressionLanguage

Caching
-------

The ExpressionLanguage component provides a
:method:`Symfony\\Component\\ExpressionLanguage\\ExpressionLanguage::compile`
method to be able to cache the expressions in plain PHP. But internally, the
component also caches the parsed expressions, so duplicated expressions can be
compiled/evaluated quicker.

The Workflow
~~~~~~~~~~~~

Both :method:`Symfony\\Component\\ExpressionLanguage\\ExpressionLanguage::evaluate`
and ``compile()`` need to do some things before each can provide the return
values. For ``evaluate()``, this overhead is even bigger.

Both methods need to tokenize and parse the expression. This is done by the
:method:`Symfony\\Component\\ExpressionLanguage\\ExpressionLanguage::parse`
method. It  returns a :class:`Symfony\\Component\\ExpressionLanguage\\ParsedExpression`.
Now, the ``compile()`` method just returns the string conversion of this object.
The ``evaluate()`` method needs to loop through the "nodes" (pieces of an
expression saved in the ``ParsedExpression``) and evaluate them on the fly.

To save time, the ``ExpressionLanguage`` caches the ``ParsedExpression`` so
it can skip the tokenization and parsing steps with duplicate expressions. The
caching is done by a PSR-6 `CacheItemPoolInterface`_ instance (by default, it
uses an :class:`Symfony\\Component\\Cache\\Adapter\\ArrayAdapter`). You can
customize this by creating a custom cache pool or using one of the available
ones and injecting this using the constructor::

    use Symfony\Component\Cache\Adapter\RedisAdapter;
    use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

    $cache = new RedisAdapter(...);
    $expressionLanguage = new ExpressionLanguage($cache);

.. seealso::

    See the :doc:`/components/cache` documentation for more information about
    available cache adapters.

Using Parsed and Serialized Expressions
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Both ``evaluate()`` and ``compile()`` can handle ``ParsedExpression`` and
``SerializedParsedExpression``::

    // ...

    // the parse() method returns a ParsedExpression
    $expression = $expressionLanguage->parse('1 + 4', []);

    var_dump($expressionLanguage->evaluate($expression)); // prints 5

.. code-block:: php

    use Symfony\Component\ExpressionLanguage\SerializedParsedExpression;
    // ...

    $expression = new SerializedParsedExpression(
        '1 + 4',
        serialize($expressionLanguage->parse('1 + 4', [])->getNodes())
    );

    var_dump($expressionLanguage->evaluate($expression)); // prints 5

.. index::
    single: AST; ExpressionLanguage
    single: AST; Abstract Syntax Tree

AST Dumping and Editing
-----------------------

It's difficult to manipulate or inspect the expressions created with the ExpressionLanguage
component, because the expressions are plain strings. A better approach is to
turn those expressions into an AST. In computer science, `AST`_ (*Abstract
Syntax Tree*) is *"a tree representation of the structure of source code written
in a programming language"*. In Symfony, a ExpressionLanguage AST is a set of
nodes that contain PHP classes representing the given expression.

Dumping the AST
~~~~~~~~~~~~~~~

Call the :method:`Symfony\\Component\\ExpressionLanguage\\ExpressionLanguage::getNodes`
method after parsing any expression to get its AST::

    use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

    $ast = (new ExpressionLanguage())
        ->parse('1 + 2', [])
        ->getNodes()
    ;

    // dump the AST nodes for inspection
    var_dump($ast);

    // dump the AST nodes as a string representation
    $astAsString = $ast->dump();

Manipulating the AST
~~~~~~~~~~~~~~~~~~~~

The nodes of the AST can also be dumped into a PHP array of nodes to allow
manipulating them. Call the :method:`Symfony\\Component\\ExpressionLanguage\\ExpressionLanguage::toArray`
method to turn the AST into an array::

    // ...

    $astAsArray = (new ExpressionLanguage())
        ->parse('1 + 2', [])
        ->getNodes()
        ->toArray()
    ;

.. _expression-language-extending:

.. index::
    single: Extending; ExpressionLanguage

Extending the ExpressionLanguage
--------------------------------

The ExpressionLanguage can be extended by adding custom functions. For
instance, in the Symfony Framework, the security has custom functions to check
the user's role.

.. note::

    If you want to learn how to use functions in an expression, read
    ":ref:`component-expression-functions`".

Registering Functions
~~~~~~~~~~~~~~~~~~~~~

Functions are registered on each specific ``ExpressionLanguage`` instance.
That means the functions can be used in any expression executed by that
instance.

To register a function, use
:method:`Symfony\\Component\\ExpressionLanguage\\ExpressionLanguage::register`.
This method has 3 arguments:

* **name** - The name of the function in an expression;
* **compiler** - A function executed when compiling an expression using the
  function;
* **evaluator** - A function executed when the expression is evaluated.

Example::

    use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

    $expressionLanguage = new ExpressionLanguage();
    $expressionLanguage->register('lowercase', function ($str) {
        return sprintf('(is_string(%1$s) ? strtolower(%1$s) : %1$s)', $str);
    }, function ($arguments, $str) {
        if (!is_string($str)) {
            return $str;
        }

        return strtolower($str);
    });

    var_dump($expressionLanguage->evaluate('lowercase("HELLO")'));
    // this will print: hello

In addition to the custom function arguments, the **evaluator** is passed an
``arguments`` variable as its first argument, which is equal to the second
argument of ``evaluate()`` (e.g. the "values" when evaluating an expression).

.. _components-expression-language-provider:

Using Expression Providers
~~~~~~~~~~~~~~~~~~~~~~~~~~

When you use the ``ExpressionLanguage`` class in your library, you often want
to add custom functions. To do so, you can create a new expression provider by
creating a class that implements
:class:`Symfony\\Component\\ExpressionLanguage\\ExpressionFunctionProviderInterface`.

This interface requires one method:
:method:`Symfony\\Component\\ExpressionLanguage\\ExpressionFunctionProviderInterface::getFunctions`,
which returns an array of expression functions (instances of
:class:`Symfony\\Component\\ExpressionLanguage\\ExpressionFunction`) to
register::

    use Symfony\Component\ExpressionLanguage\ExpressionFunction;
    use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

    class StringExpressionLanguageProvider implements ExpressionFunctionProviderInterface
    {
        public function getFunctions()
        {
            return [
                new ExpressionFunction('lowercase', function ($str) {
                    return sprintf('(is_string(%1$s) ? strtolower(%1$s) : %1$s)', $str);
                }, function ($arguments, $str) {
                    if (!is_string($str)) {
                        return $str;
                    }

                    return strtolower($str);
                }),
            ];
        }
    }

.. tip::

    To create an expression function from a PHP function with the
    :method:`Symfony\\Component\\ExpressionLanguage\\ExpressionFunction::fromPhp` static method::

        ExpressionFunction::fromPhp('strtoupper');

    Namespaced functions are supported, but they require a second argument to
    define the name of the expression::

        ExpressionFunction::fromPhp('My\strtoupper', 'my_strtoupper');

You can register providers using
:method:`Symfony\\Component\\ExpressionLanguage\\ExpressionLanguage::registerProvider`
or by using the second argument of the constructor::

    use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

    // using the constructor
    $expressionLanguage = new ExpressionLanguage(null, [
        new StringExpressionLanguageProvider(),
        // ...
    ]);

    // using registerProvider()
    $expressionLanguage->registerProvider(new StringExpressionLanguageProvider());

.. tip::

    It is recommended to create your own ``ExpressionLanguage`` class in your
    library. Now you can add the extension by overriding the constructor::

        use Psr\Cache\CacheItemPoolInterface;
        use Symfony\Component\ExpressionLanguage\ExpressionLanguage as BaseExpressionLanguage;

        class ExpressionLanguage extends BaseExpressionLanguage
        {
            public function __construct(CacheItemPoolInterface $cache = null, array $providers = [])
            {
                // prepends the default provider to let users override it
                array_unshift($providers, new StringExpressionLanguageProvider());

                parent::__construct($cache, $providers);
            }
        }

.. _`AST`: https://en.wikipedia.org/wiki/Abstract_syntax_tree
.. _`CacheItemPoolInterface`: https://github.com/php-fig/cache/blob/master/src/CacheItemPoolInterface.php
