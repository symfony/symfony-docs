.. index::
   single: Configuration; Doctrine ORM
   single: Doctrine; ORM Configuration

Configuration
=============

.. code-block:: yaml

    doctrine.orm:
      default_entity_manager:   default
      cache_driver:             apc           # array, apc, memcache, xcache
      entity_managers:
        default:
          connection:           default
        customer:
          connection:           customer

Just like the DBAL, if you have configured multiple ``EntityManager`` instances and want to
get a specific one you can use the ``getEntityManager()`` method by just passing it an argument
that is the name of the ``EntityManager`` you want::

    class UserController extends Controller
    {
        public function indexAction()
        {
            $em = $this->container->getService('doctrine.orm.customer_entity_manager');
        }
    }

Now the scenario arrises where you want to change your mapping information and
update your development database schema without blowing away everything and
losing your existing data. So first lets just add a new property to our ``User``
entity::

    namespace Application\HelloBundle\Entities;

    /** @Entity */
    class User
    {
        /** @Column(type="string") */
        protected $new;

        // ...
    }

Once you've done that, to get your database schema updated with the new column
you just need to run the following command:

    $ php hello/console doctrine:schema:update

Now your database will be updated and the new column added to the database
table.