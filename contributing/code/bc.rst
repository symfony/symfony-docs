Our Backwards Compatibility Promise
===================================

As of Symfony 2.3, we promise you backwards compatibility (BC) for all further
2.x releases. Ensuring smooth upgrades of your projects is our first priority.
However, backwards compatibility comes in many different flavors.

This page has two different target audiences: If you are using Symfony, it will
tell you how to make sure that you will be able to upgrade smoothly to all
future 2.x versions. If you are contributing to Symfony, this page will tell you
the rules that you need to follow to ensure smooth upgrades for our users.

.. note::

    This promise is in trial until April 15th, 2014. Until then, we may change
    parts of it if we discover problems or limitations.


Using Symfony Code
------------------

You are using Symfony in your projects? Then stick to the guidelines in this
section in order to guarantee smooth upgrades to all future 2.x versions.


Using Our Interfaces
~~~~~~~~~~~~~~~~~~~~

In Symfony, we distinguish between regular and API interfaces. API interfaces
are marked with an ``@api`` tag in their source code. For example::

    /**
     * HttpKernelInterface handles a Request to convert it to a Response.
     *
     * @author Fabien Potencier <fabien@symfony.com>
     *
     * @api
     */
    interface HttpKernelInterface
    {

When using these interfaces, we guarantee full backwards compatibility for the
following use cases:

==============================================  ==============  ==============
Use Case                                        Regular         API
==============================================  ==============  ==============
Type hint against                               Yes             Yes
Call method                                     Yes             Yes
**In Implementing Classes**
Implement method                                No [1]_         Yes
Add custom method                               No [1]_         Yes
Add custom method parameter                     No [1]_         Yes
Add parameter default value                     Yes             Yes
==============================================  ==============  ==============

.. note::

    If you need to do any of the things marked with "No" above, feel free to
    ask us whether the ``@api`` tag can be added on the respective Symfony code.
    For that, simply open a `new ticket on GitHub`_.

Interfaces or interface methods tagged with ``@internal`` should never be
implemented or used.


Using Our Classes
~~~~~~~~~~~~~~~~~

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

When using these classes, we guarantee full backwards compatibility for the
following use cases:

==============================================  ==============  ==============
Use Case                                        Regular         API
==============================================  ==============  ==============
Type hint against                               Yes             Yes
Create instance                                 Yes             Yes
Extend                                          Yes             Yes
Access public property                          Yes             Yes
Call public method                              Yes             Yes
**In Extending Classes**
Access protected property                       No [1]_         Yes
Call protected method                           No [1]_         Yes
Override public property                        Yes             Yes
Override protected property                     No [1]_         Yes
Override public method                          No [1]_         Yes
Override protected method                       No [1]_         Yes
Add custom property                             No              No
Add custom method                               No              No
Add custom method parameter                     No [1]_         Yes
Add parameter default value                     Yes             Yes
==============================================  ==============  ==============

.. note::

    If you need to do any of the things marked with "No" above, feel free to
    ask us whether the ``@api`` tag can be added on the respective Symfony code.
    For that, simply open a `new ticket on GitHub`_.

In some cases, only specific properties and methods are tagged with the ``@api``
tag, even though their class is not. In these cases, we guarantee full backwards
compatibility for the tagged properties and methods (as indicated in the column
"API" above), but not for the rest of the class.

Classes, properties and methods tagged with ``@internal`` should never be
created, extended or called directly. The same applies to all classes located in
the various ``*\\Tests\\`` namespaces.


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
