Our backwards Compatibility Promise
===================================

Ensuring smooth upgrades of your projects is our first priority. That's why
we promise you backwards compatibility (BC) for all minor Symfony releases.
You probably recognize this strategy as `Semantic Versioning`_. In short,
Semantic Versioning means that only major releases (such as 2.0, 3.0 etc.) are
allowed to break backwards compatibility. Minor releases (such as 2.5, 2.6 etc.)
may introduce new features, but must do so without breaking the existing API of
that release branch (2.x in the previous example).

.. caution::

    This promise was introduced with Symfony 2.3 and does not apply to previous
    versions of Symfony.

However, backwards compatibility comes in many different flavors. In fact, almost
every change that we make to the framework can potentially break an application.
For example, if we add a new method to a class, this will break an application
which extended this class and added the same method, but with a different
method signature.

Also, not every BC break has the same impact on application code. While some BC
breaks require you to make significant changes to your classes or your
architecture, others are fixed as easily as changing the name of a method.

That's why we created this page for you. The section "Using Symfony Code" will
tell you how you can ensure that your application won't break completely when
upgrading to a newer version of the same major release branch.

The second section, "Working on Symfony Code", is targeted at Symfony
contributors. This section lists detailed rules that every contributor needs to
follow to ensure smooth upgrades for our users.

Using Symfony Code
------------------

If you are using Symfony in your projects, the following guidelines will help
you to ensure smooth upgrades to all future minor releases of your Symfony
version.

Using our Interfaces
~~~~~~~~~~~~~~~~~~~~

All interfaces shipped with Symfony can be used in type hints. You can also call
any of the methods that they declare. We guarantee that we won't break code that
sticks to these rules.

.. caution::

    The exception to this rule are interfaces tagged with ``@internal``. Such
    interfaces should not be used or implemented.

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
code. Regular interfaces, by contrast, may be extended between minor releases,
for example by adding a new method. Be prepared to upgrade your code manually
if you implement a regular interface.

.. note::

    Even if we do changes that require manual upgrades, we limit ourselves to
    changes that can be upgraded easily. We will always document the precise
    upgrade instructions in the UPGRADE file in Symfony's root directory.

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
| Add an argument to an implemented method      | No [1]_       | Yes           |
+-----------------------------------------------+---------------+---------------+
| Add a default value to an argument            | Yes           | Yes           |
+-----------------------------------------------+---------------+---------------+

.. include:: _api_tagging.rst.inc

Using our Classes
~~~~~~~~~~~~~~~~~

All classes provided by Symfony may be instantiated and accessed through their
public methods and properties.

.. caution::

    Classes, properties and methods that bear the tag ``@internal`` as well as
    the classes located in the various ``*\\Tests\\`` namespaces are an
    exception to this rule. They are meant for internal use only and should
    not be accessed by your own code.

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
example, add an optional argument to a method. Consequently, the signature of
your overridden method wouldn't match anymore and generate a fatal error.

.. note::

    As with interfaces, we limit ourselves to changes that can be upgraded
    easily. We will document the precise ugprade instructions in the UPGRADE
    file in Symfony's root directory.

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
| Add an argument to an overridden method       | No [1]_       | Yes           |
+-----------------------------------------------+---------------+---------------+
| Add a default value to an argument            | Yes           | Yes           |
+-----------------------------------------------+---------------+---------------+
| Call a private method (via Reflection)        | No            | No            |
+-----------------------------------------------+---------------+---------------+
| Access a private property (via Reflection)    | No            | No            |
+-----------------------------------------------+---------------+---------------+

.. include:: _api_tagging.rst.inc

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
Add parent interface                            Yes [2]_        Yes [3]_
Remove parent interface                         No              No
**Methods**
Add method                                      Yes [2]_        No
Remove method                                   No              No
Change name                                     No              No
Move to parent interface                        Yes             Yes
Add argument without a default value            No              No
Add argument with a default value               Yes [2]_        No
Remove argument                                 Yes [4]_        Yes [4]_
Add default value to an argument                Yes [2]_        No
Remove default value of an argument             No              No
Add type hint to an argument                    No              No
Remove type hint of an argument                 Yes [2]_        No
Change argument type                            Yes [2]_ [5]_   No
Change return type                              Yes [2]_ [6]_   No
==============================================  ==============  ==============

