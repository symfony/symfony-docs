Doctrine
========

The Doctrine Project is the home of a selected set of PHP libraries primarily focused on
providing persistence services and related functionality. The integration between Symfony2
and Doctrine2 implements most of the features the project has to offer for working with
relational databases, such as:

* <a href="#dbal">Database Abstraction Layer</a>
* <a href="#orm">Object Relational Mapper</a>
* <a href="#database-migrations">Database Migrations</a>

Continue reading to get a quick tour of how to get started using these features with Symfony2!

## Database Abstraction Layer
<a name="dbal"></a>

The Doctrine Database Abstraction Layer (DBAL) offers an intuitive and flexible API for
communicating with the most popular relational databases that exist today.

### Configuring

In order to start using the DBAL you simply need to configure it. If you are using YAML,
open `application/config/config.yml`, add and customize the following as necessary:

    # ...

    doctrine.dbal:
      driver:               PDOMySql
      dbname:               Symfony2
      user:                 root
      password:             null

### Getting Connection

Now that you have configured the DBAL you can access the connection from your controllers
with the `getDatabaseConnection()` method:

    class UserController extends DoctrineController
    {
        public function indexAction()
        {
            $conn = $this->container->getDatabaseConnection();
        }
    }

### Using Connection

Using the connection is simple, you can execute a query and fetch the results with the
following:

    class UserController extends DoctrineController
    {
        public function indexAction()
        {
            $conn = $this->container->getDatabaseConnection();
            $users = $conn->fetchAll('SELECT * FROM users');
        }
    }

