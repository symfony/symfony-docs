Doctrine
========

The [Doctrine][1] project is the home of a selected set of PHP libraries
primarily focused on providing persistence services and related functionality.
The integration between Symfony2 and Doctrine2 implements most of the features
the project has to offer for working with relational databases, such as:

 * Database Abstraction Layer
 * Object Relational Mapper
 * Database Migrations

>**TIP**
>You can learn more about the ORM API and functionality by reading the
>dedicated [documentation][2].

Doctrine DBAL
-------------

The Doctrine Database Abstraction Layer (DBAL) offers an intuitive and
flexible API for communicating with the most popular relational databases that
exist today. In order to start using the DBAL, configure it:

    [yml]
    # config/config.yml
    doctrine.dbal:
        driver:   PDOMySql
        dbname:   Symfony2
        user:     root
        password: null

You can then access the connection from your controllers by getting the
`database_connection` service:

    [php]
    class UserController extends Controller
    {
        public function indexAction()
        {
            $conn = $this->container->getService('database_connection');

            $users = $conn->fetchAll('SELECT * FROM users');
        }
    }

You can then execute a query and fetch the results as show above with the
`fetchAll()` method.

>**TIP**
>You can learn more about the DBAL API and functionality by reading the
>dedicated [documentation][3].

Doctrine Object Relational Mapper
---------------------------------

The Doctrine Object Relational Mapper (ORM) is the prize library under the
Doctrine Project umbrella. It is built on top of the Doctrine DBAL (Database
Abstraction Layer) and offers transparent persistence of PHP5 objects to a
relational database.

Before using the ORM, enable it in the configuation:

    [php]
    # config/config.yml
    doctrine.orm: ~

Next, write your entity classes. A typical entity read as follows:

    [php]
    // Application/HelloBundle/Entities/User.php

    namespace Application\HelloBundle\Entities;

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

    $ php hello/console doctrine:schema:create

>**NOTE**
>Don't forget to create the database if it does not exist yet.

Eventually, use your entity and manage its persistent state with Doctrine:

    [php]
    use Application\HelloBundle\Entites\User;

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

[1]: http://www.doctrine-project.org/
[3]: http://www.doctrine-project.org/projects/orm/2.0/docs/en
[2]: http://www.doctrine-project.org/projects/dbal/2.0/docs/en