Changing Classes
~~~~~~~~~~~~~~~~

This table tells you which changes you are allowed to do when working on
Symfony's classes:

==================================================  ==============  ==============
Type of Change                                      Regular         API
==================================================  ==============  ==============
Remove entirely                                     No              No
Make final                                          No              No
Make abstract                                       No              No
Change name or namespace                            No              No
Change parent class                                 Yes [7]_        Yes [7]_
Add interface                                       Yes             Yes
Remove interface                                    No              No
**Public Properties**
Add public property                                 Yes             Yes
Remove public property                              No              No
Reduce visibility                                   No              No
Move to parent class                                Yes             Yes
**Protected Properties**
Add protected property                              Yes             Yes
Remove protected property                           Yes [2]_        No
Reduce visibility                                   Yes [2]_        No
Move to parent class                                Yes             Yes
**Private Properties**
Add private property                                Yes             Yes
Remove private property                             Yes             Yes
**Constructors**
Add constructor without mandatory arguments         Yes [2]_        Yes [2]_
Remove constructor                                  Yes [2]_        No
Reduce visibility of a public constructor           No              No
Reduce visibility of a protected constructor        Yes [2]_        No
Move to parent class                                Yes             Yes
**Public Methods**
Add public method                                   Yes             Yes
Remove public method                                No              No
Change name                                         No              No
Reduce visibility                                   No              No
Move to parent class                                Yes             Yes
Add argument without a default value                No              No
Add argument with a default value                   Yes [2]_        No
Remove argument                                     Yes [4]_        Yes [4]_
Add default value to an argument                    Yes [2]_        No
Remove default value of an argument                 No              No
Add type hint to an argument                        Yes [8]_        No
Remove type hint of an argument                     Yes [2]_        No
Change argument type                                Yes [2]_ [5]_   No
Change return type                                  Yes [2]_ [6]_   No
**Protected Methods**
Add protected method                                Yes             Yes
Remove protected method                             Yes [2]_        No
Change name                                         No              No
Reduce visibility                                   Yes [2]_        No
Move to parent class                                Yes             Yes
Add argument without a default value                Yes [2]_        No
Add argument with a default value                   Yes [2]_        No
Remove argument                                     Yes [4]_        Yes [4]_
Add default value to an argument                    Yes [2]_        No
Remove default value of an argument                 Yes [2]_        No
Add type hint to an argument                        Yes [2]_        No
Remove type hint of an argument                     Yes [2]_        No
Change argument type                                Yes [2]_ [5]_   No
Change return type                                  Yes [2]_ [6]_   No
**Private Methods**
Add private method                                  Yes             Yes
Remove private method                               Yes             Yes
Change name                                         Yes             Yes
Reduce visibility                                   Yes             Yes
Add argument without a default value                Yes             Yes
Add argument with a default value                   Yes             Yes
Remove argument                                     Yes             Yes
Add default value to an argument                    Yes             Yes
Remove default value of an argument                 Yes             Yes
Add type hint to an argument                        Yes             Yes
Remove type hint of an argument                     Yes             Yes
Change argument type                                Yes             Yes
Change return type                                  Yes             Yes
==================================================  ==============  ==============

.. [1] Your code may be broken by changes in the Symfony code. Such changes will
       however be documented in the UPGRADE file.

.. [2] Should be avoided. When done, this change must be documented in the
       UPGRADE file.

.. [3] The added parent interface must not introduce any new methods that don't
       exist in the interface already.

.. [4] Only the last argument(s) of a method may be removed, as PHP does not
       care about additional arguments that you pass to a method.

.. [5] The argument type may only be changed to a compatible or less specific
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

.. [6] The return type may only be changed to a compatible or more specific
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

.. [7] When changing the parent class, the original parent class must remain an
       ancestor of the class.

.. [8] A type hint may only be added if passing a value with a different type
       previously generated a fatal error.

.. _Semantic Versioning: http://semver.org/
.. _scalar type: http://php.net/manual/en/function.is-scalar.php
.. _boolean values: http://php.net/manual/en/function.boolval.php
.. _string values: http://www.php.net/manual/en/function.strval.php
.. _integer values: http://www.php.net/manual/en/function.intval.php
.. _float values: http://www.php.net/manual/en/function.floatval.php
