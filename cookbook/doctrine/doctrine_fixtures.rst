.. index::
   single: Doctrine; Creating fixtures in Symfony2

How to create Fixtures in Symfony2
==================================

Fixtures are used to load the database with a set of data. This data can
either be for testing or could be the initial data required for the
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
    install_git doctrine-fixtures git://github.com/doctrine/data-fixtures.git

Update vendors and rebuild the bootstrap file:

.. code-block:: bash

    $ bin/vendors.sh bin/build_bootstrap.php

If everything worked, the ``doctrine-fixtures`` library can now be found
at ``vendor/doctrine-fixtures``.

Finally, register the ``Doctrine\Common\DataFixtures`` namespace in ``app/autoload.php``.

.. code-block:: php

    // ...
    $loader->registerNamespaces(array(
        // ...
        'Doctrine\\Common\\DataFixtures' => __DIR__.'/../vendor/doctrine-fixtures/lib',
        'Doctrine\\Common' => __DIR__.'/../vendor/doctrine-common/lib',
        // ...
    ));

Be sure to register the new namespace *after* ``Doctrine\Common``. Otherwise,
Symfony will look for data fixture classes inside the ``Doctrine\Common``
directory. Symfony's autoloader always looks for a class inside the directory
of the first matching namespace, so more specific namespaces should always
come first.

Writing Simple Fixtures
-----------------------

The ideal place to store your fixtures is inside
``src/VendorName/MyBundle/DataFixtures/ORM`` and ``src/VendorName/MyBundle/DataFixtures/ODM``
respectively for the ORM and ODM. This tutorial assumes that you are using
the ORM - but fixtures can be added just as easily if you're using the ODM.

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
            $manager->flush()
        }
    }

In Doctrine2, fixtures are just objects where you load data by interacting
with your entities as you normal do. This allows you to create the exact
fixtures you want for your application.

The most serious limitation is that you can not share objects between fixtures.
Later, you'll see how to overcome this limitation.

Executing Fixtures
------------------

Once your fixtures have been written, you load the fixtures via the command
line via the ``doctrine:data:load`` command:

.. code-block:: bash

    $ php app/console doctrine:data:load

If you're using the ODM, use the ``doctrine:mongodb:data:load`` command instead:

.. code-block:: bash

    $ php app/console doctrine:mongodb:data:load

The task will look inside the ``DataFixtures/ORM`` (or ``DataFixtures/ODM``
for the ODM) directory of each bundle and execute each class that implements
the ``FixtureInterface``.

Both commands come with a few options:

* ``--fixtures=/path/to/fixture`` - Use this option to manually specify the
  directory or file where the fixtures classes should be loaded;

* ``--append`` - Use this flag to append data instead of deleting data before
  loading it (the default behavior);

* ``--em=manager_name`` - Manually specify the entity manager to use for
  loading the data.

.. note::

   If using the ``doctrine:mongodb:data:load`` task, replace the ``--em=``
   option with ``--dm=`` to manually specify the document manager.

A full example use might look like:

.. code-block:: bash

   $ php app/console doctrine:data:load --fixtures=/path/to/fixture1 --fixtures=/path/to/fixture2 --append --em=foo_manager

Sharing Objects between Fixtures
--------------------------------

.. code-block:: php

    <?php
    
    //Vendor/MyBundle/DataFixtures/ORM/LoadUserData.php
    namespace Vendor\MyBundle\DataFixtures\ORM;

    use Doctrine\Common\DataFixtures\AbstractFixture;
    use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
    use Vendor\MyBundle\Entity\User; //Modify this to use your entity

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


.. code-block:: php

    <?php
    
    //Vendor/MyBundle/DataFixtures/ORM/LoadGroupData.php
    namespace Vendor\MyBundle\DataFixtures\ORM;

    use Doctrine\Common\DataFixtures\AbstractFixture;
    use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
    use Vendor\MyBundle\Entity\Group; //Modify this to use your entity

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

.. code-block:: php

    <?php
    
    //Vendor/MyBundle/DataFixtures/ORM/LoadUserGroupData.php
    namespace Vendor\MyBundle\DataFixtures\ORM;

    use Doctrine\Common\DataFixtures\AbstractFixture;
    use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
    use Vendor\MyBundle\Entity\UserGroup; //Modify this to use your entity
    
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
            return 3; // the order in which fixtures will be loaded
        }    
    }

A brief explanation on how this works.

The fixtures will be executed in the ascending order of the value returned by
``getOrder()``. Any object that is set with the ``setReference`` method and
can be accessed via ``getReference`` in fixtures, which are of higher order.

.. _`Doctrine Data Fixtures`: https://github.com/doctrine/data-fixtures