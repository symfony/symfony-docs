.. index::
    single: Doctrine; Associations

How to Work with Doctrine Associations / Relations
==================================================

.. admonition:: Screencast
    :class: screencast

    Do you prefer video tutorials? Check out the `Mastering Doctrine Relations`_
    screencast series.

There are **two** main relationship/association types:

``ManyToOne`` / ``OneToMany``
    The most common relationship, mapped in the database with a foreign
    key column (e.g. a ``category_id`` column on the ``product`` table). This is
    actually just *one* association type, but seen from the two different *sides*
    of the relation.

``ManyToMany``
    Uses a join table and is needed when both sides of the relationship can have
    many of the other side (e.g. "students" and "classes": each student is in many
    classes, and each class has many students).

First, you need to determine which relationship to use. If both sides of the relation
will contain many of the other side (e.g. "students" and "classes"), you need a
``ManyToMany`` relation. Otherwise, you likely need a ``ManyToOne``.

.. tip::

    There is also a OneToOne relationship (e.g. one User has one Profile and vice
    versa). In practice, using this is similar to ``ManyToOne``.

The ManyToOne / OneToMany Association
-------------------------------------

Suppose that each product in your application belongs to exactly one category.
In this case, you'll need a ``Category`` class, and a way to relate a
``Product`` object to a ``Category`` object.

Start by creating a ``Category`` entity with a ``name`` field:

.. code-block:: terminal

    $ php bin/console make:entity Category

    New property name (press <return> to stop adding fields):
    > name

    Field type (enter ? to see all types) [string]:
    > string

    Field length [255]:
    > 255

    Can this field be null in the database (nullable) (yes/no) [no]:
    > no

    New property name (press <return> to stop adding fields):
    >
    (press enter again to finish)

This will generate your new entity class::

    // src/Entity/Category.php
    // ...

    class Category
    {
        /**
         * @ORM\Id
         * @ORM\GeneratedValue
         * @ORM\Column(type="integer")
         */
        private $id;

        /**
         * @ORM\Column(type="string")
         */
        private $name;

        // ... getters and setters
    }

Mapping the ManyToOne Relationship
----------------------------------

In this example, each category can be associated with *many* products. But,
each product can be associated with only *one* category. This relationship
can be summarized as: *many* products to *one* category (or equivalently,
*one* category to *many* products).

From the perspective of the ``Product`` entity, this is a many-to-one relationship.
From the perspective of the ``Category`` entity, this is a one-to-many relationship.

To map this, first create a ``category`` property on the ``Product`` class with
the ``ManyToOne`` annotation. You can do this by hand, or by using the ``make:entity``
command, which will ask you several questions about your relationship. If you're
not sure of the answer, don't worry! You can always change the settings later:

.. code-block:: terminal

    $ php bin/console make:entity

    Class name of the entity to create or update (e.g. BraveChef):
    > Product

    New property name (press <return> to stop adding fields):
    > category

    Field type (enter ? to see all types) [string]:
    > relation

    What class should this entity be related to?:
    > Category

    Relation type? [ManyToOne, OneToMany, ManyToMany, OneToOne]:
    > ManyToOne

    Is the Product.category property allowed to be null (nullable)? (yes/no) [yes]:
    > no

    Do you want to add a new property to Category so that you can access/update
    Product objects from it - e.g. $category->getProducts()? (yes/no) [yes]:
    > yes

    New field name inside Category [products]:
    > products

    Do you want to automatically delete orphaned App\Entity\Product objects
    (orphanRemoval)? (yes/no) [no]:
    > no

    New property name (press <return> to stop adding fields):
    >
    (press enter again to finish)

