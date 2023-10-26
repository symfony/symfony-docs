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

.. _how-can-the-expression-engine-help-me:

How can the Expression Language Help Me?
----------------------------------------

The purpose of the component is to allow users to use expressions inside
configuration for more complex logic. For example, the Symfony Framework uses
expressions in security, for validation rules and in route matching.

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

Expressions can be seen as a very restricted PHP sandbox and are less vulnerable
to external injections because you must explicitly declare which variables are
available in an expression (but you should still sanitize any data given by end
users and passed to expressions).

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

.. tip::

    See :doc:`/reference/formats/expression_language` to learn the syntax of
    the ExpressionLanguage component.

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

Passing in Variables
--------------------

You can also pass variables into the expression, which can be of any valid
PHP type (including objects)::

    use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

    $expressionLanguage = new ExpressionLanguage();

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

.. _expression-language-caching:

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

.. _expression-language-ast:

AST Dumping and Editing
-----------------------

It's difficult to manipulate or inspect the expressions created with the ExpressionLanguage
component, because the expressions are plain strings. A better approach is to
turn those expressions into an AST. In computer science, `AST`_ (*Abstract
Syntax Tree*) is *"a tree representation of the structure of source code written
in a programming language"*. In Symfony, an ExpressionLanguage AST is a set of
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
    $expressionLanguage->register('lowercase', function ($str): string {
        return sprintf('(is_string(%1$s) ? strtolower(%1$s) : %1$s)', $str);
    }, function ($arguments, $str): string {
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
        public function getFunctions(): array
        {
            return [
                new ExpressionFunction('lowercase', function ($str): string {
                    return sprintf('(is_string(%1$s) ? strtolower(%1$s) : %1$s)', $str);
                }, function ($arguments, $str): string {
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
