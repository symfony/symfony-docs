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
</guides/doctrine/dbal/overview>`, then enable the ORM:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        doctrine.orm: ~

    .. code-block:: xml

        <!-- xmlns:doctrine="http://www.symfony-project.org/schema/dic/doctrine" -->
        <!-- xsi:schemaLocation="http://www.symfony-project.org/schema/dic/doctrine http://www.symfony-project.org/schema/dic/doctrine/doctrine-1.0.xsd"> -->

        <doctrine:orm />

    .. code-block:: php

        $container->loadFromExtension('doctrine', 'orm');

As Doctrine provides transparent persistence for PHP objects, it works with
any PHP class::

    // Application/HelloBundle/Entity/User.php
    namespace Application\HelloBundle\Entity;

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
    below.)

To let Doctrine manage your classes (entities in Doctrine2 speak), you need to
write mapping information with annotations, XML, or YAML:

.. configuration-block::

    .. code-block:: php

        // Application/HelloBundle/Entity/User.php
        namespace Application\HelloBundle\Entity;

        /**
         * @orm:Entity
         */
        class User
        {
            /**
             * @orm:Id
             * @orm:Column(type="integer")
             * @orm:GeneratedValue(strategy="IDENTITY")
             */
            protected $id;

            /**
             * @orm:Column(type="string", length="255")
             */
            protected $name;
        }

    .. code-block:: yaml

        # Application/HelloBundle/Resources/config/doctrine/metadata/orm/Application.HelloBundle.Entity.User.dcm.yml
        Application\HelloBundle\Entity\User:
            type: entity
            table: user
            id:
                id:
                    type: integer
                    generator:
                        strategy: IDENTITY
            fields:
                name:
                    type: string
                    length: 50

    .. code-block:: xml

        <!-- Application/HelloBundle/Resources/config/doctrine/metadata/orm/Application.HelloBundle.Entity.User.dcm.xml -->
        <doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
              xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
              xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                            http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">

            <entity name="Application\HelloBundle\Entity\User" table="user">
                <id name="id" type="integer" column="id">
                    <generator strategy="IDENTITY"/>
                </id>
                <field name="name" column="name" type="string" length="255" />
            </entity>

        </doctrine-mapping>

.. note::
   If you use YAML or XML to describe your entities, you can omit the creation
   of the Entity class, and let the ``doctrine:generate:entities`` command do
   it for you.

Create the database and the schema related to your metadata information with
the following commands:

.. code-block:: bash

    $ php app/console doctrine:database:create
    $ php app/console doctrine:schema:create

Eventually, use your entity and manage its persistent state with Doctrine::

    use Application\HelloBundle\Entity\User;

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
            $user = $em->createQuery('SELECT u FROM HelloBundle:User WHERE id = ?', $id);
            $user->setBody('new body');
            $em->flush();

            // ...
        }

        public function deleteAction($id)
        {
            $em = $this->get('doctrine.orm.entity_manager');
            $user = $em->createQuery('SELECT e FROM HelloBundle:User WHERE id = ?', $id);
            $em->remove($user);
            $em->flush();

            // ...
        }
    }

.. _documentation: http://www.doctrine-project.org/projects/orm/2.0/docs/en
.. _Doctrine:      http://www.doctrine-project.org
