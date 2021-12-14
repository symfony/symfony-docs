.. index::
   single: Doctrine; Generating entities from existing database

How to Generate Entities from an Existing Database
==================================================

When starting work on a brand new project that uses a database, two different
situations can occur. In most cases, the database model is designed
and built from scratch. Sometimes, however, you'll start with an existing and
probably unchangeable database model. Fortunately, Doctrine comes with a bunch
of tools to help generate model classes from your existing database.

.. note::

    As the `Doctrine tools documentation`_ says, reverse engineering is a
    one-time process to get started on a project. Doctrine is able to convert
    approximately 70-80% of the necessary mapping information based on fields,
    indexes and foreign key constraints. Doctrine can't discover inverse
    associations, inheritance types, entities with foreign keys as primary keys
    or semantical operations on associations such as cascade or lifecycle
    events. Some additional work on the generated entities will be necessary
    afterwards to design each to fit your domain model specificities.

This tutorial assumes you're using a simple blog application with the following
two tables: ``blog_post`` and ``blog_comment``. A comment record is linked
to a post record thanks to a foreign key constraint.

.. code-block:: sql

    CREATE TABLE `blog_post` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
      `content` longtext COLLATE utf8_unicode_ci NOT NULL,
      `created_at` datetime NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `blog_comment` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `post_id` bigint(20) NOT NULL,
      `author` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
      `content` longtext COLLATE utf8_unicode_ci NOT NULL,
      `created_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `blog_comment_post_id_idx` (`post_id`),
      CONSTRAINT `blog_post_id` FOREIGN KEY (`post_id`) REFERENCES `blog_post` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

Before diving into the recipe, be sure your database connection parameters are
correctly set up in the ``.env`` file (or ``.env.local`` override file).

The first step towards building entity classes from an existing database
is to ask Doctrine to introspect the database and generate the corresponding
metadata files. Metadata files describe the entity class to generate based on
table fields.

.. code-block:: terminal

    $ php bin/console doctrine:mapping:import "App\Entity" annotation --path=src/Entity

This command line tool asks Doctrine to introspect the database and generate
new PHP classes with annotation metadata into ``src/Entity``. This generates two
files: ``BlogPost.php`` and ``BlogComment.php``.

.. tip::

    It's also possible to generate the metadata files into XML or eventually into YAML:

    .. code-block:: terminal

        $ php bin/console doctrine:mapping:import "App\Entity" xml --path=config/doctrine

    In this case, make sure to adapt your mapping configuration accordingly:

    .. code-block:: yaml

        # config/packages/doctrine.yaml
        doctrine:
            # ...
            orm:
                # ...
                mappings:
                    App:
                        is_bundle: false
                        type: xml # "yml" is marked as deprecated for doctrine v2.6+ and will be removed in v3
                        dir: '%kernel.project_dir%/config/doctrine'
                        prefix: 'App\Entity'
                        alias: App

Generating the Getters & Setters or PHP Classes
-----------------------------------------------

The generated PHP classes now have properties and annotation metadata, but they
do *not* have any getter or setter methods. If you generated XML or YAML metadata,
you don't even have the PHP classes!

To generate the missing getter/setter methods (or to *create* the classes if necessary),
run:

.. code-block:: terminal

    // generates getter/setter methods for all Entities
    $ php bin/console make:entity --regenerate App

    // generates getter/setter methods for one specific Entity
    $ php bin/console make:entity --regenerate App\Entity\Country

.. note::

    If you want to have a OneToMany relationship, you will need to add
    it manually into the entity (e.g. add a ``comments`` property to ``BlogPost``)
    or to the generated XML or YAML files. Add a section on the specific entities
    for one-to-many defining the ``inversedBy`` and the ``mappedBy`` pieces.

The generated entities are now ready to be used. Have fun!

.. _`Doctrine tools documentation`: https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/tools.html#reverse-engineering