This made changes to *two* entities. First, it added a new ``category`` property to
the ``Product`` entity (and getter & setter methods):

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Product.php

        // ...
        class Product
        {
            // ...

            /**
             * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="products")
             */
            private $category;

            public function getCategory(): ?Category
            {
                return $this->category;
            }

            public function setCategory(?Category $category): self
            {
                $this->category = $category;

                return $this;
            }
        }

    .. code-block:: yaml

        # src/Resources/config/doctrine/Product.orm.yml
        App\Entity\Product:
            type: entity
            # ...
            manyToOne:
                category:
                    targetEntity: App\Entity\Category
                    inversedBy: products
                    joinColumn:
                        nullable: false

    .. code-block:: xml

        <!-- src/Resources/config/doctrine/Product.orm.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                https://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">

            <entity name="App\Entity\Product">
                <!-- ... -->
                <many-to-one
                    field="category"
                    target-entity="App\Entity\Category"
                    inversed-by="products">
                    <join-column nullable="false"/>
                </many-to-one>
            </entity>
        </doctrine-mapping>

This ``ManyToOne`` mapping is required. It tells Doctrine to use the ``category_id``
column on the ``product`` table to relate each record in that table with
a record in the ``category`` table.

Next, since *one* ``Category`` object will relate to *many* ``Product`` objects,
the ``make:entity`` command *also* added a ``products`` property to the ``Category``
class that will hold these objects:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Category.php

        // ...
        use Doctrine\Common\Collections\ArrayCollection;
        use Doctrine\Common\Collections\Collection;

        class Category
        {
            // ...

            /**
             * @ORM\OneToMany(targetEntity="App\Entity\Product", mappedBy="category")
             */
            private $products;

            public function __construct()
            {
                $this->products = new ArrayCollection();
            }

            /**
             * @return Collection|Product[]
             */
            public function getProducts(): Collection
            {
                return $this->products;
            }

            // addProduct() and removeProduct() were also added
        }

    .. code-block:: yaml

        # src/Resources/config/doctrine/Category.orm.yml
        App\Entity\Category:
            type: entity
            # ...
            oneToMany:
                products:
                    targetEntity: App\Entity\Product
                    mappedBy: category
        # Don't forget to initialize the collection in
        # the __construct() method of the entity

    .. code-block:: xml

        <!-- src/Resources/config/doctrine/Category.orm.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                https://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">

            <entity name="App\Entity\Category">
                <!-- ... -->
                <one-to-many
                    field="products"
                    target-entity="App\Entity\Product"
                    mapped-by="category"/>

                <!--
                    don't forget to init the collection in
                    the __construct() method of the entity
                -->
            </entity>
        </doctrine-mapping>

The ``ManyToOne`` mapping shown earlier is *required*, But, this ``OneToMany``
is optional: only add it *if* you want to be able to access the products that are
related to a category (this is one of the questions ``make:entity`` asks you). In
this example, it *will* be useful to be able to call ``$category->getProducts()``.
If you don't want it, then you also don't need the ``inversedBy`` or ``mappedBy``
config.

.. sidebar:: What is the ArrayCollection Stuff?

    The code inside ``__construct()`` is important: The ``$products`` property must
    be a collection object that implements Doctrine's ``Collection`` interface.
    In this case, an ``ArrayCollection`` object is used. This looks and acts almost
    *exactly* like an array, but has some added flexibility. Just imagine that it's
    an ``array`` and you'll be in good shape.

Your database is setup! Now, execute the migrations like normal:

.. code-block:: terminal

    $ php bin/console doctrine:migrations:diff
    $ php bin/console doctrine:migrations:migrate

Thanks to the relationship, this creates a ``category_id`` foreign key column on
the ``product`` table. Doctrine is ready to persist our relationship!

Saving Related Entities
-----------------------

