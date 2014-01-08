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


Allowed Changes
~~~~~~~~~~~~~~~

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
Remove parameter                                No              No
Add default value to a parameter                Yes [1]_        No
Remove default value of a parameter             No              No
Add type hint to a parameter                    No              No
Remove type hint of a parameter                 Yes [1]_        No
Change return type                              Yes [1]_ [2]_   No
==============================================  ==============  ==============


Classes
-------

Normal Classes
~~~~~~~~~~~~~~

All classes in the ``Symfony`` namespace are **safe for use**. That means that:

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


Allowed Changes
~~~~~~~~~~~~~~~

==================================================  ==============  ==============
Type of Change                                      Normal          API
==================================================  ==============  ==============
Remove entirely                                     No              No
Make final                                          Yes [1]_        No
Make abstract                                       No              No
Change name or namespace                            No              No
Change parent class                                 Yes [3]_        Yes [3]_
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
Add parameter with a default value                  Yes             Yes
Remove parameter                                    No              No
Add default value to a parameter                    Yes [1]_        No
Remove default value of a parameter                 No              No
Add type hint to a parameter                        Yes [4]_        No
Remove type hint of a parameter                     Yes [1]_        No
Change return type                                  Yes [1]_ [2]_   No
**Protected Methods**
Add protected method                                Yes             Yes
Remove protected method                             Yes [1]_        No
Change name                                         No              No
Reduce visibility                                   Yes [1]_        No
Add parameter without a default value               Yes [1]_        No
Add parameter with a default value                  Yes             Yes
Remove parameter                                    Yes [1]_        No
Add default value to a parameter                    Yes [1]_        No
Remove default value of a parameter                 Yes [1]_        No
Add type hint to a parameter                        Yes [1]_        No
Remove type hint of a parameter                     Yes [1]_        No
Change return type                                  Yes [1]_ [2]_   No
==================================================  ==============  ==============


.. [1] Should be avoided. When done, this change must be documented in the
       UGPRADE file.

.. [2] The return type may only be changed to compatible types. The following
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

.. [3] When changing the parent class, the original parent class must remain an
       ancestor of the class.

.. [4] A type hint may only be added if passing a value with a different type
       previously generated a fatal error.

.. _scalar type: http://php.net/manual/en/function.is-scalar.php

.. _boolean values: http://php.net/manual/en/function.boolval.php

.. _string values: http://www.php.net/manual/en/function.strval.php

.. _integer values: http://www.php.net/manual/en/function.intval.php

.. _float values: http://www.php.net/manual/en/function.floatval.php
