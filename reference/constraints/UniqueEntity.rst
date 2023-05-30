UniqueEntity
============

Validates that a particular field (or fields) in a Doctrine entity is (are)
unique. This is commonly used, for example, to prevent a new user to register
using an email address that already exists in the system.

.. seealso::

    If you want to validate that all the elements of the collection are unique
    use the :doc:`Unique constraint </reference/constraints/Unique>`.

.. note::

    In order to use this constraint, you should have installed the
    symfony/doctrine-bridge with Composer.

==========  ===================================================================
Applies to  :ref:`class <validation-class-target>`
Class       :class:`Symfony\\Bridge\\Doctrine\\Validator\\Constraints\\UniqueEntity`
Validator   :class:`Symfony\\Bridge\\Doctrine\\Validator\\Constraints\\UniqueEntityValidator`
==========  ===================================================================

Basic Usage
-----------

Suppose you have a ``User`` entity that has an ``email`` field. You can use the
``UniqueEntity`` constraint to guarantee that the ``email`` field remains unique
between all of the rows in your user table:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/User.php
        namespace App\Entity;

        use Doctrine\ORM\Mapping as ORM;

        // DON'T forget the following use statement!!!
        use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

        use Symfony\Component\Validator\Constraints as Assert;

        #[ORM\Entity]
        #[UniqueEntity('email')]
        class User
        {
            #[ORM\Column(name: 'email', type: 'string', length: 255, unique: true)]
            #[Assert\Email]
            protected $email;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\User:
            constraints:
                - Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity: email
            properties:
                email:
                    - Email: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\User">
                <constraint name="Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity">
                    <option name="fields">email</option>
                </constraint>
                <property name="email">
                    <constraint name="Email"/>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/User.php
        namespace App\Entity;

        // DON'T forget the following use statement!!!
        use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

        use Symfony\Component\Validator\Constraints as Assert;

        class User
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addConstraint(new UniqueEntity([
                    'fields' => 'email',
                ]));

                $metadata->addPropertyConstraint('email', new Assert\Email());
            }
        }

.. caution::

    This constraint doesn't provide any protection against `race conditions`_.
    They may occur when another entity is persisted by an external process after
    this validation has passed and before this entity is actually persisted in
    the database.

.. caution::

    This constraint cannot deal with duplicates found in a collection of items
    that haven't been persisted as entities yet. You'll need to create your own
    validator to handle that case.

Options
-------

em
~~

**type**: ``string`` **default**: ``null``

The name of the entity manager to use for making the query to determine
the uniqueness. If it's left blank, the correct entity manager will be
determined for this class. For that reason, this option should probably
not need to be used.

``entityClass``
~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``null``

By default, the query performed to ensure the uniqueness uses the repository of
the current class instance. However, in some cases, such as when using Doctrine
inheritance mapping, you need to execute the query in a different repository.
Use this option to define the fully-qualified class name (FQCN) of the Doctrine
entity associated with the repository you want to use.

``errorPath``
~~~~~~~~~~~~~

**type**: ``string`` **default**: The name of the first field in `fields`_

If the entity violates the constraint the error message is bound to the
first field in `fields`_. If there is more than one field, you may want
to map the error message to another field.

Consider this example:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Service.php
        namespace App\Entity;

        use App\Entity\Host;
        use Doctrine\ORM\Mapping as ORM;
        use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

        #[ORM\Entity]
        #[UniqueEntity(
            fields: ['host', 'port'],
            errorPath: 'port',
            message: 'This port is already in use on that host.',
        )]
        class Service
        {
            #[ORM\ManyToOne(targetEntity: Host::class)]
            public Host $host;

            #[ORM\Column(type: 'integer')]
            public int $port;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Service:
            constraints:
                - Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity:
                    fields: [host, port]
                    errorPath: port
                    message: 'This port is already in use on that host.'

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Service">
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

        // src/Entity/Service.php
        namespace App\Entity;

        use App\Entity\Host;
        use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Service
        {
            public Host $host;
            public int $port;

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addConstraint(new UniqueEntity([
                    'fields' => ['host', 'port'],
                    'errorPath' => 'port',
                    'message' => 'This port is already in use on that host.',
                ]));
            }
        }

Now, the message would be bound to the ``port`` field with this configuration.

``fields``
~~~~~~~~~~

**type**: ``array`` | ``string`` [:ref:`default option <validation-default-option>`]

This required option is the field (or list of fields) on which this entity
should be unique. For example, if you specified both the ``email`` and ``name``
field in a single ``UniqueEntity`` constraint, then it would enforce that
the combination value is unique (e.g. two users could have the same email,
as long as they don't have the same name also).

If you need to require two fields to be individually unique (e.g. a unique
``email`` *and* a unique ``username``), you use two ``UniqueEntity`` entries,
each with a single field.

.. include:: /reference/constraints/_groups-option.rst.inc

``ignoreNull``
~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

If this option is set to ``true``, then the constraint will allow multiple
entities to have a ``null`` value for a field without failing validation.
If set to ``false``, only one ``null`` value is allowed - if a second entity
also has a ``null`` value, validation would fail.

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is already used.``

The message that's displayed when this constraint fails. This message is by default
mapped to the first field causing the violation. When using multiple fields
in the constraint, the mapping can be specified via the `errorPath`_ property.

Messages can include the ``{{ value }}`` placeholder to display a string
representation of the invalid entity. If the entity doesn't define the
``__toString()`` method, the following generic value will be used: *"Object of
class __CLASS__ identified by <comma separated IDs>"*

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
``{{ label }}``  Corresponding form field label
===============  ==============================================================

.. include:: /reference/constraints/_payload-option.rst.inc

``repositoryMethod``
~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``findBy``

The name of the repository method used to determine the uniqueness. If it's left
blank, ``findBy()`` will be used. The method receives as its argument a
``fieldName => value`` associative array (where ``fieldName`` is each of the
fields configured in the ``fields`` option). The method should return a
:phpfunction:`countable PHP variable <is_countable>`.

.. _`race conditions`: https://en.wikipedia.org/wiki/Race_condition
