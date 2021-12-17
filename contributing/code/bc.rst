Our Backward Compatibility Promise
==================================

Ensuring smooth upgrades of your projects is our first priority. That's why
we promise you backward compatibility (BC) for all minor Symfony releases.
You probably recognize this strategy as `Semantic Versioning`_. In short,
Semantic Versioning means that only major releases (such as 5.0, 6.0 etc.) are
allowed to break backward compatibility. Minor releases (such as 5.1, 5.2 etc.)
may introduce new features, but must do so without breaking the existing API of
that release branch (5.x in the previous example).

We also provide deprecation message triggered in the code base to help you with
the migration process across major releases.

.. caution::

    This promise was introduced with Symfony 2.3 and does not apply to previous
    versions of Symfony.

However, backward compatibility comes in many different flavors. In fact, almost
every change that we make to the framework can potentially break an application.
For example, if we add a new method to a class, this will break an application
which extended this class and added the same method, but with a different
method signature.

Also, not every BC break has the same impact on application code. While some BC
breaks require you to make significant changes to your classes or your
architecture, others are fixed by changing the name of a method.

That's why we created this page for you. The section "Using Symfony Code" will
tell you how you can ensure that your application won't break completely when
upgrading to a newer version of the same major release branch.

The second section, "Working on Symfony Code", is targeted at Symfony
contributors. This section lists detailed rules that every contributor needs to
follow to ensure smooth upgrades for our users.

.. caution::

    :doc:`Experimental Features </contributing/code/experimental>` and code
    marked with the ``@internal`` tags are excluded from our Backward
    Compatibility promise.

    Also note that backward compatibility breaks are tolerated if they are
    required to fix a security issue.

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

If you implement an interface, we promise that we won't ever break your code.

The following table explains in detail which use cases are covered by our
backward compatibility promise:

+-----------------------------------------------+-----------------------------+
| Use Case                                      | Backward Compatibility      |
+===============================================+=============================+
| **If you...**                                 | **Then we guarantee BC...** |
+-----------------------------------------------+-----------------------------+
| Type hint against the interface               | Yes                         |
+-----------------------------------------------+-----------------------------+
| Call a method                                 | Yes [10]_                   |
+-----------------------------------------------+-----------------------------+
| **If you implement the interface and...**     | **Then we guarantee BC...** |
+-----------------------------------------------+-----------------------------+
| Implement a method                            | Yes                         |
+-----------------------------------------------+-----------------------------+
| Add an argument to an implemented method      | Yes                         |
+-----------------------------------------------+-----------------------------+
| Add a default value to an argument            | Yes                         |
+-----------------------------------------------+-----------------------------+
| Add a return type to an implemented method    | Yes                         |
+-----------------------------------------------+-----------------------------+

Using our Classes
~~~~~~~~~~~~~~~~~

All classes provided by Symfony may be instantiated and accessed through their
public methods and properties.

.. caution::

    Classes, properties and methods that bear the tag ``@internal`` as well as
    the classes located in the various ``*\Tests\`` namespaces are an
    exception to this rule. They are meant for internal use only and should
    not be accessed by your own code.

To be on the safe side, check the following table to know which use cases are
covered by our backward compatibility promise:

+-----------------------------------------------+-----------------------------+
| Use Case                                      | Backward Compatibility      |
+===============================================+=============================+
| **If you...**                                 | **Then we guarantee BC...** |
+-----------------------------------------------+-----------------------------+
| Type hint against the class                   | Yes                         |
+-----------------------------------------------+-----------------------------+
| Create a new instance                         | Yes                         |
+-----------------------------------------------+-----------------------------+
| Extend the class                              | Yes                         |
+-----------------------------------------------+-----------------------------+
| Access a public property                      | Yes                         |
+-----------------------------------------------+-----------------------------+
| Call a public method                          | Yes [10]_                   |
+-----------------------------------------------+-----------------------------+
| **If you extend the class and...**            | **Then we guarantee BC...** |
+-----------------------------------------------+-----------------------------+
| Access a protected property                   | Yes                         |
+-----------------------------------------------+-----------------------------+
| Call a protected method                       | Yes [10]_                   |
+-----------------------------------------------+-----------------------------+
| Override a public property                    | Yes                         |
+-----------------------------------------------+-----------------------------+
| Override a protected property                 | Yes                         |
+-----------------------------------------------+-----------------------------+
| Override a public method                      | Yes                         |
+-----------------------------------------------+-----------------------------+
| Override a protected method                   | Yes                         |
+-----------------------------------------------+-----------------------------+
| Add a new property                            | No                          |
+-----------------------------------------------+-----------------------------+
| Add a new method                              | No                          |
+-----------------------------------------------+-----------------------------+
| Add an argument to an overridden method       | Yes                         |
+-----------------------------------------------+-----------------------------+
| Add a default value to an argument            | Yes                         |
+-----------------------------------------------+-----------------------------+
| Call a private method (via Reflection)        | No                          |
+-----------------------------------------------+-----------------------------+
| Access a private property (via Reflection)    | No                          |
+-----------------------------------------------+-----------------------------+