Now you can see this new code in action! Imagine you're inside a controller::

    // ...

    use App\Entity\Category;
    use App\Entity\Product;
    use Symfony\Component\HttpFoundation\Response;

    class ProductController extends AbstractController
    {
        /**
         * @Route("/product", name="product")
         */
        public function index()
        {
            $category = new Category();
            $category->setName('Computer Peripherals');

            $product = new Product();
            $product->setName('Keyboard');
            $product->setPrice(19.99);
            $product->setDescription('Ergonomic and stylish!');

            // relates this product to the category
            $product->setCategory($category);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->persist($product);
            $entityManager->flush();

            return new Response(
                'Saved new product with id: '.$product->getId()
                .' and new category with id: '.$category->getId()
            );
        }
    }

When you go to ``/product``, a single row is added to both the ``category`` and
``product`` tables. The ``product.category_id`` column for the new product is set
to whatever the ``id`` is of the new category. Doctrine manages the persistence of this
relationship for you:

.. image:: /_images/doctrine/mapping_relations.png
    :align: center

If you're new to an ORM, this is the *hardest* concept: you need to stop thinking
about your database, and instead *only* think about your objects. Instead of setting
the category's integer id onto ``Product``, you set the entire ``Category`` *object*.
Doctrine takes care of the rest when saving.

.. sidebar:: Updating the Relationship from the Inverse Side

    Could you also call ``$category->addProduct()`` to change the relationship? Yes,
    but, only because the ``make:entity`` command helped us. For more details,
    see: `associations-inverse-side`_.

Fetching Related Objects
------------------------

When you need to fetch associated objects, your workflow looks just like it
did before. First, fetch a ``$product`` object and then access its related
``Category`` object::

    use App\Entity\Product;
    // ...

    public function show($id)
    {
        $product = $this->getDoctrine()
            ->getRepository(Product::class)
            ->find($id);

        // ...

        $categoryName = $product->getCategory()->getName();

        // ...
    }

In this example, you first query for a ``Product`` object based on the product's
``id``. This issues a query for *just* the product data and hydrates the
``$product``. Later, when you call ``$product->getCategory()->getName()``,
Doctrine silently makes a second query to find the ``Category`` that's related
to this ``Product``. It prepares the ``$category`` object and returns it to
you.

.. image:: /_images/doctrine/mapping_relations_proxy.png
    :align: center

