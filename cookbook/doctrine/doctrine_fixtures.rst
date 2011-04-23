.. index::
   single: Doctrine; Creating fixtures in Symfony2

How to create Fixtures in Symfony2
==================================

Fixtures are used to load a controlled set of data into a database. This
data can be used for testing or could be the initial data required for the
application to run smoothly. Symfony2 has no built in way to manage fixtures
but Doctrine2 has a library to help you write fixtures for the Doctrine
:doc:`ORM</book/doctrine/orm/overview>` or :doc:`ODM</book/doctrine/mongodb-odm/overview>`.

Setup and Configuration
-----------------------

If you don't have the `Doctrine Data Fixtures`_ library configured with Symfony2
yet, follow these steps to do so.

Add the following to ``bin/vendors.sh``, right after the "Monolog" entry:

.. code-block:: text

    # Doctrine Fixtures
    install_git doctrine-fixtures https://github.com/doctrine/data-fixtures.git

And also, the following after the "WebConfiguratorBundle" entry:

.. code-block:: text

    # DoctrineFixturesBundle
    install_git DoctrineFixturesBundle https://github.com/symfony/DoctrineFixturesBundle.git

Update vendors and rebuild the bootstrap file:

.. code-block:: bash

    $ bin/vendors.sh bin/build_bootstrap.php

If everything worked, the ``doctrine-fixtures`` library can now be found
at ``vendor/doctrine-fixtures``.

Register the ``Doctrine\Common\DataFixtures`` namespace in ``app/autoload.php``.

.. code-block:: php

    // ...
    $loader->registerNamespaces(array(
        // ...
        'Doctrine\\Common\\DataFixtures' => __DIR__.'/../vendor/doctrine-fixtures/lib',
        'Doctrine\\Common' => __DIR__.'/../vendor/doctrine-common/lib',
        // ...
    ));

.. caution::

    Be sure to register the new namespace *before* ``Doctrine\Common``. Otherwise,
    Symfony will look for data fixture classes inside the ``Doctrine\Common``
    directory. Symfony's autoloader always looks for a class inside the directory
    of the first matching namespace, so more specific namespaces should always
    come first.

Finally, register the Bundle ``DoctrineFixturesBundle`` in ``app/AppKernel.php``.

.. code-block:: php

    // ...
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Symfony\Bundle\DoctrineFixturesBundle\DoctrineFixturesBundle(),
            // ...
        );
        // ...
    }

Writing Simple Fixtures
-----------------------

Doctrine2 fixtures are PHP classes where you can create objects and persist
them to the database. Like all classes in Symfony2, fixtures should live inside
one of your application bundles.

For a bundle located at ``src/VendorName/MyBundle``, the fixture classes
should live inside ``src/VendorName/MyBundle/DataFixtures/ORM`` or
``src/VendorName/MyBundle/DataFixtures/ODM`` respectively for the ORM and ODM,
This tutorial assumes that you are using the ORM - but fixtures can be added
just as easily if you're using the ODM.

Imagine that you have a ``User`` class, and you'd like to load one ``User``
entry:

.. code-block:: php

    <?php

    // src/VendorName/MyBundle/DataFixtures/ORM/LoadUserData.php
    namespace VendorName\MyBundle\DataFixtures\ORM;

    use Doctrine\Common\DataFixtures\FixtureInterface;
    use VendorName\MyBundle\Entity\User;

    class LoadUserData implements FixtureInterface
    {
        public function load($manager)
        {
            $userAdmin = new User();
            $userAdmin->setUsername('admin');
            $userAdmin->setPassword('test');

            $manager->persist($userAdmin);
            $manager->flush();
        }
    }

In Doctrine2, fixtures are just objects where you load data by interacting
with your entities as you normally do. This allows you to create the exact
fixtures you need for your application.

The most serious limitation is that you cannot share objects between fixtures.
Later, you'll see how to overcome this limitation.

Executing Fixtures
------------------

Once your fixtures have been written, you can load them via the command
line by using the ``doctrine:fixtures:load`` command:

.. code-block:: bash

    $ php app/console doctrine:fixtures:load

If you're using the ODM, use the ``doctrine:mongodb:fixtures:load`` command instead:

.. code-block:: bash

    $ php app/console doctrine:mongodb:data:load

The task will look inside the ``DataFixtures/ORM`` (or ``DataFixtures/ODM``
for the ODM) directory of each bundle and execute each class that implements
the ``FixtureInterface``.

