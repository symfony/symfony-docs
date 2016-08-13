.. index::
    single: Doctrine; Associations

How to Work with Doctrine Associations / Relations
==================================================

Suppose that each product in your application belongs to exactly one category.
In this case, you'll need a ``Category`` class, and a way to relate a
``Product`` object to a ``Category`` object.

Start by creating the ``Category`` entity. Since you know that you'll eventually
need to persist category objects through Doctrine, you can let Doctrine create
the class for you.

.. code-block:: bash

    $ php app/console doctrine:generate:entity --no-interaction \
        --entity="AppBundle:Category" \
        --fields="name:string(255)"

This task generates the ``Category`` entity for you, with an ``id`` field,
a ``name`` field and the associated getter and setter functions.

Relationship Mapping Metadata
-----------------------------

In this example, each category can be associated with *many* products, while
each product can be associated with only *one* category. This relationship
can be summarized as: *many* products to *one* category (or equivalently,
*one* category to *many* products).

From the perspective of the ``Product`` entity, this is a many-to-one relationship.
From the perspective of the ``Category`` entity, this is a one-to-many relationship.
This is important, because the relative nature of the relationship determines
which mapping metadata to use. It also determines which class *must* hold
a reference to the other class.

To relate the ``Product`` and ``Category`` entities, simply create a ``category``
property on the ``Product`` class, annotated as follows:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Product.php

        // ...
        class Product
        {
            // ...

            /**
             * @ORM\ManyToOne(targetEntity="Category", inversedBy="products")
             * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
             */
            private $category;
        }

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/doctrine/Product.orm.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">

            <entity name="AppBundle\Entity\Product">
                <!-- ... -->
                <many-to-one
                    field="category"
                    target-entity="Category"
                    inversed-by="products"
                    join-column="category">

                    <join-column name="category_id" referenced-column-name="id" />
                </many-to-one>
            </entity>
        </doctrine-mapping>

This many-to-one mapping is critical. It tells Doctrine to use the ``category_id``
column on the ``product`` table to relate each record in that table with
a record in the ``category`` table.

Next, since a single ``Category`` object will relate to many ``Product``
objects, a ``products`` property can be added to the ``Category`` class
to hold those associated objects.

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Category.php

        // ...
        use Doctrine\Common\Collections\ArrayCollection;

        class Category
        {
            // ...

            /**
             * @ORM\OneToMany(targetEntity="Product", mappedBy="category")
             */
            private $products;

            public function __construct()
            {
                $this->products = new ArrayCollection();
            }
        }

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/doctrine/Category.orm.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">

            <entity name="AppBundle\Entity\Category">
                <!-- ... -->
                <one-to-many
                    field="products"
                    target-entity="Product"
                    mapped-by="category" />

                <!--
                    don't forget to init the collection in
                    the __construct() method of the entity
                -->
            </entity>
        </doctrine-mapping>

While the many-to-one mapping shown earlier was mandatory, this one-to-many
mapping is optional. It is included here to help demonstrate Doctrine's range
of relationship management capabailties. Plus, in the context of this application,
it will likely be convenient for each ``Category`` object to automatically
own a collection of its related ``Product`` objects.

.. note::

    The code in the constructor is important.  Rather than being instantiated
    as a traditional ``array``, the ``$products`` property must be of a type
    that implements Doctrine's ``Collection`` interface. In this case, an
    ``ArrayCollection`` object is used. This object looks and acts almost
    *exactly* like an array, but has some added flexibility. If this makes
    you uncomfortable, don't worry. Just imagine that it's an ``array``
    and you'll be in good shape.

.. tip::

   The targetEntity value in the metadata used above can reference any entity
   with a valid namespace, not just entities defined in the same namespace. To
   relate to an entity defined in a different class or bundle, enter a full
   namespace as the targetEntity.

Now that you've added new properties to both the ``Product`` and ``Category``
classes, tell Doctrine to generate the missing getter and setter methods for you:

.. code-block:: bash

    $ php app/console doctrine:generate:entities AppBundle

Ignore the Doctrine metadata for a moment. You now have two classes - ``Product``
and ``Category``, with a natural many-to-one relationship. The ``Product``
class holds a *single* ``Category`` object, and the ``Category`` class holds
a *collection* of ``Product`` objects. In other words, you've built your classes
in a way that makes sense for your application. The fact that the data needs
to be persisted to a database is always secondary.

Now, review the metadata above the ``Product`` entity's ``$category`` property.
It tells Doctrine that the related class is ``Category``, and that the ``id``
of the related category record should be stored in a ``category_id`` field
on the ``product`` table.

In other words, the related ``Category`` object will be stored in the
``$category`` property, but behind the scenes, Doctrine will persist this
relationship by storing the category's id in the ``category_id`` column
of the ``product`` table.

.. image:: /_images/doctrine/mapping_relations.png
    :align: center

The metadata above the ``Category`` entity's ``$products`` property is less
complicated. It simply tells Doctrine to look at the ``Product.category``
property to figure out how the relationship is mapped.

Before you continue, be sure to tell Doctrine to add the new ``category``
table, the new ``product.category_id`` column, and the new foreign key:

.. code-block:: bash

    $ php app/console doctrine:schema:update --force

