Our Backwards Compatibility Promise
===================================

If you are using Symfony, we promise you backwards compatibility (BC) for all
minor releases (2.5, 2.6, etc.). Ensuring smooth upgrades of your projects is our
first priority. However, backwards compatibility comes in many different flavors.

.. caution::

    This promise was introduced with Symfony 2.3 and does not apply to previous
    versions of Symfony.

This page has two different target audiences: If you are using Symfony, it will
tell you how to make sure that you will be able to upgrade smoothly to all
future 2.x versions. If you are contributing to Symfony, this page will tell you
the rules that you need to follow to ensure smooth upgrades for our users.

Using Symfony Code
------------------

If you are using Symfony in your projects, the following guidelines will help
you to ensure smooth upgrades to all future minor releases of Symfony (such as
2.5, 2.6 and so on).

Using Our Interfaces
~~~~~~~~~~~~~~~~~~~~

All interfaces shipped with Symfony can be used in type hints. You can also call
any of the methods that they declare. We guarantee that we won't break code that
sticks to these rules.

.. caution::

    The exception to this rule are interfaces tagged with ``@internal``. Such
    interfaces should *never* be used or implemented.

If you want to implement an interface, you should first make sure that the
interface is an API interface. You can recognize API interfaces by the ``@api``
tag in their source code::

    /**
     * HttpKernelInterface handles a Request to convert it to a Response.
     *
     * @author Fabien Potencier <fabien@symfony.com>
     *
     * @api
     */
    interface HttpKernelInterface
    {
        // ...
    }

If you implement an API interface, we promise that we won't ever break your
code. Regular interfaces, by contrast, should never be implemented, because if
we add a new method to the interface or add a new optional parameter to the
signature of a method, we would generate a fatal error in your application.

The following table explains in detail which use cases are covered by our
backwards compatibility promise:

+-----------------------------------------------+---------------+---------------+
| Use Case                                      | Regular       | API           |
+===============================================+===============+===============+
| **If you...**                                 | **Then we guarantee BC...**   |
+-----------------------------------------------+---------------+---------------+
| Type hint against the interface               | Yes           | Yes           |
+-----------------------------------------------+---------------+---------------+
| Call a method                                 | Yes           | Yes           |
+-----------------------------------------------+---------------+---------------+
| **If you implement the interface and...**     | **Then we guarantee BC...**   |
+-----------------------------------------------+---------------+---------------+
| Implement a method                            | No [1]_       | Yes           |
+-----------------------------------------------+---------------+---------------+
| Add a parameter to an implemented method      | No [1]_       | Yes           |
+-----------------------------------------------+---------------+---------------+
| Add a default value to a parameter            | Yes           | Yes           |
+-----------------------------------------------+---------------+---------------+

.. note::

    If you think that one of our regular interfaces should have an ``@api`` tag,
    put your request into a `new ticket on GitHub`_. We will then evaluate
    whether we can add the tag or not.

Using Our Classes
~~~~~~~~~~~~~~~~~

All classes provided by Symfony may be instantiated and accessed through their
public methods and properties.

.. caution::

    Classes, properties and methods that bear the tag ``@internal`` as well as
    the classes located in the various ``*\\Tests\\`` namespaces are an
    exception to this rule. They are meant for internal use only and should
    *never* be accessed by your own code.

Just like with interfaces, we also distinguish between regular and API classes.
Like API interfaces, API classes are marked with an ``@api`` tag::

    /**
     * Request represents an HTTP request.
     *
     * @author Fabien Potencier <fabien@symfony.com>
     *
     * @api
     */
    class Request
    {
        // ...
    }

The difference between regular and API classes is that we guarantee full
backwards compatibility if you extend an API class and override its methods. We
can't give the same promise for regular classes, because there we may, for
example, add an optional parameter to a method. Consequently, the signature of
your overridden method wouldn't match anymore and generate a fatal error.

In some cases, only specific properties and methods are tagged with the ``@api``
tag, even though their class is not. In these cases, we guarantee full backwards
compatibility for the tagged properties and methods (as indicated in the column
"API" below), but not for the rest of the class.

