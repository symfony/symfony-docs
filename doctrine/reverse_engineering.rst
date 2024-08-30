How to Generate Entities from an Existing Database
==================================================

.. caution::

    The ``doctrine:mapping:import`` command used to generate Doctrine entities
    from existing databases was deprecated by Doctrine in 2019 and there's no
    replacement for it.

    Instead, you can use the ``make:entity`` command from `Symfony Maker Bundle`_
    to help you generate the code of your Doctrine entities. This command
    requires manual supervision because it doesn't generate entities from
    existing databases.

When starting a new project that involves using a database, there are naturally two different scenarios. In most cases, the database model is designed and built from scratch. However, there are times when you start with an existing database model that may not be modifiable. In Symfony 7.0, you can use Attributes to describe the database information, and the `doctrine-helper`_ provides tools to help generate model classes from an existing database.

The generated entities are now ready to be used. Have fun!

.. _`doctrine-helper`: https://github.com/siburuxue/doctrine-helper
.. _`Symfony Maker Bundle`: https://symfony.com/bundles/SymfonyMakerBundle/current/index.html