Saving Related Entities
-----------------------

Now you can see this new code in action! Imagine you're inside a controller::

    // ...

    use AppBundle\Entity\Category;
    use AppBundle\Entity\Product;
    use Symfony\Component\HttpFoundation\Response;

    class DefaultController extends Controller
    {
        public function createProductAction()
        {
            $category = new Category();
            $category->setName('Computer Peripherals');

            $product = new Product();
            $product->setName('Keyboard');
            $product->setPrice(19.99);
            $product->setDescription('Ergonomic and stylish!');

            // relate this product to the category
            $product->setCategory($category);

            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->persist($product);
            $em->flush();

            return new Response(
                'Saved new product with id: '.$product->getId()
                .' and new category with id: '.$category->getId()
            );
        }
    }

Now, a single row is added to both the ``category`` and ``product`` tables.
The ``product.category_id`` column for the new product is set to whatever
the ``id`` is of the new category. Doctrine manages the persistence of this
relationship for you.

Fetching Related Objects
------------------------

When you need to fetch associated objects, your workflow looks just like it
did before. First, fetch a ``$product`` object and then access its related
``Category`` object::

    public function showAction($productId)
    {
        $product = $this->getDoctrine()
            ->getRepository('AppBundle:Product')
            ->find($productId);

        $categoryName = $product->getCategory()->getName();

        // ...
    }

In this example, you first query for a ``Product`` object based on the product's
``id``. This issues a query for *just* the product data and hydrates the
``$product`` object with that data. Later, when you call ``$product->getCategory()->getName()``,
Doctrine silently makes a second query to find the ``Category`` that's related
to this ``Product``. It prepares the ``$category`` object and returns it to
you.

.. image:: /_images/doctrine/mapping_relations_proxy.png
    :align: center

What's important is the fact that you have easy access to the product's related
category, but the category data isn't actually retrieved until you ask for
the category (i.e. it's "lazily loaded").

You can also query in the other direction::

    public function showProductsAction($categoryId)
    {
        $category = $this->getDoctrine()
            ->getRepository('AppBundle:Category')
            ->find($categoryId);

        $products = $category->getProducts();

        // ...
    }

In this case, the same things occur: you first query out for a single ``Category``
object, and then Doctrine makes a second query to retrieve the related ``Product``
objects, but only once/if you ask for them (i.e. when you call ``->getProducts()``).
The ``$products`` variable is an array of all ``Product`` objects that relate
to the given ``Category`` object via their ``category_id`` value.

.. sidebar:: Relationships and Proxy Classes

    This "lazy loading" is possible because, when necessary, Doctrine returns
    a "proxy" object in place of the true object. Look again at the above
    example::

        $product = $this->getDoctrine()
            ->getRepository('AppBundle:Product')
            ->find($productId);

        $category = $product->getCategory();

        // prints "Proxies\AppBundleEntityCategoryProxy"
        dump(get_class($category));
        die();

    This proxy object extends the true ``Category`` object, and looks and
    acts exactly like it. The difference is that, by using a proxy object,
    Doctrine can delay querying for the real ``Category`` data until you
    actually need that data (e.g. until you call ``$category->getName()``).

    The proxy classes are generated by Doctrine and stored in the cache directory.
    And though you'll probably never even notice that your ``$category``
    object is actually a proxy object, it's important to keep it in mind.

    In the next section, when you retrieve the product and category data
    all at once (via a *join*), Doctrine will return the *true* ``Category``
    object, since nothing needs to be lazily loaded.

Joining Related Records
-----------------------

In the above examples, two queries were made - one for the original object
(e.g. a ``Category``) and one for the related object(s) (e.g. the ``Product``
objects).

.. tip::

    Remember that you can see all of the queries made during a request via
    the web debug toolbar.

Of course, if you know up front that you'll need to access both objects, you
can avoid the second query by issuing a join in the original query. Add the
following method to the ``ProductRepository`` class::

    // src/AppBundle/Entity/ProductRepository.php
    public function findOneByIdJoinedToCategory($productId)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT p, c FROM AppBundle:Product p
                JOIN p.category c
                WHERE p.id = :id'
            )->setParameter('id', $productId);

        try {
            return $query->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }

Now, you can use this method in your controller to query for a ``Product``
object and its related ``Category`` with just one query::

    public function showAction($productId)
    {
        $product = $this->getDoctrine()
            ->getRepository('AppBundle:Product')
            ->findOneByIdJoinedToCategory($productId);

        $category = $product->getCategory();

        // ...
    }

More Information on Associations
--------------------------------

This section has been an introduction to one common type of entity relationship,
the one-to-many relationship. For more advanced details and examples of how
to use other types of relations (e.g. one-to-one, many-to-many), see
Doctrine's `Association Mapping Documentation`_.

.. note::

    If you're using annotations, you'll need to prepend all annotations with
    ``ORM\`` (e.g. ``ORM\OneToMany``), which is not reflected in Doctrine's
    documentation. You'll also need to include the ``use Doctrine\ORM\Mapping as ORM;``
    statement, which *imports* the ``ORM`` annotations prefix.

.. _`Association Mapping Documentation`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/association-mapping.html
