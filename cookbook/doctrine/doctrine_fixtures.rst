Doctrine2 Fixtures with Symfony2
=========================

##Setup and Configuration

If you don't have Doctrine data-fixtures configured with Symfony2 follow the steps here.

Add the following to `bin/vendors.sh`

    #Doctrine Fixtures
    install_git doctrine-fixtures git://github.com/doctrine/data-fixtures.git

Update vendors and rebuild the bootstrap file

    bin/vendors.sh
    bin/build_bootstrap.php

As a final step in configuration, you have to register the namespace in `app/autoload.php`

    'Doctrine\\Common\\DataFixtures'    => __DIR__.'/../vendor/doctrine-fixtures/lib',

##Simple Fixtures

`TODO: Add a simple fixture, ORM and ODM`

##Sharing Objects Between Fixtures

    #Vendor/MyBundle/DataFixtures/ORM/LoadUserData.php

    <?php

    namespace Vendor\MyBundle\DataFixtures\ORM;

    use Vendor\MyBundle\Entity\User;
    use Doctrine\ORM\EntityManager;
    use Doctrine\Common\DataFixtures\AbstractFixture;
    use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

    class LoadUserData extends AbstractFixture implements OrderedFixtureInterface
    {
        public function load($manager)
        {
            $userKertz = new User();
            $userKertz->setUsername('kertz');
            $userKertz->setPassword('test');

            $manager->persist($userKertz);
            $manager->flush();
        
            $this->addReference('kertz-user', $userKertz);        
        }

        public function getOrder()
        {
            return 1; // the order in which fixtures will be loaded
        }    
    }



    #Vendor/MyBundle/DataFixtures/ORM/LoadGroupData.php

    <?php

    namespace Vendor\MyBundle\DataFixtures\ORM;

    use Vendor\MyBundle\Entity\Group;
    use Doctrine\ORM\EntityManager;
    use Doctrine\Common\DataFixtures\AbstractFixture;
    use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

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


    #Vendor/MyBundle/DataFixtures/ORM/LoadUserGroupData.php

    <?php

    namespace Vendor\MyBundle\DataFixtures\ORM;

    use Vendor\MyBundle\Entity\UserGroup;
    use Doctrine\ORM\EntityManager;
    use Doctrine\Common\DataFixtures\AbstractFixture;
    use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

    class LoadUserGroupData extends AbstractFixture implements OrderedFixtureInterface
    {
        public function load($manager)
        {
            $manager->persist($this->getReference($userKertz));
            $manager->persist($this->getReference($groupAdmin));
        
            $kertzGroup = new UserGroup();
            $kertzGroup->setUser($this->getReference($userKertz));
            $kertzGroup->setGroup($this->getReference($groupAdmin));

            $manager->persist($kertzGroup);
            $manager->flush();
        }

        public function getOrder()
        {
            return 3; // the order in which fixtures will be loaded
        }    
    }

A brief explanation on how this works.

The fixtures will be executed in the order of the value returned in getOrder().
