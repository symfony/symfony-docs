.. index::
   single: Doctrine; Creating fixtures in Symfony2

How to create fixtures in Symfony2
==================================

Fixtures are used to load the database with a set of data. This data can either be for testing or could be the initial data required for the application to run smoothly. Symfony2 has no built in way to manage fixtures but Doctrine2 has a library to help you write fixtures for ORM and ODM.

Setup and Configuration
-----------------------

If you don't have `Doctrine Data Fixtures`_ configured with Symfony2 yes, follow these steps to do so.

Add the following to ``bin/vendors.sh``

.. code-block:: bash

    #Doctrine Fixtures
    install_git doctrine-fixtures git://github.com/doctrine/data-fixtures.git

Update vendors and rebuild the bootstrap file

.. code-block:: bash

    bin/vendors.sh
    bin/build_bootstrap.php

As the final step in configuration, you have to register the namespace in ``app/autoload.php``

.. code-block:: php

    'Doctrine\\Common\\DataFixtures'    => __DIR__.'/../vendor/doctrine-fixtures/lib',
    'Doctrine\\Common'                  => __DIR__.'/../vendor/doctrine-common/lib',

Note that namespaces are registered with preference to the first match. Make sure ``Doctrine\Common`` is registered after ``Doctrine\\Common\\DataFixtures``.

Simple Fixtures
---------------

The ideal place to store your fixtures is inside ``Vendor/MyBundle/DataFixtures/ORM`` and ``Vendor/MyBundle/DataFixtures/ODM`` respectively for ORM and ODM.

In this tutorial we will assume you are using ORM. If you are using ODM make the changes as required.

In our first fixture we will add a default user to the table of ``User`` entity.

.. code-block:: php

    <?php
    
    //Vendor/MyBundle/DataFixtures/ORM/LoadUserData.php
    namespace Vendor\MyBundle\DataFixtures\ORM;

    use Doctrine\Common\DataFixtures\FixtureInterface;
    use Vendor\MyBundle\Entity\User; //Modify this to use your entity

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

Writing fixtures this way is quite easy and simple but is not sufficient when you are building something serious. The most serious limitation is that you can not share objects between fixtures. Lets see how we can overcome this limitation in the next section.

Sharing Objects Between Fixtures
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
            $manager->persist($this->getReference('admin-user'));
            $manager->persist($this->getReference('admin-group'));
        
            $userGroupAdmin = new UserGroup();
            $userGroupAdmin->setUser($this->getReference('admin-user'));
            $userGroupAdmin->setGroup($this->getReference('admin-group'));

            $manager->persist($userGroupAdmin);
            $manager->flush();
        }

        public function getOrder()
        {
            return 3; // the order in which fixtures will be loaded
        }    
    }

A brief explanation on how this works.

The fixtures will be executed in the ascending order of the value returned by ``getOrder()``. 
Any object that is set with the ``setReference`` method and can be accessed with ``getReference`` in fixtures, which are of higher order.
Also remember that, in order to use a reference with the entity manager, it has to first persisted.

.. _`Doctrine Data Fixtures`: https://github.com/doctrine/data-fixtures