You can learn more about the DBAL API and functionality by reading the projects
[documentation](http://www.doctrine-project.org/projects/dbal/2.0/docs/en).

<a name="orm"></a>
## Object Relational Mapper

The Doctrine Object Relational Mapper (ORM) is the prize library under the Doctrine Project
umbrella. It is built on top of the DBAL and offers transparent persistence of PHP5 objects
to a relational database.

### Configuring

Using the ORM in Symfony2 is just as easy as the DBAL, you just need to first configure it:

    doctrine.orm:
      default_entity_manager:   default
      cache_driver:             apc           # array, apc, memcache, xcache
      entity_managers:
        default:
          connection:           default
        customer:
          connection:           customer

### Getting EntityManager

Once you have configured the ORM you can get the `EntityManager` instances by using the
`getEntityManager()` method:

    class UserController extends DoctrineController
    {
        public function indexAction()
        {
            $em = $this->container->getEntityManager();
        }
    }

### Entities and Mapping Information

The next thing we have to do in order to get started working with the Doctrine2 ORM is write
our first entity. Create a new `Application/HelloBundle/Entities/User.php` and paste the
following code inside:

    namespace Application\HelloBundle\Entities;

    /**
     * @Entity
     */
    class User
    {
        /**
         * @Id
         */
        private $id;

        /**
         * @Column(type="string", length="255")
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

### Managing Entity Persistence

Now that you have an entity created and mapped properly you can begin managing its persistent
state with Doctrine:

    class UserController extends DoctrineController
    {
        public function createAction()
        {
            $user = new User();
            $user->setName('Jonathan H. Wage');
            
            $em = $this->container->getEntityManager();
            $em->persist($user);
            $em->flush();

            // ...
        }

        public function editAction($id)
        {
            $em = $this->container->getEntityManager();
            $user = $em->createQuery('select u from HelloBundle:User where id = ?', $id);
            $user->setBody('new body');
            $em->flush();
        }

        public function deleteAction($id)
        {
          $em = $this->container->getEntityManager();
          $user = $em->createQuery('select e from HelloBundle:User where id = ?', $id);
          $em->remove($user);
          $em->flush();
        }
    }

### Console Commands

The Doctrine2 ORM integration offers several console commands under the `doctrine`
namespace. To view a list of the commands you can run the console without any arguments
or options:

    $ php console
    ...

    doctrine
      :ensure-production-settings  Verify that Doctrine is properly configured for a production environment.
      :schema-tool                 Processes the schema and either apply it directly on EntityManager or generate the SQL output.
    doctrine:cache
      :clear-metadata              Clear all metadata cache for a entity manager.
      :clear-query                 Clear all query cache for a entity manager.
      :clear-result                Clear result cache for a entity manager.
    doctrine:data
      :load                        Load data fixtures to your database.
    doctrine:database
      :create                      Create the configured databases.
      :drop                        Drop the configured databases.
    doctrine:generate
      :entities                    Generate entity classes and method stubs from your mapping information.
      :entity                      Generate a new Doctrine entity inside a bundle.
      :proxies                     Generates proxy classes for entity classes.
      :repositories                Generate repository classes from your mapping information.
    doctrine:mapping
      :convert                     Convert mapping information between supported formats.
      :convert-d1-schema           Convert a Doctrine1 schema to Doctrine2 mapping files.
      :import                      Import mapping information from an existing database.
    doctrine:query
      :dql                         Executes arbitrary DQL directly from the command line.
      :sql                         Executes arbitrary SQL directly from the command line.
    doctrine:schema
      :create                      Processes the schema and either create it directly on EntityManager Storage Connection or generate the SQL output.
      :drop                        Processes the schema and either drop the database schema of EntityManager Storage Connection or generate the SQL output.
      :update                      Processes the schema and either update the database schema of EntityManager Storage Connection or generate the SQL output.

    ...

### Schema Tool

The schema tool in Doctrine2 allows you to easily drop and create the database schemas
for your mapping information. To create the schema in your configured database you can
run the following command:

    php console doctrine:schema:create

Similarly if you want to then drop that same schema you just created you can run the `drop`
command:

    php console doctrine:schema:drop

Now the scenario arrises where you want to change your mapping information and update your
development database schema without blowing away everything and losing your existing data.
So first lets just add a new property to our `User` entity:

    namespace Application\HelloBundle\Entities;

    /** @Entity */
    class User
    {
        /** @Column(type="string") */
        private $new;

        // ...
    }

Once you've done that, to get your database schema updated with the new column you just
need to run the following command:

    php console doctrine:schema:update

Now your database will be updated and the new column added to the database table!

You can read more about the Doctrine Object Relational Mapper on the projects 
[documentation](http://www.doctrine-project.org/projects/orm/2.0/docs/en).

<a name="database-migrations"></a>
## Database Migrations

The database migrations feature is an extension of the database abstraction layer and offers
you the ability to programatically deploy new versions of your database schema in a safe and
standardized way.

All of the migrations functionality is contained in a few console commands:

    doctrine:migrations
      :diff                        Generate a migration by comparing your current database to your mapping information.
      :execute                     Execute a single migration version up or down manually.
      :generate                    Generate a blank migration class.
      :migrate                     Execute a migration to a specified version or the latest available version.
      :status                      View the status of a set of migrations.
      :version                     Manually add and delete migration versions from the version table.

Every bundle manages its own migrations so when working with the above commands you must
specify the bundle you want to work with. For example to see the status of a bundles migrations
you can run the `status` command:

    $ php console doctrine:migrations:status --bundle="Application\HelloBundle"

     == Configuration

        >> Name:                                               HelloBundle Migrations
        >> Configuration Source:                               manually configured
        >> Version Table Name:                                 hello_bundle_migration_versions
        >> Migrations Namespace:                               Application\HelloBundle\DoctrineMigrations
        >> Migrations Directory:                               /path/to/symfony-sandbox/src/Bundle/HelloBundle/DoctrineMigrations
        >> Current Version:                                    0
        >> Latest Version:                                     0
        >> Executed Migrations:                                0
        >> Available Migrations:                               0
        >> New Migrations:                                     0

Now we can start working with migrations by generating a new blank migration class:

    $ php console doctrine:migrations:generate --bundle="Application\HelloBundle"
    Generated new migration class to "/path/to/symfony-sandbox/src/Bundle/HelloBundle/DoctrineMigrations/Version20100621140655.php"

Have a look at the newly generated migration class and you will see something like the
following:

    namespace Application\HelloBundle\DoctrineMigrations;

    use Doctrine\DBAL\Migrations\AbstractMigration,
        Doctrine\DBAL\Schema\Schema;

    class Version20100621140655 extends AbstractMigration
    {
        public function up(Schema $schema)
        {

        }

        public function down(Schema $schema)
        {

        }
    }

If you were to run the `status` command for the `HelloBundle` it will show that you
have one new migration to execute:

    $ php console doctrine:migrations:status --bundle="Application\HelloBundle"

     == Configuration

       >> Name:                                               HelloBundle Migrations
       >> Configuration Source:                               manually configured
       >> Version Table Name:                                 hello_bundle_migration_versions
       >> Migrations Namespace:                               Application\HelloBundle\DoctrineMigrations
       >> Migrations Directory:                               /path/to/symfony-sandbox/src/Application/HelloBundle/DoctrineMigrations
       >> Current Version:                                    0
       >> Latest Version:                                     2010-06-21 14:06:55 (20100621140655)
       >> Executed Migrations:                                0
       >> Available Migrations:                               1
       >> New Migrations:                                     1

    == Migration Versions

       >> 2010-06-21 14:06:55 (20100621140655)                not migrated
    
Now you can add some migration code to the `up()` and `down()` methods and migrate:

    $ php console doctrine:migrations:migrate --bundle="Application\HelloBundle"

You can read more about the Doctrine Database Migrations on the projects
[documentation](http://www.doctrine-project.org/projects/migrations/2.0/docs/en).

## Advanced DBAL

You can also specify some additional configurations on a connection but they are not
required:

    # ...

    doctrine.dbal:
      # ...

      host:                 localhost
      port:                 ~
      path:                 %kernel.data_dir%/symfony.sqlite
      event_manager_class:  Doctrine\Common\EventManager
      configuration_class:  Doctrine\DBAL\Configuration
      wrapper_class:        ~
      options:              []

If you want to configure multiple connections you can do so by simply listing them under
the key named `connections`:

    doctrine.dbal:
      default_connection:       default
      connections:
        default:
          dbname:               Symfony2
          user:                 root
          password:             null
          host:                 localhost
        customer:
          dbname:               customer
          user:                 root
          password:             null
          host:                 localhost

If you have defined multiple connections you can use the `getDatabaseConnection()` as well
but you must pass it an argument with the name of the connection you want to get:

    class UserController extends DoctrineController
    {
        public function indexAction()
        {
            $conn = $this->container->getDatabaseConnection('customer');
        }
    }

## Advanced ORM

Just like the DBAL, if you have configured multiple `EntityManager` instances and want to
get a specific one you can use the `getEntityManager()` method by just passing it an argument
that is the name of the `EntityManager` you want:

    class UserController extends DoctrineController
    {
        public function indexAction()
        {
            $em = $this->container->getEntityManager('customer');
        }
    }