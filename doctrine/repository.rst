.. index::
    single: Doctrine; Custom Repository Class

How to Create custom Repository Classes
=======================================

In the previous sections, you began constructing and using more complex queries
from inside a controller. In order to isolate, reuse and test these queries,
it's a good practice to create a custom repository class for your entity.
Methods containing your query logic can then be stored in this class.

To do this, add the repository class name to your entity's mapping definition:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Product.php
        namespace AppBundle\Entity;

        use Doctrine\ORM\Mapping as ORM;

        /**
         * @ORM\Entity(repositoryClass="AppBundle\Repository\ProductRepository")
         */
        class Product
        {
            //...
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/doctrine/Product.orm.yml
        AppBundle\Entity\Product:
            type: entity
            repositoryClass: AppBundle\Repository\ProductRepository
            # ...

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/doctrine/Product.orm.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">

            <entity
                name="AppBundle\Entity\Product"
                repository-class="AppBundle\Repository\ProductRepository">

                <!-- ... -->
            </entity>
        </doctrine-mapping>

Then, create an empty ``AppBundle\Repository\ProductRepository`` class extending
from ``Doctrine\ORM\EntityRepository``.

Next, add a new method - ``findAllOrderedByName()`` - to the newly-generated
``ProductRepository`` class. This method will query for all the ``Product``
entities, ordered alphabetically by name.

.. code-block:: php

    // src/AppBundle/Repository/ProductRepository.php
    namespace AppBundle\Repository;

    use Doctrine\ORM\EntityRepository;

    class ProductRepository extends EntityRepository
    {
        public function findAllOrderedByName()
        {
            return $this->getEntityManager()
                ->createQuery(
                    'SELECT p FROM AppBundle:Product p ORDER BY p.name ASC'
                )
                ->getResult();
        }
    }

.. tip::

    The entity manager can be accessed via ``$this->getEntityManager()``
    from inside the repository.

You can use this new method just like the default finder methods of the repository::

    $em = $this->getDoctrine()->getManager();
    $products = $em->getRepository('AppBundle:Product')
        ->findAllOrderedByName();

.. note::

    When using a custom repository class, you still have access to the default
    finder methods such as ``find()`` and ``findAll()``.
