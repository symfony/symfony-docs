MongoDB
=======

The [MongoDB][1] Object Document Mapper is much like the Doctrine2 ORM in the
way it works and architecture. The plain PHP5 objects are persisted
transparently.

>**TIP**
>You can read more about the Doctrine MongoDB Object Document Mapper on the
>projects [documentation][2].

To get started working with Doctrine and MongoDB you just need to configure it:

    [yml]
    # config/config.yml
    doctrine_odm.mongodb:
        default_document_manager: default
        cache_driver:             array
        document_managers:
            default:
                connection: mongodb
        connections:
            mongodb:
                server:           localhost
                default_database: jwage

Working with documents and MongoDB is very similar to how you work with
entities in the ORM. You are only dealing with plain PHP objects that are
mapped to MongoDB:

    [php]
    namespace Application\HelloBundle\Documents;

    /**
     * @Document
     */
    class User
    {
        /**
         * @Id
         */
        protected $id;

        /**
         * @String
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

Now that you have a document created and mapped properly you can begin
managing its persistent state with Doctrine:

    class UserController extends Controller
    {
        public function createAction()
        {
            $user = new User();
            $user->setName('Jonathan H. Wage');

            $dm = $this->container->getService('doctrine_odm.mongodb.document_manager');
            $dm->persist($user);
            $dm->flush();

            // ...
        }

        public function editAction($id)
        {
            $dm = $this->container->getService('doctrine_odm.mongodb.document_manager');
            $user = $dm->createQuery('HelloBundle:User')
                ->where('id', $id)
                ->getSingleResult();
            $user->setBody('new body');
            $dm->flush();
        }

        public function deleteAction($id)
        {
            $dm = $this->container->getService('doctrine_odm.mongodb.document_manager');
            $user = $dm->createQuery('HelloBundle:User')
                ->where('id', $id)
                ->getSingleResult();
            $dm->remove($user);
            $dm->flush();

          // ...
        }
    }

[1]: http://www.mongodb.org/
[2]: http://www.doctrine-project.org/projects/mongodb_odm/1.0/docs/en
