.. index::
   pair: Doctrine; MongoDB ODM

MongoDB ODM
===========

The `MongoDB`_ Object Document Mapper is much like the Doctrine2 ORM in the way
it works and architecture. You only deal with plain PHP objects and they are
persisted transparently without imposing on your domain model.

.. tip::

    You can read more about the Doctrine MongoDB Object Document Mapper on the
    projects `documentation`_.

To get started working with Doctrine and the MongoDB Object Document Mapper you
just need to enable it:

.. code-block:: yaml

    # app/config/config.yml

    doctrine_odm.mongodb: ~

Now you can start writing documents and mapping them with annotations, xml, or
yaml. In this example we will use annotations::

    // Application/HelloBundle/Document/User.php

    namespace Application\HelloBundle\Document;

    /**
     * @mongodb:Document(collection="users")
     */
    class User
    {
        /**
         * @mongodb:Id
         */
        protected $id;

        /**
         * @mongodb:String
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

Now, use your document and manage its persistent state with Doctrine::

    use Application\HelloBundle\Document\User;

    class UserController extends Controller
    {
        public function createAction()
        {
            $user = new User();
            $user->setName('Jonathan H. Wage');

            $dm = $this->get('doctrine.odm.mongodb.document_manager');
            $dm->persist($user);
            $dm->flush();

            // ...
        }

        public function editAction($id)
        {
            $dm = $this->get('doctrine.odm.mongodb.document_manager');
            $user = $dm->createQuery('find all from HelloBundle:User where id = ?', $id);
            $user->setBody('new body');
            $dm->flush();

            // ...
        }

        public function deleteAction($id)
        {
            $dm = $this->get('doctrine.orm.entity_manager');
            $user = $dm->createQuery('find all from HelloBundle:User where id = ?', $id);
            $dm->remove($user);
            $dm->flush();

            // ...
        }
    }

.. _MongoDB:       http://www.mongodb.org/
.. _documentation: http://www.doctrine-project.org/projects/mongodb_odm/1.0/docs/en
