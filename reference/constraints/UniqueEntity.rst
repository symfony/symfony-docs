UniqueEntity
============

Validates that a particular field (or fields) in a Doctrine entity is (are)
unique. This is commonly used, for example, to prevent a new user to register
using an email address that already exists in the system.

+----------------+-------------------------------------------------------------------------------------+
| Applies to     | :ref:`class <validation-class-target>`                                              |
+----------------+-------------------------------------------------------------------------------------+
| Options        | - `fields`_                                                                         |
|                | - `message`_                                                                        |
|                | - `em`_                                                                             |
|                | - `repositoryMethod`_                                                               |
|                | - `errorPath`_                                                                      |
|                | - `ignoreNull`_                                                                     |
+----------------+-------------------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Bridge\\Doctrine\\Validator\\Constraints\\UniqueEntity`            |
+----------------+-------------------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Bridge\\Doctrine\\Validator\\Constraints\\UniqueEntityValidator`   |
+----------------+-------------------------------------------------------------------------------------+

Basic Usage
-----------

Suppose you have an ``AcmeUserBundle`` bundle with a ``User`` entity that has an
``email`` field. You can use the ``UniqueEntity`` constraint to guarantee that
the ``email`` field remains unique between all of the constraints in your user
table:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/UserBundle/Resources/config/validation.yml
        Acme\UserBundle\Entity\Author:
            constraints:
                - Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity: email
            properties:
                email:
                    - Email: ~

    .. code-block:: php-annotations

        // Acme/UserBundle/Entity/User.php
        namespace Acme\UserBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Doctrine\ORM\Mapping as ORM;

        // DON'T forget this use statement!!!
        use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

        /**
         * @ORM\Entity
         * @UniqueEntity("email")
         */
        class Author
        {
            /**
             * @var string $email
             *
             * @ORM\Column(name="email", type="string", length=255, unique=true)
             * @Assert\Email()
             */
            protected $email;

            // ...
        }

    .. code-block:: xml

        <!-- src/Acme/AdministrationBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="Acme\UserBundle\Entity\Author">
                <constraint name="Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity">
                    <option name="fields">email</option>
                    <option name="message">This email already exists.</option>
                </constraint>
                <property name="email">
                    <constraint name="Email" />
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php


        // Acme/UserBundle/Entity/User.php
        namespace Acme\UserBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        // DON'T forget this use statement!!!
        use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

        class Author
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addConstraint(new UniqueEntity(array(
                    'fields'  => 'email',
                    'message' => 'This email already exists.',
                )));

                $metadata->addPropertyConstraint('email', new Assert\Email());
            }
        }

Options
-------

fields
~~~~~~

**type**: ``array`` | ``string`` [:ref:`default option <validation-default-option>`]

This required option is the field (or list of fields) on which this entity
should be unique. For example, if you specified both the ``email`` and ``name``
field in a single ``UniqueEntity`` constraint, then it would enforce that
the combination value where unique (e.g. two users could have the same email,
as long as they don't have the same name also).

If you need to require two fields to be individually unique (e.g. a unique
``email`` *and* a unique ``username``), you use two ``UniqueEntity`` entries,
each with a single field.

message
~~~~~~~

**type**: ``string`` **default**: ``This value is already used.``

The message that's displayed when this constraint fails.

em
~~

**type**: ``string``

The name of the entity manager to use for making the query to determine the
uniqueness. If it's left blank, the correct entity manager will be determined
for this class. For that reason, this option should probably not need to be
used.

repositoryMethod
~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``findBy``

The name of the repository method to use for making the query to determine the
uniqueness. If it's left blank, the ``findBy`` method will be used. This
method should return a countable result.

errorPath
~~~~~~~~~

**type**: ``string`` **default**: The name of the first field in `fields`_

If the entity violates the constraint the error message is bound to the first
field in `fields`_. If there is more than one field, you may want to map
the error message to another field.

Consider this example:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/AdministrationBundle/Resources/config/validation.yml
        Acme\AdministrationBundle\Entity\Service:
            constraints:
                - Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity:
                    fields: [host, port]
                    errorPath: port
                    message: 'This port is already in use on that host.'

    .. code-block:: php-annotations

        // src/Acme/AdministrationBundle/Entity/Service.php
        namespace Acme\AdministrationBundle\Entity;

        use Doctrine\ORM\Mapping as ORM;
        use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

        /**
         * @ORM\Entity
         * @UniqueEntity(
         *     fields={"host", "port"},
         *     errorPath="port",
         *     message="This port is already in use on that host."
         * )
         */
        class Service
        {
            /**
             * @ORM\ManyToOne(targetEntity="Host")
             */
            public $host;

            /**
             * @ORM\Column(type="integer")
             */
            public $port;
        }

    .. code-block:: xml

        <!-- src/Acme/AdministrationBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="Acme\AdministrationBundle\Entity\Service">
                <constraint name="Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity">
                    <option name="fields">
                        <value>host</value>
                        <value>port</value>
                    </option>
                    <option name="errorPath">port</option>
                    <option name="message">This port is already in use on that host.</option>
                </constraint>
            </class>

        </constraint-mapping>

    .. code-block:: php

        // src/Acme/AdministrationBundle/Entity/Service.php
        namespace Acme\AdministrationBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

        class Service
        {
            public $host;
            public $port;

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addConstraint(new UniqueEntity(array(
                    'fields'    => array('host', 'port'),
                    'errorPath' => 'port',
                    'message'   => 'This port is already in use on that host.',
                )));
            }
        }

Now, the message would be bound to the ``port`` field with this configuration.

ignoreNull
~~~~~~~~~~

**type**: ``Boolean`` **default**: ``true``

If this option is set to ``true``, then the constraint will allow multiple
entities to have a ``null`` value for a field without failing validation.
If set to ``false``, only one ``null`` value is allowed - if a second entity
also has a ``null`` value, validation would fail.
