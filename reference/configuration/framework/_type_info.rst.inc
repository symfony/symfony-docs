type_info
~~~~~~~~~

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

Enables the TypeInfo component integration in the framework.

aliases
.......

**type**: ``array`` **default**: ``[]``

Defines global type aliases for the TypeInfo component. The keys are the alias
names and the values are the type definitions (using PHPStan/Psalm syntax):

.. code-block:: yaml

    framework:
        type_info:
            aliases:
                MoneyAmount: int
                UserData: 'array{name: string, email: string, age: int}'

See the :doc:`TypeInfo component documentation </components/type_info>` for more
details about type aliases.
