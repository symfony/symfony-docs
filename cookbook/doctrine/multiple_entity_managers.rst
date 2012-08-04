.. index::
   single: Doctrine; Multiple entity managers

How to work with Multiple Entity Managers
=========================================

You can use multiple entity managers in a Symfony2 application. This is
necessary if you are using different databases or even vendors with entirely
different sets of entities. In other words, one entity manager that connects
to one database will handle some entities while another entity manager that
connects to another database might handle the rest.

.. note::

    Using multiple entity managers is pretty easy, but more advanced and not
    usually required. Be sure you actually need multiple entity managers before
    adding in this layer of complexity.

The following configuration code shows how you can configure two entity managers:

.. configuration-block::

    .. code-block:: yaml

        doctrine:
            orm:
                default_entity_manager:   default
                entity_managers:
                    default:
                        connection:       default
                        mappings:
                            AcmeDemoBundle: ~
                            AcmeStoreBundle: ~
                    customer:
                        connection:       customer
                        mappings:
                            AcmeCustomerBundle: ~

In this case, you've defined two entity managers and called them ``default``
and ``customer``. The ``default`` entity manager manages entities in the
``AcmeDemoBundle`` and ``AcmeStoreBundle``, while the ``customer`` entity
manager manages entities in the ``AcmeCustomerBundle``.

When working with multiple entity managers, you should be explicit about which
entity manager you want. If you *do* omit the entity manager's name when you
update your schema, the default (i.e. ``default``) is used::

    # Play only with "default" mappings
    php app/console doctrine:schema:update --force

    # Play only with "customer" mappings
    php app/console doctrine:schema:update --force --em=customer

If you *do* omit the entity manager's name when asking for it,
the default entity manager (i.e. ``default``) is returned::

    class UserController extends Controller
    {
        public function indexAction()
        {
            // both return the "default" em
            $em = $this->get('doctrine')->getManager();
            $em = $this->get('doctrine')->getManager('default');
            
            $customerEm =  $this->get('doctrine')->getManager('customer');
        }
    }

You can now use Doctrine just as you did before - using the ``default`` entity
manager to persist and fetch entities that it manages and the ``customer``
entity manager to persist and fetch its entities.

The same applies to repository call::

    class UserController extends Controller
    {
        public function indexAction()
        {
            // Retrieves a repository managed by the "default" em
            $products = $this->get('doctrine')
                             ->getRepository('AcmeStoreBundle:Product')
                             ->findAll();

            // Explicit way to deal with the "default" em
            $products = $this->get('doctrine')
                             ->getRepository('AcmeStoreBundle:Product', 'default')
                             ->findAll();

            // Retrieves a repository managed by the "customer" em
            $customers = $this->get('doctrine')
                              ->getRepository('AcmeCustomerBundle:Customer', 'customer')
                              ->findAll();
        }
    }
