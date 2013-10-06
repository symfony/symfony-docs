.. index::
    single: Expressions
    Single: Components; Expression Language

The ExpressionLanguage Component
=================================

    The ExpressionLanguage component provides an engine that can compile and
    evaluate expressions. An expression is a one-liner that returns a value
    (mostly, but not limited to, Booleans).

.. versionadded:: 2.4
    The ExpressionLanguage Component was new in Symfony 2.4.

Installation
------------

You can install the component in 2 different ways:

* Use the official Git repository (https://github.com/symfony/ExpressionLanguage);
* :doc:`Install it via Composer </components/using_components>` (``symfony/expression-language`` on `Packagist`_).

Usage
-----

The ExpressionLanguage component can compile and evaluate expressions.
Expressions are one-liners which most of the time return a boolean, you can
compare them to the expression in an ``if`` statement. A simple example of an
expression is ``1 + 2``. You can also use more complicated expressions, such
as ``someArray[3].someMethod('bar')``.

The component provide 2 ways to work with expressions:

* **compile**: the expression is compiled to PHP, so it can be cached and
  evaluated;
* **evaluation**: the expression is evaluated without being compiled to PHP.

The main class of the component is
:class:`Symfony\\Component\\ExpressionLanguage\\ExpressionLanguage`::

    use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

    $language = new ExpressionLanguage();

    echo $language->evaluate('1 + 2'); // displays 3

    echo $language->compile('1 + 2'); // displays (1 + 2)

Supported Literals
~~~~~~~~~~~~~~~~~~

The component supports:

* **strings** - single and double quotes (e.g. ``'hello'``)
* **numbers** - e.g. ``103``
* **arrays** - using twig notation (e.g. ``[1, 2]``)
* **hashes** - using twig notation (e.g. ``{ foo: 'bar' }``)
* **booleans** - ``true`` and ``false``
* **null** - ``null``

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

Assignment Operators
....................

* ``=``

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
* ``=~`` (regex match)
* ``!~`` (regex does not match)

.. sidebar:: Regex Operator

    The Regex Operators (``=~`` and ``!~``) are coming from Perl. This
    operator matches if the regular expression on the right side of the
    operator matches the string on the left. For instance,
    ``'foobar' =~ '/foo/'`` evaluates to true.
    ``!~`` is the opposite and matches if the regular expression does *not*
    match the string.

Logical Operators
.................

* ``not`` or ``!``
* ``and`` or ``&&``
* ``or`` or ``||``

String Operators
................

* ``~`` (concatenation)

Array Operators
...............

* ``in`` (contain)
* ``not in`` (does not contain)

Numeric Operators
.................

* ``..`` (range)

.. _Packagist: https://packagist.org/packages/symfony/expression-language
