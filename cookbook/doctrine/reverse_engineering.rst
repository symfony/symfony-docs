.. index::
   single: Doctrine; Generating entities from existing database

How to generate Entities from an Existing Database
==================================================

When starting work on a brand new project that uses a database, two different
situations comes naturally. In most cases, the database model is designed
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
correctly setup in the ``app/config/parameters.ini`` file (or wherever your
database configuration is kept) and that you have initialized a bundle that
will host your future entity class. In this tutorial, we will assume that
an ``AcmeBlogBundle`` exists and is located under the ``src/Acme/BlogBundle``
folder.

The first step towards building entity classes from an existing database
is to ask Doctrine to introspect the database and generate the corresponding
metadata files. Metadata files describe the entity class to generate based on
tables fields.

.. code-block:: bash

    $ php app/console doctrine:mapping:convert xml ./src/Acme/BlogBundle/Resources/config/doctrine/metadata/orm --from-database --force

This command line tool asks Doctrine to introspect the database and generate
the XML metadata files under the ``src/Acme/BlogBundle/Resources/config/doctrine/metadata/orm``
folder of your bundle.

.. tip::

    It's also possible to generate metadata class in YAML format by changing the
    first argument to `yml`.

The generated ``BlogPost.dcm.xml`` metadata file looks as follows:

.. code-block:: xml

    <?xml version="1.0" encoding="utf-8"?>
    <doctrine-mapping>
      <entity name="BlogPost" table="blog_post">
        <change-tracking-policy>DEFERRED_IMPLICIT</change-tracking-policy>
        <id name="id" type="bigint" column="id">
          <generator strategy="IDENTITY"/>
        </id>
        <field name="title" type="string" column="title" length="100"/>
        <field name="content" type="text" column="content"/>
        <field name="isPublished" type="boolean" column="is_published"/>
        <field name="createdAt" type="datetime" column="created_at"/>
        <field name="updatedAt" type="datetime" column="updated_at"/>
        <field name="slug" type="string" column="slug" length="255"/>
        <lifecycle-callbacks/>
      </entity>
    </doctrine-mapping>

Once the metadata files are generated, you can ask Doctrine to import the
schema and build related entity classes by executing the following two commands.

.. code-block:: bash

    $ php app/console doctrine:mapping:import AcmeBlogBundle annotation
    $ php app/console doctrine:generate:entities AcmeBlogBundle

The first command generates entity classes with an annotations mapping, but
you can of course change the ``annotation`` argument to ``xml`` or ``yml``.
The newly created ``BlogComment`` entity class looks as follow:

.. code-block:: php

    <?php

    // src/Acme/BlogBundle/Entity/BlogComment.php
    namespace Acme\BlogBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;

    /**
     * Acme\BlogBundle\Entity\BlogComment
     *
     * @ORM\Table(name="blog_comment")
     * @ORM\Entity
     */
    class BlogComment
    {
        /**
         * @var bigint $id
         *
         * @ORM\Column(name="id", type="bigint", nullable=false)
         * @ORM\Id
         * @ORM\GeneratedValue(strategy="IDENTITY")
         */
        private $id;

        /**
         * @var string $author
         *
         * @ORM\Column(name="author", type="string", length=100, nullable=false)
         */
        private $author;

        /**
         * @var text $content
         *
         * @ORM\Column(name="content", type="text", nullable=false)
         */
        private $content;

        /**
         * @var datetime $createdAt
         *
         * @ORM\Column(name="created_at", type="datetime", nullable=false)
         */
        private $createdAt;

        /**
         * @var BlogPost
         *
         * @ORM\ManyToOne(targetEntity="BlogPost")
         * @ORM\JoinColumn(name="post_id", referencedColumnName="id")
         */
        private $post;
    }

As you can see, Doctrine converts all table fields to pure private and annotated
class properties. The most impressive thing is that it also discovered the
relationship with the ``BlogPost`` entity class based on the foreign key constraint.
Consequently, you can find a private ``$post`` property mapped with a ``BlogPost``
entity in the ``BlogComment`` entity class.

The last command generated all getters and setters for your two ``BlogPost`` and
``BlogComment`` entity class properties. The generated entities are now ready to be
used. Have fun!

.. _`Doctrine tools documentation`: http://docs.doctrine-project.org/projects/doctrine-orm/en/2.1/reference/tools.html#reverse-engineering
