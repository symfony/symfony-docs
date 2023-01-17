Traverse
========

Object properties are only validated if they are accessible, either by being
public or having public accessor methods (e.g. a public getter).
If your object needs to be traversed to validate its data, you can use this
constraint.

==========  ===================================================================
Applies to  :ref:`class <validation-class-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Traverse`
==========  ===================================================================

Basic Usage
-----------

In the following example, create two classes ``BookCollection`` and ``Book``
that all have constraints on their properties.

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/BookCollection.php
        namespace App\Entity;

        use Doctrine\Common\Collections\ArrayCollection;
        use Doctrine\Common\Collections\Collection;
        use Doctrine\ORM\Mapping as ORM;
        use Symfony\Component\Validator\Constraints as Assert;

        /**
         * @ORM\Entity
         * @Assert\Traverse
         */
        class BookCollection implements \IteratorAggregate
        {
            /**
             * @var string
             *
             * @ORM\Column
             *
             * @Assert\NotBlank
             */
            protected $name = '';

            /**
             * @var Collection|Book[]
             *
             * @ORM\ManyToMany(targetEntity="App\Entity\Book")
             */
            protected $books;

            // some other properties

            public function __construct()
            {
                $this->books = new ArrayCollection();
            }

            // ... setter for name, adder and remover for books

            // the name can be validated by calling the getter
            public function getName(): string
            {
                return $this->name;
            }

            /**
             * @return \Generator|Book[] The books for a given author
             */
            public function getBooksForAuthor(Author $author): iterable
            {
                foreach ($this->books as $book) {
                    if ($book->isAuthoredBy($author)) {
                        yield $book;
                    }
                }
            }

            // neither the method above nor any other specific getter
            // could be used to validated all nested books;
            // this object needs to be traversed to call the iterator
            public function getIterator()
            {
                return $this->books->getIterator();
            }
        }

    .. code-block:: php-attributes

        // src/Entity/BookCollection.php
        namespace App\Entity;

        use App\Entity\Book;
        use Doctrine\Common\Collections\ArrayCollection;
        use Doctrine\Common\Collections\Collection;
        use Doctrine\ORM\Mapping as ORM;
        use Symfony\Component\Validator\Constraints as Assert;

        #[ORM\Entity]
        #[Assert\Traverse]
        class BookCollection implements \IteratorAggregate
        {
            /**
             * @var string
             */
            #[ORM\Column]
            #[Assert\NotBlank]
            protected $name = '';

            /**
             * @var Collection|Book[]
             */
            #[ORM\ManyToMany(targetEntity: Book::class)] 
            protected $books;

            // some other properties

            public function __construct()
            {
                $this->books = new ArrayCollection();
            }

            // ... setter for name, adder and remover for books

            // the name can be validated by calling the getter
            public function getName(): string
            {
                return $this->name;
            }

            /**
             * @return \Generator|Book[] The books for a given author
             */
            public function getBooksForAuthor(Author $author): iterable
            {
                foreach ($this->books as $book) {
                    if ($book->isAuthoredBy($author)) {
                        yield $book;
                    }
                }
            }

            // neither the method above nor any other specific getter
            // could be used to validated all nested books;
            // this object needs to be traversed to call the iterator
            public function getIterator()
            {
                return $this->books->getIterator();
            }
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\BookCollection:
            constraints:
                - Traverse: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\BookCollection">
                <constraint name="Traverse"/>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/BookCollection.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class BookCollection
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addConstraint(new Assert\Traverse());
            }
        }

When the object implements ``\Traversable`` (like here with its child
``\IteratorAggregate``), its traversal strategy will implicitly be set and the
object will be iterated over without defining the constraint.
It is mostly useful to add it to be explicit or to disable the traversal using
the ``traverse`` option.
If a public getter exists to return the inner books collection like
``getBooks(): Collection``, the :doc:`/reference/constraints/Valid` constraint
can be used on the ``$books`` property instead.

Options
-------

The ``groups`` option is not available for this constraint.

.. _traverse-option:

``traverse``
~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

Instances of ``\Traversable`` are traversed by default, use this option to
disable validating:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/BookCollection.php

        // ... same as above

        /**
         * ...
         * @Assert\Traverse(false)
         */
         class BookCollection implements \IteratorAggregate
         {
             // ...
         }

    .. code-block:: php-attributes

        // src/Entity/BookCollection.php

        // ... same as above

        /**
         * ...
         */
         #[Assert\Traverse(false)]
         class BookCollection implements \IteratorAggregate
         {
             // ...
         }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\BookCollection:
            constraints:
                - Traverse: false

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\BookCollection">
                <constraint name="Traverse">false</constraint>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/BookCollection.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class BookCollection
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addConstraint(new Assert\Traverse(false));
            }
        }

.. include:: /reference/constraints/_payload-option.rst.inc