To be on the safe side, check the following table to know which use cases are
covered by our backwards compatibility promise:

+-----------------------------------------------+---------------+---------------+
| Use Case                                      | Regular       | API           |
+===============================================+===============+===============+
| **If you...**                                 | **Then we guarantee BC...**   |
+-----------------------------------------------+---------------+---------------+
| Type hint against the class                   | Yes           | Yes           |
+-----------------------------------------------+---------------+---------------+
| Create a new instance                         | Yes           | Yes           |
+-----------------------------------------------+---------------+---------------+
| Extend the class                              | Yes           | Yes           |
+-----------------------------------------------+---------------+---------------+
| Access a public property                      | Yes           | Yes           |
+-----------------------------------------------+---------------+---------------+
| Call a public method                          | Yes           | Yes           |
+-----------------------------------------------+---------------+---------------+
| **If you extend the class and...**            | **Then we guarantee BC...**   |
+-----------------------------------------------+---------------+---------------+
| Access a protected property                   | No [1]_       | Yes           |
+-----------------------------------------------+---------------+---------------+
| Call a protected method                       | No [1]_       | Yes           |
+-----------------------------------------------+---------------+---------------+
| Override a public property                    | Yes           | Yes           |
+-----------------------------------------------+---------------+---------------+
| Override a protected property                 | No [1]_       | Yes           |
+-----------------------------------------------+---------------+---------------+
| Override a public method                      | No [1]_       | Yes           |
+-----------------------------------------------+---------------+---------------+
| Override a protected method                   | No [1]_       | Yes           |
+-----------------------------------------------+---------------+---------------+
| Add a new property                            | No            | No            |
+-----------------------------------------------+---------------+---------------+
| Add a new method                              | No            | No            |
+-----------------------------------------------+---------------+---------------+
| Add a parameter to an overridden method       | No [1]_       | Yes           |
+-----------------------------------------------+---------------+---------------+
| Add a default value to a parameter            | Yes           | Yes           |
+-----------------------------------------------+---------------+---------------+

.. note::

    If you think that one of our regular classes should have an ``@api`` tag,
    put your request into a `new ticket on GitHub`_. We will then evaluate
    whether we can add the tag or not.

Working on Symfony Code
-----------------------

Do you want to help us improve Symfony? That's great! However, please stick
to the rules listed below in order to ensure smooth upgrades for our users.

Changing Interfaces
~~~~~~~~~~~~~~~~~~~

This table tells you which changes you are allowed to do when working on
Symfony's interfaces:

==============================================  ==============  ==============
Type of Change                                  Regular         API
==============================================  ==============  ==============
Remove entirely                                 No              No
Change name or namespace                        No              No
Add parent interface                            Yes [2]_        No
Remove parent interface                         No              No
**Methods**
Add method                                      Yes [2]_        No
Remove method                                   No              No
Change name                                     No              No
Add parameter without a default value           No              No
Add parameter with a default value              Yes [2]_        No
Remove parameter                                Yes [3]_        Yes [3]_
Add default value to a parameter                Yes [2]_        No
Remove default value of a parameter             No              No
Add type hint to a parameter                    No              No
Remove type hint of a parameter                 Yes [2]_        No
Change parameter type                           Yes [2]_ [4]_   No
Change return type                              Yes [2]_ [5]_   No
==============================================  ==============  ==============

Changing Classes
~~~~~~~~~~~~~~~~

This table tells you which changes you are allowed to do when working on
Symfony's classes:

