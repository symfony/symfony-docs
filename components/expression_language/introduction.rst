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

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/expression-language`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/expression-language).

How can the Expression Engine Help Me?
--------------------------------------

The purpose of the component is to allow users to use expressions inside
configuration for more complex logic. For some examples, the Symfony2 Framework
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

    $language = new ExpressionLanguage();

    var_dump($language->evaluate('1 + 2')); // displays 3

    var_dump($language->compile('1 + 2')); // displays (1 + 2)

Expression Syntax
-----------------

See :doc:`/components/expression_language/syntax` to learn the syntax of the
ExpressionLanguage component.

Passing in Variables
--------------------

You can also pass variables into the expression, which can be of any valid
PHP type (including objects)::

    use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

    $language = new ExpressionLanguage();

    class Apple
    {
        public $variety;
    }

    $apple = new Apple();
    $apple->variety = 'Honeycrisp';

    var_dump($language->evaluate(
        'fruit.variety',
        array(
            'fruit' => $apple,
        )
    ));

This will print "Honeycrisp". For more information, see the :doc:`/components/expression_language/syntax`
entry, especially :ref:`component-expression-objects` and :ref:`component-expression-arrays`.

Caching
-------

The component provides some different caching strategies, read more about them
in :doc:`/components/expression_language/caching`.

.. _Packagist: https://packagist.org/packages/symfony/expression-language
