Our Backwards Compatibility Promise
===================================

As of Symfony 2.3, we promise you backwards compatibility for all further 2.x
releases. Ensuring smooth upgrades of your projects is our first priority.
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

Normal Interfaces
.................

All interfaces in the ``Symfony`` namespace are **safe for use**. That means
that:

* You can safely type hint against the interface.

* You can safely use any of the methods provided by the interface.

However:

* You cannot safely implement the interface. The interface may change, but all
  changes will be documented in the UPGRADE file.

Methods tagged with ``@api`` are treated as if they belonged to an API
interface.


API Interfaces
..............

All interfaces tagged with ``@api`` are also **safe for implementation**. That
means that:

* You can safely implement the interface.


Internal Interfaces
...................

Interfaces or interface methods tagged with ``@internal`` are meant for internal
use in Symfony only. You should never use nor implement them.


Safe Operations
...............

The following table summarizes the safe operations when using our interfaces:

==============================================  ==============  ==============
Operation                                       Normal          API
==============================================  ==============  ==============
Type hint against                               Safe            Safe
Use method                                      Safe            Safe
**In Implementing Classes**
Implement method                                Not Safe [1]_   Safe
Add custom method                               Not Safe [1]_   Safe
Add custom method parameter                     Not Safe [1]_   Safe
Add parameter default value                     Safe            Safe
==============================================  ==============  ==============

If you need to do any of the things marked as "Not Safe" above, feel free to
ask us whether the ``@api`` tag can be added on the respective Symfony code.
For that, simply open a `new ticket on GitHub`_.


Using Our Classes
~~~~~~~~~~~~~~~~~

Normal Classes
..............

All classes in the ``Symfony`` namespace are **safe for use**. That means that:

* You can safely type hint against the class' name.

* You can safely create new instances.

* You can safely extend the class.

* You can safely use public properties and methods.

When extending the class:

* You can safely override public properties.

However:

* You cannot safely override methods in extending classes. The class may change,
  but all changes will be documented in the UPGRADE file.

Properties and methods tagged with ``@api`` are treated as if they belonged
to an API class.


API Classes
...........

All classes tagged with ``@api`` are also **safe for extension**. That means
that:

* You can safely use protected properties and methods.

* You can safely override protected properties.

* You can safely override public or protected methods.


Internal Classes
................

Classes, properties and class methods tagged with ``@internal`` are meant for
internal use in Symfony only. You should never use nor extend them.


Safe Operations
...............

The following table summarizes the safe operations when using our classes:

==============================================  ==============  ==============
Operation                                       Normal          API
==============================================  ==============  ==============
Type hint against                               Safe            Safe
Create instance                                 Safe            Safe
Extend                                          Safe            Safe
Use public property                             Safe            Safe
Use public method                               Safe            Safe
**In Extending Classes**
Use protected property                          Not Safe [1]_   Safe
Use protected method                            Not Safe [1]_   Safe
Override public property                        Safe            Safe
Override protected property                     Not Safe [1]_   Safe
Override public method                          Not Safe [1]_   Safe
Override protected method                       Not Safe [1]_   Safe
Add custom property                             Not Safe        Not Safe
Add custom method                               Not Safe        Not Safe
Add custom method parameter                     Not Safe [1]_   Safe
Add parameter default value                     Safe            Safe
==============================================  ==============  ==============

If you need to do any of the things marked as "Not Safe" above, feel free to
ask us whether the ``@api`` tag can be added on the respective Symfony code.
For that, simply open a `new ticket on GitHub`_.


Working on Symfony Code
-----------------------

Do you want to help us improve Symfony? That's great! However, please stick
to the rules listed below in order to ensure smooth upgrades for our users.


Changing Interfaces
~~~~~~~~~~~~~~~~~~~

This table tells you which changes you are allowed to do when working on
Symfony's interfaces:

==============================================  ==============  ==============
Type of Change                                  Normal          API
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
Type of Change                                      Normal          API
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