==================================================  ==============  ==============
Type of Change                                      Regular         API
==================================================  ==============  ==============
Remove entirely                                     No              No
Make final                                          Yes [2]_        No
Make abstract                                       No              No
Change name or namespace                            No              No
Change parent class                                 Yes [6]_        Yes [6]_
Add interface                                       Yes             Yes
Remove interface                                    No              No
**Public Properties**
Add public property                                 Yes             Yes
Remove public property                              No              No
Reduce visibility                                   No              No
**Protected Properties**
Add protected property                              Yes             Yes
Remove protected property                           Yes [2]_        No
Reduce visibility                                   Yes [2]_        No
**Constructors**
Add constructor without mandatory parameters        Yes [2]_        Yes [2]_
Remove constructor                                  Yes [2]_        No
Reduce visibility of a public constructor           No              No
Reduce visibility of a protected constructor        Yes [2]_        No
**Public Methods**
Add public method                                   Yes             Yes
Remove public method                                No              No
Change name                                         No              No
Reduce visibility                                   No              No
Add parameter without a default value               No              No
Add parameter with a default value                  Yes [2]_        No
Remove parameter                                    Yes [3]_        Yes [3]_
Add default value to a parameter                    Yes [2]_        No
Remove default value of a parameter                 No              No
Add type hint to a parameter                        Yes [7]_        No
Remove type hint of a parameter                     Yes [2]_        No
Change parameter type                               Yes [2]_ [4]_   No
Change return type                                  Yes [2]_ [5]_   No
**Protected Methods**
Add protected method                                Yes             Yes
Remove protected method                             Yes [2]_        No
Change name                                         No              No
Reduce visibility                                   Yes [2]_        No
Add parameter without a default value               Yes [2]_        No
Add parameter with a default value                  Yes [2]_        No
Remove parameter                                    Yes [3]_        Yes [3]_
Add default value to a parameter                    Yes [2]_        No
Remove default value of a parameter                 Yes [2]_        No
Add type hint to a parameter                        Yes [2]_        No
Remove type hint of a parameter                     Yes [2]_        No
Change parameter type                               Yes [2]_ [4]_   No
Change return type                                  Yes [2]_ [5]_   No
==================================================  ==============  ==============

.. [1] Your code may be broken by changes in the Symfony code. Such changes will
       however be documented in the UPGRADE file.

.. [2] Should be avoided. When done, this change must be documented in the
       UPGRADE file.

.. [3] Only the last parameter(s) of a method may be removed.

.. [4] The parameter type may only be changed to a compatible or less specific
       type. The following type changes are allowed:

       ===================  ==================================================================
       Original Type        New Type
       ===================  ==================================================================
       boolean              any `scalar type`_ with equivalent `boolean values`_
       string               any `scalar type`_ or object with equivalent `string values`_
       integer              any `scalar type`_ with equivalent `integer values`_
       float                any `scalar type`_ with equivalent `float values`_
       class ``<C>``        any superclass or interface of ``<C>``
       interface ``<I>``    any superinterface of ``<I>``
       ===================  ==================================================================

.. [5] The return type may only be changed to a compatible or more specific
       type. The following type changes are allowed:

       ===================  ==================================================================
       Original Type        New Type
       ===================  ==================================================================
       boolean              any `scalar type`_ with equivalent `boolean values`_
       string               any `scalar type`_ or object with equivalent `string values`_
       integer              any `scalar type`_ with equivalent `integer values`_
       float                any `scalar type`_ with equivalent `float values`_
       array                instance of ``ArrayAccess``, ``Traversable`` and ``Countable``
       ``ArrayAccess``      array
       ``Traversable``      array
       ``Countable``        array
       class ``<C>``        any subclass of ``<C>``
       interface ``<I>``    any subinterface or implementing class of ``<I>``
       ===================  ==================================================================

.. [6] When changing the parent class, the original parent class must remain an
       ancestor of the class.

.. [7] A type hint may only be added if passing a value with a different type
       previously generated a fatal error.

.. _scalar type: http://php.net/manual/en/function.is-scalar.php
.. _boolean values: http://php.net/manual/en/function.boolval.php
.. _string values: http://www.php.net/manual/en/function.strval.php
.. _integer values: http://www.php.net/manual/en/function.intval.php
.. _float values: http://www.php.net/manual/en/function.floatval.php
.. _new ticket on GitHub: https://github.com/symfony/symfony/issues/new
