Our Backwards Compatibility Promise
===================================

As of Symfony 2.3, we promise you backwards compatibility for all further 2.x
releases. Ensuring smooth upgrades of your projects is our first priority.
However, backwards compatibility comes in many different flavors. This page
lists which code changes are covered by our promise and to what degree.

If you are contributing to Symfony, make sure that your code changes comply to
the rules listed below.

.. note::

    This promise is in trial until April 15th, 2014. Until then, we may change
    parts of it if we discover problems or limitations.


Interfaces
----------

Normal Interfaces
~~~~~~~~~~~~~~~~~

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
~~~~~~~~~~~~~~

All interfaces tagged with ``@api`` are also **safe for implementation**. That
means that:

* You can safely implement the interface.


Safe Operations
~~~~~~~~~~~~~~~

The following table summarizes the safe operations when using our interfaces:

==============================================  ==============  ==============
Operation                                       Normal          API
==============================================  ==============  ==============
Type hint against                               Safe            Safe
Use method                                      Safe            Safe
**When Implementing**
Implement method                                Not Safe        Safe
Add custom method                               Not Safe        Not Safe
Add custom method parameter                     Not Safe        Not Safe
Add parameter default value                     Safe            Safe
==============================================  ==============  ==============


Allowed Changes
~~~~~~~~~~~~~~~

This table tells you which changes you are allowed to do when working on
Symfony code:

==============================================  ==============  ==============
Type of Change                                  Normal          API
==============================================  ==============  ==============
Remove entirely                                 No              No
Change name or namespace                        No              No
Add parent interface                            Yes [1]_        No
Remove parent interface                         No              No
**Methods**
Add method                                      Yes [1]_        No
Remove method                                   No              No
Change name                                     No              No
Add parameter without a default value           No              No
Add parameter with a default value              Yes [1]_        No
Remove parameter                                Yes [2]_        Yes [2]_
Add default value to a parameter                Yes [1]_        No
Remove default value of a parameter             No              No
Add type hint to a parameter                    No              No
Remove type hint of a parameter                 Yes [1]_        No
Change return type                              Yes [1]_ [3]_   No
==============================================  ==============  ==============


Classes
-------

Normal Classes
~~~~~~~~~~~~~~

All classes in the ``Symfony`` namespace are **safe for use**. That means that:

* You can safely type hint against the class' name.

* You can safely create new instances.

* You can safely extend the class.

* You can safely use public properties and methods.

When extending the class:

* You cannot safely use protected properties and methods. We may change or
  remove them, but will document this in the UPGRADE file.

* You can safely override public properties.

* You cannot safely override protected properties. We may change or remove them,
  but will document this in the UPGRADE file.

* You cannot safely override public or protected methods. We may change them,
  but will document this in the UPGRADE file.

* You cannot safely add public or protected properties. We may add a property
  with the same name.

* You cannot safely add a new public or protected method. We may add a method
  with the same name.

* You cannot safely add parameters to overridden methods. We may do the same.

Properties and methods tagged with ``@api`` are treated as if they belonged
to an API class.


API Classes
~~~~~~~~~~~

All classes tagged with ``@api`` are also **safe for extension**. That means
that:

* You can safely use protected properties and methods.

* You can safely override protected properties.

* You can safely override public or protected methods.


Safe Operations
~~~~~~~~~~~~~~~

The following table summarizes the safe operations when using our classes:

==============================================  ==============  ==============
Operation                                       Normal          API
==============================================  ==============  ==============
Type hint against                               Safe            Safe
Create instance                                 Safe            Safe
Extend                                          Safe            Safe
Use public property                             Safe            Safe
Use public method                               Safe            Safe
**When Extending**
Use protected property                          Not Safe        Safe
Use protected method                            Not Safe        Safe
Override public property                        Safe            Safe
Override protected property                     Not Safe        Safe
Override public method                          Not Safe        Safe
Override protected method                       Not Safe        Safe
Add custom method                               Not Safe        Not Safe
Add custom method parameter                     Not Safe        Not Safe
Add parameter default value                     Safe            Safe
==============================================  ==============  ==============


Allowed Changes
~~~~~~~~~~~~~~~

This table tells you which changes you are allowed to do when working on
Symfony code:

==================================================  ==============  ==============
Type of Change                                      Normal          API
==================================================  ==============  ==============
Remove entirely                                     No              No
Make final                                          Yes [1]_        No
Make abstract                                       No              No
Change name or namespace                            No              No
Change parent class                                 Yes [4]_        Yes [4]_
Add interface                                       Yes             Yes
Remove interface                                    No              No
**Public Properties**
Add public property                                 Yes             Yes
Remove public property                              No              No
Reduce visibility                                   No              No
**Protected Properties**
Add protected property                              Yes             Yes
Remove protected property                           Yes [1]_        No
Reduce visibility                                   Yes [1]_        No
**Constructors**
Add constructor without mandatory parameters        Yes [1]_        Yes [1]_
Remove constructor                                  Yes [1]_        No
Reduce visibility of a public constructor           No              No
Reduce visibility of a protected constructor        Yes [1]_        No
**Public Methods**
Add public method                                   Yes             Yes
Remove public method                                No              No
Change name                                         No              No
Reduce visibility                                   No              No
Add parameter without a default value               No              No
Add parameter with a default value                  Yes [1]_        No
Remove parameter                                    Yes [2]_        Yes [2]_
Add default value to a parameter                    Yes [1]_        No
Remove default value of a parameter                 No              No
Add type hint to a parameter                        Yes [5]_        No
Remove type hint of a parameter                     Yes [1]_        No
Change return type                                  Yes [1]_ [3]_   No
**Protected Methods**
Add protected method                                Yes             Yes
Remove protected method                             Yes [1]_        No
Change name                                         No              No
Reduce visibility                                   Yes [1]_        No
Add parameter without a default value               Yes [1]_        No
Add parameter with a default value                  Yes [1]_        No
Remove parameter                                    Yes [2]_        Yes [2]_
Add default value to a parameter                    Yes [1]_        No
Remove default value of a parameter                 Yes [1]_        No
Add type hint to a parameter                        Yes [1]_        No
Remove type hint of a parameter                     Yes [1]_        No
Change return type                                  Yes [1]_ [3]_   No
==================================================  ==============  ==============


.. [1] Should be avoided. When done, this change must be documented in the
       UGPRADE file.

.. [2] Only the last parameter(s) of a method may be removed.

.. [3] The return type may only be changed to compatible types. The following
       type changes are allowed:

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
       ===================  ==================================================================

.. [4] When changing the parent class, the original parent class must remain an
       ancestor of the class.

.. [5] A type hint may only be added if passing a value with a different type
       previously generated a fatal error.

.. _scalar type: http://php.net/manual/en/function.is-scalar.php

.. _boolean values: http://php.net/manual/en/function.boolval.php

.. _string values: http://www.php.net/manual/en/function.strval.php

.. _integer values: http://www.php.net/manual/en/function.intval.php

.. _float values: http://www.php.net/manual/en/function.floatval.php
