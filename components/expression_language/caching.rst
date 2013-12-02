.. index::
    single: Caching; ExpressionLanguage

Caching Expressions Using ParserCaches
======================================

The ExpressionLanguage component already provides a
:method:`Symfony\\Component\\ExpresionLanguage\\ExpressionLanguage::compile`
method to be able to cache the expressions in plain PHP. But internally, the
component also caches the parsed expressions, so duplicated expressions can be
compiled/evaluated quicker.

The Workflow
------------

Both ``evaluate`` and ``compile`` needs to do some things before it can
provide the return values. For ``evaluate``, this overhead is even bigger.

Both methods need to tokenize and parse the expression. This is done by the 
:method:`Symfony\\Component\\ExpressionLanguage\\ExpressionLanguage::parse`
method. It'll return a :class:`Symfony\\Component\\ExpressionLanguage\\ParsedExpression`.
Now, the ``compile`` method just returns the string conversion of this object.
The ``evaluate`` method needs to loop through the "nodes" (pieces of an
expression saved in the ``ParsedExpression``) and evaluate them on the fly.

To save time, the ``ExpressionLanguage`` caches the ``ParsedExpression``, so
it can skip the tokenize and parse steps with duplicate expressions.
The caching is done by a
:class:`Symfony\\Component\\ExpressionLanguage\\ParserCache\\ParserCacheInterface`
instance (by default, it uses an
:class:`Symfony\\Component\\ExpressionLanguage\\ParserCache\\ArrayParserCache`).
You can customize this by creating a custom ``ParserCache`` and injecting this
in the object using the constructor::

    use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
    use Acme\ExpressionLanguage\ParserCache\MyDatabaseParserCache;

    $cache = new MyDatabaseParserCache(...);
    $language = new ExpressionLanguage($cache);

Using Parsed and Serialized Expressions
---------------------------------------

Both ``evaluate`` and ``compile`` can handle ``ParsedExpression`` and
``SerializedParsedExpression``::

    use Symfony\Component\ExpressionLanguage\ParsedExpression;
    // ...

    $expression = new ParsedExpression($language->parse('1 + 4'));

    echo $language->evaluate($expression); // prints 5

.. code-block:: php

    use Symfony\Component\ExpressionLanguage\SerializedParsedExpression;
    // ...

    $expression = new SerializedParsedExpression(serialize($language->parse('1 + 4')));

    echo $language->evaluate($expression); // prints 5
