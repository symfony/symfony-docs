.. index::
   single: Doctrine; ORM
   single: ORM

ORM
===

`Doctrine`_ is an Object relational mapper (ORM) for PHP that sits on top of a powerful
database abstraction layer (DBAL). One of its key features is the option to write database
queries in a proprietary object oriented SQL dialect called Doctrine Query Language (DQL),
inspired by Hibernates HQL. This provides developers with a powerful alternative to SQL that
maintains flexibility without requiring unnecessary code duplication.

.. tip::
   You can read more about the Doctrine Object Relational Mapper on the projects `documentation`_.

To get started you just need to enable the ORM:

.. code-block:: yaml

    # config/config.yml

    doctrine.orm: ~

Now you can start writing entities and mapping them with annotations, xml, or yaml. In this
example we will use annotations:

    // Application/HelloBundle/Entity/User.php

    namespace Application\HelloBundle\Entity;

    /**
     * @Entity
     */
    class User
    {
        /**
         * @Id
         * @Column(type="integer")
         * @GeneratedValue(strategy="IDENTITY")
         */
        protected $id;

        /**
         * @Column(type="string", length="255")
         */
        protected $name;

        /**
         * Get id
         *
         * @return integer $id
         */
        public function getId()
        {
            return $this->id;
        }

        /**
         * Set name
         *
         * @param string $name
         */
        public function setName($name)
        {
            $this->name = $name;
        }

        /**
         * Get name
         *
         * @return string $name
         */
        public function getName()
        {
            return $this->name;
        }
    }

Now, create the schema by running the following command:

.. code-block:: bash

    $ php hello/console doctrine:schema:create

.. note::
   Don't forget to create the database if it does not exist yet.

Eventually, use your entity and manage its persistent state with Doctrine::

    use Application\HelloBundle\Entity\User;

    class UserController extends Controller
    {
        public function createAction()
        {
            $user = new User();
            $user->setName('Jonathan H. Wage');

            $em = $this->container->getService('doctrine.orm.entity_manager');
            $em->persist($user);
            $em->flush();

            // ...
        }

        public function editAction($id)
        {
            $em = $this->container->getService('doctrine.orm.entity_manager');
            $user = $em->createQuery('select u from HelloBundle:User where id = ?', $id);
            $user->setBody('new body');
            $em->flush();

            // ...
        }

        public function deleteAction($id)
        {
            $em = $this->container->getService('doctrine.orm.entity_manager');
            $user = $em->createQuery('select e from HelloBundle:User where id = ?', $id);
            $em->remove($user);
            $em->flush();

            // ...
        }
    }

.. _documentation http://www.doctrine-project.org/projects/orm/2.0/docs/en
.. _Doctrine: http://www.doctrine-project.org