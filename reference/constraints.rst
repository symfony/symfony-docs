Validation Constraints Reference
================================

.. toctree::
   :maxdepth: 1
   :hidden:

   constraints/False
   constraints/True
   constraints/Type
   constraints/Callback
   constraints/Choice
   constraints/Collection
   constraints/Date
   constraints/DateTime
   constraints/Email
   constraints/File
   constraints/Max
   constraints/MaxLength
   constraints/Min
   constraints/MinLength
   constraints/NotBlank
   constraints/NotNull
   constraints/Blank
   constraints/Null
   constraints/UniqueEntity
   constraints/Regex
   constraints/Time
   constraints/Url
   constraints/Valid

The Validator is designed to validate objects against *constraints*.
In real life, a constraint could be: "The cake must not be burned". In
Symfony2, constraints are similar: They are assertions that a condition is 
true.

Supported Constraints
---------------------

The following constraints are natively available in Symfony2:

Basic Constraints
~~~~~~~~~~~~~~~~~

These are the basic constraints: use them to assert very basic things about
the value of properties or the return value of methods on your object.

* :doc:`NotBlank <constraints/NotBlank>`
* :doc:`Blank <constraints/Blank>`
* :doc:`NotNull <constraints/NotNull>`
* :doc:`Null <constraints/Null>`
* :doc:`True <constraints/True>`
* :doc:`False <constraints/False>`
* :doc:`Type <constraints/Type>`

String Constraints
~~~~~~~~~~~~~~~~~~

* :doc:`Email <constraints/Email>`
* :doc:`MinLength <constraints/MinLength>`
* :doc:`MaxLength <constraints/MaxLength>`
* :doc:`Url <constraints/Url>`
* :doc:`Regex <constraints/Regex>`
* :doc:`Ip <constraints/Ip>`

Number Constraints
~~~~~~~~~~~~~~~~~~

* :doc:`Max <constraints/Max>`
* :doc:`Min <constraints/Min>`

Date Constraints
~~~~~~~~~~~~~~~~

* :doc:`Date <constraints/Date>`
* :doc:`DateTime <constraints/DateTime>`
* :doc:`Time <constraints/Time>`

Collection Constraints
~~~~~~~~~~~~~~~~~~~~~~

* :doc:`Choice <constraints/Choice>`
* :doc:`Collection <constraints/Collection>`
* :doc:`UniqueEntity <constraints/UniqueEntity>`
* :doc:`Language <constraints/Language>`
* :doc:`Locale <constraints/Locale>`
* :doc:`Country <constraints/Country>`

File Constraints
~~~~~~~~~~~~~~~~

* :doc:`File <constraints/File>`
* :doc:`Image <constraints/Image>`

Other Constraints
~~~~~~~~~~~~~~~~~

* :doc:`Callback <constraints/Callback>`
* :doc:`All <constraints/All>`
* :doc:`Valid <constraints/Valid>`