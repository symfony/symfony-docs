The [MongoDB](http://www.mongodb.org) Object Document Mapper is much like like the Doctrine2
ORM in the way it works and architecture. The plain PHP5 objects are persisted transparently.
Instead of working with `EntityManager` instances you work with `DocumentManager` instances.

### Configuring Container

To get started working with Doctrine and MongoDB you just need to configure it with the
following:

    doctrine_odm.mongodb:
      default_document_manager: default
      cache_driver:             array
      document_managers:
        default:
          connection:           mongodb
      connections:
        mongodb:
          server:               localhost
          default_database:     jwage

### Getting DocumentManager

Now just like the ORM you can get the `DocumentManager` from your controllers:

    class UserController extends DoctrineController
    {
        public function indexAction()
        {
            $dm = $this->container->getDoctrine_Odm_Mongodb_DocumentManagerService();
        }
    }

### Working with Documents

Working with documents and MongoDB is very similar to how you work with entities in the ORM.
You are only dealing with plain PHP objects that are mapped to MongoDB:

    namespace Application\HelloBundle\Documents;

    /**
     * @Document
     */
    class User
    {
        /**
         * @Id
         */
        private $id;

        /**
         * @String
         */
        private $name;

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

#### Managing Document Persistence

Now that you have a document created and mapped properly you can begin managing its persistent
state with Doctrine:

    class UserController extends DoctrineController
    {
        public function createAction()
        {
            $user = new User();
            $user->setName('Jonathan H. Wage');

            $dm = $this->container->getDoctrine_Odm_Mongodb_DocumentManagerService();
            $dm->persist($user);
            $dm->flush();

            // ...
        }

        public function editAction($id)
        {
            $dm = $this->container->getDoctrine_Odm_Mongodb_DocumentManagerService();
            $user = $dm->createQuery('HelloBundle:User')
                ->where('id', $id)
                ->getSingleResult();
            $user->setBody('new body');
            $dm->flush();
        }

        public function deleteAction($id)
        {
          $dm = $this->container->getDoctrine_Odm_Mongodb_DocumentManagerService();
          $user = $dm->createQuery('HelloBundle:User')
              ->where('id', $id)
              ->getSingleResult();
          $dm->remove($user);
          $dm->flush();
        }
    }

You can read more about the Doctrine MongoDB Object Document Mapper on the projects 
[documentation](http://www.doctrine-project.org/projects/mongodb_odm/1.0/docs/en).