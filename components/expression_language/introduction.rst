.. index::
    single: Expressions
    Single: Components; Expression Language

The ExpressionLanguage Component
=================================

    The ExpressionLanguage component provides an engine that can compile and
    evaluate expressions. An expression is a one-liner that returns a value
    (mostly, but not limited to, Booleans).

.. versionadded:: 2.4
    The ExpressionLanguage component was new in Symfony 2.4.

Installation
------------

You can install the component in 2 different ways:

* Use the official Git repository (https://github.com/symfony/expression-language);
* :doc:`Install it via Composer </components/using_components>` (``symfony/expression-language`` on `Packagist`_).

Usage
-----

The ExpressionLanguage component can compile and evaluate expressions.
Expressions are one-liners which most of the time return a boolean, you can
compare them to the expression in an ``if`` statement. A simple example of an
expression is ``1 + 2``. You can also use more complicated expressions, such
as ``someArray[3].someMethod('bar')``.

The component provides 2 ways to work with expressions:

* **compile**: the expression is compiled to PHP, so it can be cached and
  evaluated;
* **evaluation**: the expression is evaluated without being compiled to PHP.

The main class of the component is
:class:`Symfony\\Component\\ExpressionLanguage\\ExpressionLanguage`::

    use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

    $language = new ExpressionLanguage();

    echo $language->evaluate('1 + 2'); // displays 3

    echo $language->compile('1 + 2'); // displays (1 + 2)

Expression Syntax
-----------------

See ":doc:`/components/expression_language/syntax`" to learn the syntax of the
ExpressionLanguage component.

.. _Packagist: https://packagist.org/packages/symfony/expression-language
