.. index::
    single: Caching; ExpressionLanguage

Caching Expressions Using Parser Caches
=======================================

The ExpressionLanguage component already provides a
:method:`Symfony\\Component\\ExpressionLanguage\\ExpressionLanguage::compile`
method to be able to cache the expressions in plain PHP. But internally, the
component also caches the parsed expressions, so duplicated expressions can be
compiled/evaluated quicker.

The Workflow
------------

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
---------------------------------------

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

.. _`CacheItemPoolInterface`: https://github.com/php-fig/cache/blob/master/src/CacheItemPoolInterface.php
