.. index::
   pair: Doctrine; ORM

ORM
===

`Doctrine`_ is an Object relational mapper (ORM) for PHP that sits on top of a
powerful DataBase Abstraction Layer (DBAL). It provides transparent
persistence for PHP objects.

.. tip::

    You can read more about the Doctrine Object Relational Mapper on the
    official `documentation`_ website.

To get started, enable and configure the :doc:`Doctrine DBAL
</book/doctrine/dbal/overview>`, then enable the ORM. The minimal
necessary configuration is to specify the bundle name which contains your entities.

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        doctrine:
            orm:
                default_entity_manager: default
                entity_managers:
                    default:
                        mappings:
                            AcmeHello: ~

    .. code-block:: xml

        <!-- xmlns:doctrine="http://symfony.com/schema/dic/doctrine" -->
        <!-- xsi:schemaLocation="http://symfony.com/schema/dic/doctrine http://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd"> -->

        <doctrine:config>
            <doctrine:orm default-entity-manager="default">
                <doctrine:entity-manager name="default">
                    <doctrine:mapping name="AcmeHello" />
                </doctrine:entity-manager>
            </doctrine:orm>
        </doctrine:config>

    .. code-block:: php

        $container->loadFromExtension('doctrine', array('orm' => array(
            "default_entity_manager" => "default",
            "entity_managers" => array(
                "default => array(
                    "mappings" => array("AcmeHello" => array()),
                ),
            ),
        ));

As Doctrine provides transparent persistence for PHP objects, it works with
any PHP class:

.. code-block:: php

    // Acme/HelloBundle/Entity/User.php
    namespace Acme\HelloBundle\Entity;

    class User
    {
        protected $id;
        protected $name;

        public function getId()
        {
            return $this->id;
        }

        public function setName($name)
        {
            $this->name = $name;
        }

        public function getName()
        {
            return $this->name;
        }
    }

.. tip::

     When defining your entities, you can omit the getter/setter methods and
     let Doctrine create them for you with the ``doctrine:generate:entities``
     command. This only works after you create the mapping information (see
     below).

To let Doctrine manage your classes (entities in Doctrine2 speak), you need to
write mapping information with annotations, XML, or YAML:

.. configuration-block::

    .. code-block:: php-annotations

        // Acme/HelloBundle/Entity/User.php
        namespace Acme\HelloBundle\Entity;

        /**
         * @orm:Entity
         */
        class User
        {
            /**
             * @orm:Id
             * @orm:Column(type="integer")
             * @orm:GeneratedValue(strategy="AUTO")
             */
            protected $id;

            /**
             * @orm:Column(type="string", length="255")
             */
            protected $name;
        }

    .. code-block:: yaml

        # Acme/HelloBundle/Resources/config/doctrine/metadata/orm/Acme.HelloBundle.Entity.User.dcm.yml
        Acme\HelloBundle\Entity\User:
            type: entity
            table: user
            id:
                id:
                    type: integer
                    generator:
                        strategy: AUTO
            fields:
                name:
                    type: string
                    length: 255

    .. code-block:: xml

        <!-- Acme/HelloBundle/Resources/config/doctrine/metadata/orm/Acme.HelloBundle.Entity.User.dcm.xml -->
        <doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
              xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
              xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                            http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">

            <entity name="Acme\HelloBundle\Entity\User" table="user">
                <id name="id" type="integer" column="id">
                    <generator strategy="AUTO"/>
                </id>
                <field name="name" column="name" type="string" length="255" />
            </entity>

        </doctrine-mapping>

.. note::

    When using annotations in your Symfony2 project you have to namespace all
    Doctrine ORM annotations with the ``orm:`` prefix.

.. tip::

    If you use YAML or XML to describe your entities, you can omit the creation
    of the Entity class, and let the ``doctrine:generate:entities`` command do
    it for you.

Create the database and the schema related to your metadata information with
the following commands:

.. code-block:: bash

    $ php app/console doctrine:database:create
    $ php app/console doctrine:schema:create

Eventually, use your entity and manage its persistent state with Doctrine:

.. code-block:: php

    // Acme/HelloBundle/Controller/UserController.php
    namespace Acme\HelloBundle\Controller;

    use Acme\HelloBundle\Entity\User;

    class UserController extends Controller
    {
        public function createAction()
        {
            $user = new User();
            $user->setName('Jonathan H. Wage');

            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($user);
            $em->flush();

            // ...
        }

        public function editAction($id)
        {
            $em = $this->get('doctrine.orm.entity_manager');
            $user = $em->find('AcmeHello:User', $id);
            $user->setBody('new body');
            $em->persist($user);
            $em->flush();

            // ...
        }

        public function deleteAction($id)
        {
            $em = $this->get('doctrine.orm.entity_manager');
            $user = $em->find('AcmeHello:User', $id);
            $em->remove($user);
            $em->flush();

            // ...
        }
    }

Now the scenario arises where you want to change your mapping information and
update your development database schema without blowing away everything and
losing your existing data. So first let's just add a new property to our ``User``
entity:

.. code-block:: php

    namespace Acme\HelloBundle\Entity;

    /** @orm:Entity */
    class User
    {
        /** @orm:Column(type="string") */
        protected $new;

        // ...
    }

Once you've done that, to get your database schema updated with the new column
you just need to run the following command:

    $ php app/console doctrine:schema:update

Now your database will be updated and the new column added to the database
table.


.. _documentation: http://www.doctrine-project.org/docs/orm/2.0/en
.. _Doctrine:      http://www.doctrine-project.org
