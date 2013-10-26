.. index::
    single: Syntax; ExpressionLanguage

The Expression Syntax
=====================

The ExpressionLanguage component uses a specific syntax which is based on the
expression syntax of Twig. In this document, you can find all supported
syntaxes.

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
* ``matches`` (regex match)

.. tip::

    To test if a string does *not* match a regex, use the logical ``not``
    operator in combination with the ``matches`` operator::

        $language->evaluate('not "foo" matches "/bar/"'); // returns true

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

Ternary Operators
.................

* ``foo ? 'yes' : 'no'``
* ``foo ?: 'no'`` (equal to ``foo ? foo : 'no'``)
* ``foo ? 'yes'`` (equal to ``foo ? 'yes' : ''``)