Using our Traits
~~~~~~~~~~~~~~~~

All traits provided by Symfony may be used in your classes.

.. caution::

    The exception to this rule are traits tagged with ``@internal``. Such
    traits should not be used.

To be on the safe side, check the following table to know which use cases are
covered by our backward compatibility promise:

+-----------------------------------------------+-----------------------------+
| Use Case                                      | Backward Compatibility      |
+===============================================+=============================+
| **If you...**                                 | **Then we guarantee BC...** |
+-----------------------------------------------+-----------------------------+
| Use a trait                                   | Yes                         |
+-----------------------------------------------+-----------------------------+
| **If you use the trait and...**               | **Then we guarantee BC...** |
+-----------------------------------------------+-----------------------------+
| Use it to implement an interface              | Yes                         |
+-----------------------------------------------+-----------------------------+
| Use it to implement an abstract method        | Yes                         |
+-----------------------------------------------+-----------------------------+
| Use it to extend a parent class               | Yes                         |
+-----------------------------------------------+-----------------------------+
| Use it to define an abstract class            | Yes                         |
+-----------------------------------------------+-----------------------------+
| Use a public, protected or private property   | Yes                         |
+-----------------------------------------------+-----------------------------+
| Use a public, protected or private method     | Yes                         |
+-----------------------------------------------+-----------------------------+

Working on Symfony Code
-----------------------

Do you want to help us improve Symfony? That's great! However, please stick
to the rules listed below in order to ensure smooth upgrades for our users.

Changing Interfaces
~~~~~~~~~~~~~~~~~~~

This table tells you which changes you are allowed to do when working on
Symfony's interfaces:

==============================================  ==============
Type of Change                                  Change Allowed
==============================================  ==============
Remove entirely                                 No
Change name or namespace                        No
Add parent interface                            Yes [2]_
Remove parent interface                         No
**Methods**
Add method                                      No
Remove method                                   No
Change name                                     No
Move to parent interface                        Yes
Add argument without a default value            No
Add argument with a default value               No
Remove argument                                 No [3]_
Add default value to an argument                No
Remove default value of an argument             No
Add type hint to an argument                    No
Remove type hint of an argument                 No
Change argument type                            No
Add return type                                 No
Remove return type                              No [9]_
Change return type                              No
**Static Methods**
Turn non static into static                     No
Turn static into non static                     No
**Constants**
Add constant                                    Yes
Remove constant                                 No
Change value of a constant                      Yes [1]_ [5]_
==============================================  ==============

Changing Classes
~~~~~~~~~~~~~~~~

This table tells you which changes you are allowed to do when working on
Symfony's classes:

==================================================  ==============
Type of Change                                      Change Allowed
==================================================  ==============
Remove entirely                                     No
Make final                                          No [6]_
Make abstract                                       No
Change name or namespace                            No
Change parent class                                 Yes [4]_
Add interface                                       Yes
Remove interface                                    No
**Public Properties**
Add public property                                 Yes
Remove public property                              No
Reduce visibility                                   No
Move to parent class                                Yes
**Protected Properties**
Add protected property                              Yes
Remove protected property                           No [7]_
Reduce visibility                                   No [7]_
Make public                                         No [7]_
Move to parent class                                Yes
**Private Properties**
Add private property                                Yes
Make public or protected                            Yes
Remove private property                             Yes
**Constructors**
Add constructor without mandatory arguments         Yes [1]_
Remove constructor                                  No
Reduce visibility of a public constructor           No
Reduce visibility of a protected constructor        No [7]_
Move to parent class                                Yes
**Destructors**
Add destructor                                      Yes
Remove destructor                                   No
Move to parent class                                Yes
**Public Methods**
Add public method                                   Yes
Remove public method                                No
Change name                                         No
Reduce visibility                                   No
Make final                                          No [6]_
Move to parent class                                Yes
Add argument without a default value                No
Add argument with a default value                   No [7]_ [8]_
Remove argument                                     No [3]_
Add default value to an argument                    No [7]_ [8]_
Remove default value of an argument                 No
Add type hint to an argument                        No [7]_ [8]_
Remove type hint of an argument                     No [7]_ [8]_
Change argument type                                No [7]_ [8]_
Add return type                                     No [7]_ [8]_
Remove return type                                  No [7]_ [8]_ [9]_
Change return type                                  No [7]_ [8]_
**Protected Methods**
Add protected method                                Yes
Remove protected method                             No [7]_
Change name                                         No [7]_
Reduce visibility                                   No [7]_
Make final                                          No [6]_
Make public                                         No [7]_ [8]_
Move to parent class                                Yes
Add argument without a default value                No [7]_
Add argument with a default value                   No [7]_ [8]_
Remove argument                                     No [3]_
Add default value to an argument                    No [7]_ [8]_
Remove default value of an argument                 No [7]_
Add type hint to an argument                        No [7]_ [8]_
Remove type hint of an argument                     No [7]_ [8]_
Change argument type                                No [7]_ [8]_
Add return type                                     No [7]_ [8]_
Remove return type                                  No [7]_ [8]_ [9]_
Change return type                                  No [7]_ [8]_
**Private Methods**
Add private method                                  Yes
Remove private method                               Yes
Change name                                         Yes
Make public or protected                            Yes
Add argument without a default value                Yes
Add argument with a default value                   Yes
Remove argument                                     Yes
Add default value to an argument                    Yes
Remove default value of an argument                 Yes
Add type hint to an argument                        Yes
Remove type hint of an argument                     Yes
Change argument type                                Yes
Add return type                                     Yes
Remove return type                                  Yes
Change return type                                  Yes
**Static Methods and Properties**
Turn non static into static                         No [7]_ [8]_
Turn static into non static                         No
**Constants**
Add constant                                        Yes
Remove constant                                     No
Change value of a constant                          Yes [1]_ [5]_
==================================================  ==============

