UniqueEntity
============

Validates that a particular field (or fields) in a Doctrine entity are unique.
For example, suppose you have an ``AcmeUserBundle`` with a ``User`` entity
that has an ``email`` field. You can use the ``Unique`` constraint to guarantee
that the ``email`` field remains unique between all of the constrains in your
user table:

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

* ``fields``: The field (or list of fields) on which this entity should be
  unique. For example, 