What's important is the fact that you have access to the product's related
category, but the category data isn't actually retrieved until you ask for
the category (i.e. it's "lazily loaded").

Because we mapped the optional ``OneToMany`` side, you can also query in the other
direction::

    public function showProducts($id)
    {
        $category = $this->getDoctrine()
            ->getRepository(Category::class)
            ->find($id);

        $products = $category->getProducts();

        // ...
    }

In this case, the same things occur: you first query for a single ``Category``
object. Then, only when (and if) you access the products, Doctrine makes a second
query to retrieve the related ``Product`` objects. This extra query can be avoided
by adding JOINs.

.. sidebar:: Relationships and Proxy Classes

    This "lazy loading" is possible because, when necessary, Doctrine returns
    a "proxy" object in place of the true object. Look again at the above
    example::

        $product = $this->getDoctrine()
            ->getRepository(Product::class)
            ->find($id);

        $category = $product->getCategory();

        // prints "Proxies\AppEntityCategoryProxy"
        dump(get_class($category));
        die();

    This proxy object extends the true ``Category`` object, and looks and
    acts exactly like it. The difference is that, by using a proxy object,
    Doctrine can delay querying for the real ``Category`` data until you
    actually need that data (e.g. until you call ``$category->getName()``).

    The proxy classes are generated by Doctrine and stored in the cache directory.
    You'll probably never even notice that your ``$category`` object is actually
    a proxy object.

    In the next section, when you retrieve the product and category data
    all at once (via a *join*), Doctrine will return the *true* ``Category``
    object, since nothing needs to be lazily loaded.

Joining Related Records
-----------------------

In the examples above, two queries were made - one for the original object
(e.g. a ``Category``) and one for the related object(s) (e.g. the ``Product``
objects).

.. tip::

    Remember that you can see all of the queries made during a request via
    the web debug toolbar.

If you know up front that you'll need to access both objects, you
can avoid the second query by issuing a join in the original query. Add the
following method to the ``ProductRepository`` class::

    // src/Repository/ProductRepository.php
    public function findOneByIdJoinedToCategory($productId)
    {
        return $this->createQueryBuilder('p')
            // p.category refers to the "category" property on product
            ->innerJoin('p.category', 'c')
            // selects all the category data to avoid the query
            ->addSelect('c')
            ->andWhere('p.id = :id')
            ->setParameter('id', $productId)
            ->getQuery()
            ->getOneOrNullResult();
    }

This will *still* return an array of ``Product`` objects. But now, when you call
``$product->getCategory()`` and use that data, no second query is made.

Now, you can use this method in your controller to query for a ``Product``
object and its related ``Category`` with just one query::

    public function show($id)
    {
        $product = $this->getDoctrine()
            ->getRepository(Product::class)
            ->findOneByIdJoinedToCategory($id);

        $category = $product->getCategory();

        // ...
    }

.. _associations-inverse-side:

Setting Information from the Inverse Side
-----------------------------------------

So far, you've updated the relationship by calling ``$product->setCategory($category)``.
This is no accident! Each relationship has two sides: in this example, ``Product.category``
is the *owning* side and ``Category.products`` is the *inverse* side.

To update a relationship in the database, you *must* set the relationship on the
*owning* side. The owning side is always where the ``ManyToOne`` mapping is set
(for a ``ManyToMany`` relation, you can choose which side is the owning side).

Does this means it's not possible to call ``$category->addProduct()`` or
``$category->removeProduct()`` to update the database? Actually, it *is* possible,
thanks to some clever code that the ``make:entity`` command generated::

    // src/Entity/Category.php

    // ...
    class Category
    {
        // ...

        public function addProduct(Product $product): self
        {
            if (!$this->products->contains($product)) {
                $this->products[] = $product;
                $product->setCategory($this);
            }

            return $this;
        }
    }

The *key* is ``$product->setCategory($this)``, which sets the *owning* side. Thanks,
to this, when you save, the relationship *will* update in the database.

What about *removing* a ``Product`` from a ``Category``? The ``make:entity`` command
also generated a ``removeProduct()`` method::

    // src/Entity/Category.php

    // ...
    class Category
    {
        // ...

        public function removeProduct(Product $product): self
        {
            if ($this->products->contains($product)) {
                $this->products->removeElement($product);
                // set the owning side to null (unless already changed)
                if ($product->getCategory() === $this) {
                    $product->setCategory(null);
                }
            }

            return $this;
        }
    }

Thanks to this, if you call ``$category->removeProduct($product)``, the ``category_id``
on that ``Product`` will be set to ``null`` in the database.

But, instead of setting the ``category_id`` to null, what if you want the ``Product``
to be *deleted* if it becomes "orphaned" (i.e. without a ``Category``)? To choose
that behavior, use the `orphanRemoval`_ option inside ``Category``::

    // src/Entity/Category.php

    // ...

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Product", mappedBy="category", orphanRemoval=true)
     */
    private $products;

Thanks to this, if the ``Product`` is removed from the ``Category``, it will be
removed from the database entirely.

More Information on Associations
--------------------------------

This section has been an introduction to one common type of entity relationship,
the one-to-many relationship. For more advanced details and examples of how
to use other types of relations (e.g. one-to-one, many-to-many), see
Doctrine's `Association Mapping Documentation`_.

.. note::

    If you're using annotations, you'll need to prepend all annotations with
    ``@ORM\`` (e.g. ``@ORM\OneToMany``), which is not reflected in Doctrine's
    documentation.

.. _`Association Mapping Documentation`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/association-mapping.html
.. _`orphanRemoval`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/working-with-associations.html#orphan-removal
.. _`Mastering Doctrine Relations`: https://symfonycasts.com/screencast/doctrine-relations