Changing Traits
~~~~~~~~~~~~~~~

This table tells you which changes you are allowed to do when working on
Symfony's traits:

==================================================  ==============
Type of Change                                      Change Allowed
==================================================  ==============
Remove entirely                                     No
Change name or namespace                            No
Use another trait                                   Yes
**Public Properties**
Add public property                                 Yes
Remove public property                              No
Reduce visibility                                   No
Move to a used trait                                Yes
**Protected Properties**
Add protected property                              Yes
Remove protected property                           No
Reduce visibility                                   No
Make public                                         No
Move to a used trait                                Yes
**Private Properties**
Add private property                                Yes
Remove private property                             No
Make public or protected                            Yes
Move to a used trait                                Yes
**Constructors and destructors**
Have constructor or destructor                      No
**Public Methods**
Add public method                                   Yes
Remove public method                                No
Change name                                         No
Reduce visibility                                   No
Make final                                          No [6]_
Move to used trait                                  Yes
Add argument without a default value                No
Add argument with a default value                   No
Remove argument                                     No
Add default value to an argument                    No
Remove default value of an argument                 No
Add type hint to an argument                        No
Remove type hint of an argument                     No
Change argument type                                No
Change return type                                  No
**Protected Methods**
Add protected method                                Yes
Remove protected method                             No
Change name                                         No
Reduce visibility                                   No
Make final                                          No [6]_
Make public                                         No [8]_
Move to used trait                                  Yes
Add argument without a default value                No
Add argument with a default value                   No
Remove argument                                     No
Add default value to an argument                    No
Remove default value of an argument                 No
Add type hint to an argument                        No
Remove type hint of an argument                     No
Change argument type                                No
Change return type                                  No
**Private Methods**
Add private method                                  Yes
Remove private method                               No
Change name                                         No
Make public or protected                            Yes
Move to used trait                                  Yes
Add argument without a default value                No
Add argument with a default value                   No
Remove argument                                     No
Add default value to an argument                    No
Remove default value of an argument                 No
Add type hint to an argument                        No
Remove type hint of an argument                     No
Change argument type                                No
Add return type                                     No
Remove return type                                  No
Change return type                                  No
**Static Methods and Properties**
Turn non static into static                         No
Turn static into non static                         No
==================================================  ==============

.. [1] Should be avoided. When done, this change must be documented in the
       UPGRADE file.

.. [2] The added parent interface must not introduce any new methods that don't
       exist in the interface already.

.. [3] Only the last optional argument(s) of a method may be removed, as PHP
       does not care about additional arguments that you pass to a method.

.. [4] When changing the parent class, the original parent class must remain an
       ancestor of the class.

.. [5] The value of a constant may only be changed when the constants aren't
       used in configuration (e.g. Yaml and XML files), as these do not support
       constants and have to hardcode the value. For instance, event name
       constants can't change the value without introducing a BC break.
       Additionally, if a constant will likely be used in objects that are
       serialized, the value of a constant should not be changed.

.. [6] Allowed using the ``@final`` annotation.

.. [7] Allowed if the class is final. Classes that received the ``@final``
       annotation after their first release are considered final in their
       next major version.
       Changing an argument type is only possible with a parent type.
       Changing a return type is only possible with a child type.

.. [8] Allowed if the method is final. Methods that received the ``@final``
       annotation after their first release are considered final in their
       next major version.
       Changing an argument type is only possible with a parent type.
       Changing a return type is only possible with a child type.

.. [9] Allowed for the ``void`` return type.

.. [10] Parameter names are only covered by the compatibility promise for
        constructors of Attribute classes. Using PHP named arguments might
        break your code when upgrading to newer Symfony versions.

.. _`Semantic Versioning`: https://semver.org/