Both commands come with a few options:

* ``--fixtures=/path/to/fixture`` - Use this option to manually specify the
  directory or file where the fixtures classes should be loaded;

* ``--append`` - Use this flag to append data instead of deleting data before
  loading it (deleting first is the default behavior);

* ``--em=manager_name`` - Manually specify the entity manager to use for
  loading the data.

.. note::

   If using the ``doctrine:mongodb:fixtures:load`` task, replace the ``--em=``
   option with ``--dm=`` to manually specify the document manager.

A full example use might look like this:

.. code-block:: bash

   $ php app/console doctrine:fixtures:load --fixtures=/path/to/fixture1 --fixtures=/path/to/fixture2 --append --em=foo_manager

Sharing Objects between Fixtures
--------------------------------

Writing a basic fixture is simple. But what if you have multiple fixture classes
and want to be able to refer to the data loaded in other fixture classes?
For example, what if you load a ``User`` object in one fixture, and then
want to refer to reference it in a different fixture in order to assign that
user to a particular group?

The Doctrine fixtures library handles this easily by allowing you to specify
the order in which fixtures are loaded.

.. code-block:: php

    // src/VendorName/MyBundle/DataFixtures/ORM/LoadUserData.php
    namespace VendorName\MyBundle\DataFixtures\ORM;

    use Doctrine\Common\DataFixtures\AbstractFixture;
    use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
    use VendorName\MyBundle\Entity\User;

    class LoadUserData extends AbstractFixture implements OrderedFixtureInterface
    {
        public function load($manager)
        {
            $userAdmin = new User();
            $userAdmin->setUsername('admin');
            $userAdmin->setPassword('test');

            $manager->persist($userAdmin);
            $manager->flush();

            $this->addReference('admin-user', $userAdmin);
        }

        public function getOrder()
        {
            return 1; // the order in which fixtures will be loaded
        }
    }

The fixture class now implements ``OrderedFixtureInterface``, which tells
Doctrine that you want to control the order of your fixtures. Create another
fixture class and make it load after ``LoadUserData`` by returning an order
of 2:

.. code-block:: php

    // src/VendorName/MyBundle/DataFixtures/ORM/LoadGroupData.php
    namespace VendorName\MyBundle\DataFixtures\ORM;

    use Doctrine\Common\DataFixtures\AbstractFixture;
    use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
    use VendorName\MyBundle\Entity\Group;

    class LoadGroupData extends AbstractFixture implements OrderedFixtureInterface
    {
        public function load($manager)
        {
            $groupAdmin = new Group();
            $groupAdmin->setGroupName('admin');

            $manager->persist($groupAdmin);
            $manager->flush();

            $this->addReference('admin-group', $groupAdmin);
        }

        public function getOrder()
        {
            return 2; // the order in which fixtures will be loaded
        }
    }

Both of the fixture classes extend ``AbstractFixture``, which allows you
to create objects and then set them as references so that they can be used
later in other fixtures. For example, the ``$userAdmin`` and ``$groupAdmin``
objects can be referenced later via the ``admin-user`` and ``admin-group``
references:

.. code-block:: php

    // src/VendorName/MyBundle/DataFixtures/ORM/LoadUserGroupData.php
    namespace VendorName\MyBundle\DataFixtures\ORM;

    use Doctrine\Common\DataFixtures\AbstractFixture;
    use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
    use VendorName\MyBundle\Entity\UserGroup;

    class LoadUserGroupData extends AbstractFixture implements OrderedFixtureInterface
    {
        public function load($manager)
        {
            $userGroupAdmin = new UserGroup();
            $userGroupAdmin->setUser($manager->merge($this->getReference('admin-user')));
            $userGroupAdmin->setGroup($manager->merge($this->getReference('admin-group')));

            $manager->persist($userGroupAdmin);
            $manager->flush();
        }

        public function getOrder()
        {
            return 3;
        }
    }

The fixtures will now be executed in the ascending order of the value returned
by ``getOrder()``. Any object that is set with the ``setReference()`` method
can be accessed via ``getReference()`` in fixture classes that have a higher
order.

Fixtures allow you to create any type of data you need via the normal PHP
interface for creating and persisting objects. By controlling the order of
fixtures and setting references, almost anything can be handled by fixtures.

.. _`Doctrine Data Fixtures`: https://github.com/doctrine/data-fixtures
