UniqueEntity
============

Validates that a particular field (or fields) in a Doctrine entity are unique.
This is commonly used, for example, to prevent a new user to register using
an email address that already exists in the system.

+----------------+-------------------------------------------------------------------------------------+
| Applies to     | :ref:`class<validation-class-target>`                                               |
+----------------+-------------------------------------------------------------------------------------+
| Options        | - `fields`_                                                                         |
|                | - `message`_                                                                        |
|                | - `em`_                                                                             |
+----------------+-------------------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Bridge\\Doctrine\\Validator\\Constraints\\UniqueEntity`            |
+----------------+-------------------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Bridge\\Doctrine\\Validator\\Constraints\\UniqueEntity\\Validator` |
+----------------+-------------------------------------------------------------------------------------+

Basic Usage
-----------

Suppose you have an ``AcmeUserBundle`` with a ``User`` entity that has an
``email`` field. You can use the ``Unique`` constraint to guarantee that the
``email`` field remains unique between all of the constrains in your user table:

.. configuration-block::

    .. code-block:: php-annotations

        // Acme/UserBundle/Entity/User.php
        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
        use Doctrine\ORM\Mapping as ORM;

        /**
         * @ORM\Entity
         * @DoctrineAssert\UniqueEntity("email")
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

    .. code-block:: yaml

        # src/Acme/UserBundle/Resources/config/validation.yml
        constraints:
            - UniqueEntity: email

Options
-------

fields
~~~~~~

**type**: ``array``|``string`` [:ref:`default option<validation-default-option>`]

This required option is the field (or list of fields) on which this entity
should be unique. For example, you could specify that both the email and
name fields in the User example above should be unique.

message
~~~~~~~

**type**: ``string`` **default**: ``This value is already used.``

The message that's displayed with this constraint fails.

em
~~

**type**: ``string``

The name of the entity manager to use for making the query to determine the
uniqueness. If left blank, the default entity manager will be used.
