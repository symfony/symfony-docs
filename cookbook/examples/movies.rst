The Movies - n:m relations in practice
=================================================

Init a new project
---------------------

1. Download the `Symfony2 Standard Edition`_ without vendors.

2. Unzip/untar the distribution. It will create a folder called Symfony with
   your new project structure, config files, etc. Rename it to ``movies``.

3. Remove ``src/ACME`` sample code. Follow the instructions on the `Standard Edition Readme`_.

4. Append following contents to ``deps`` file:

    .. code-block:: text

        [doctrine-fixtures]
            git=http://github.com/doctrine/data-fixtures.git

        [DoctrineFixturesBundle]
            git=http://github.com/symfony/DoctrineFixturesBundle.git
            target=/bundles/Symfony/Bundle/DoctrineFixturesBundle

        [DoctrineExtensions]
            git=http://github.com/l3pp4rd/DoctrineExtensions.git

5. Download all of the third-party vendor libraries:

    .. code-block:: bash
    
        $ php bin/vendors install

    At this point ``movies`` folder consumes 106 MB.

6. Remove ``.git`` folders:

    .. code-block:: bash

        $ find -name .git -type d  -exec rm -fr {} \;

    Currently ``movies`` folder consumes 22 MB.


7. Open ``app/AppKernel.php`` and add ``DoctrineFixturesBundle``:

    .. code-block:: php

        $bundles = array(
            ...
            new Symfony\Bundle\DoctrineFixturesBundle\DoctrineFixturesBundle(),
        );

8. Open ``app/autoload.php`` and register ``DataFixtures`` and ``Gedm`` namespaces:

    .. code-block:: php

        $loader->registerNamespaces(array(
            'Symfony'          => array(__DIR__.'/../vendor/symfony/src', __DIR__.'/../vendor/bundles'),
            'Sensio'           => __DIR__.'/../vendor/bundles',
            'JMS'              => __DIR__.'/../vendor/bundles',
            'Doctrine\\Common\\DataFixtures' => __DIR__.'/../vendor/doctrine-fixtures/lib',
            'Doctrine\\Common' => __DIR__.'/../vendor/doctrine-common/lib',
            'Doctrine\\DBAL'   => __DIR__.'/../vendor/doctrine-dbal/lib',
            'Doctrine'         => __DIR__.'/../vendor/doctrine/lib',
            'Monolog'          => __DIR__.'/../vendor/monolog/src',
            'Assetic'          => __DIR__.'/../vendor/assetic/src',
            'Metadata'         => __DIR__.'/../vendor/metadata/src',
            'Gedm'             => __DIR__.'/../vendor/DoctrineExtensions/lib',
        ));

9. Create ``frontend`` bundle

    Execute:

    .. code-block:: bash
    
        $ php app/console generate:bundle

    After the prompt:

    .. code-block:: text

        Bundle namespace:

    type:

    .. code-block:: text

        My/FrontendBundle

    All other options set to default values.

Create empty database and configure database access
---------------------------------------------------

1. Execute following SQL:

    .. code-block:: sql

        drop schema if exists movies;
        create schema movies default character set utf8 collate utf8_general_ci;
        grant all on movies.* to moviesadmin@localhost identified by 'secretPASSWORD';
        flush privileges;

    It will create empty database ``movies`` and account ``moviesadmin``.

2. Define database connection parameters

    Open ``app/config/parameters.ini`` and insert databasename and accout settings:

    .. code-block:: text

        [parameters]
            database_driver   = pdo_mysql
            database_host     = localhost
            database_port     =
            database_name     = movies
            database_user     = moviesadmin
            database_password = secretPASSWORD

3. Configure sluggable service

    Append following code to ``app/config/config.yml``:

    .. code-block:: yaml

        services:
            my.listener:
                class:  Gedmo\Sluggable\SluggableListener
                tags:
                    - { name: doctrine.event_listener, event: onFlush }

Create data file
---------------------------------------------------

1. Create ``movies/data/`` folder.

2. Create ``movies/data/movies.xml`` file with following contents:

    .. code-block:: xml

        <?xml version="1.0" encoding="utf-8"?>
        <movies>
            <movie>
                <title>The Getaway</title>
                <actors>
                    <actor>
                        <name>Steve</name>
                        <surname>McQueen</surname>
                    </actor>
                    <actor>
                        <name>Ben</name>
                        <surname>Johnson</surname>
                    </actor>
                    <actor>
                        <name>Ali</name>
                        <surname>MacGraw</surname>
                    </actor>
                </actors>
            </movie>
            <movie>
                <title>The Magnificent Seven</title>
                <actors>
                    <actor>
                        <name>Yul</name>
                        <surname>Brynner</surname>
                    </actor>
                    <actor>
                        <name>James</name>
                        <surname>Coburn</surname>
                    </actor>
                    <actor>
                        <name>Steve</name>
                        <surname>McQueen</surname>
                    </actor>
                </actors>
            </movie>
            <movie>
                <title>Cross of Iron</title>
                <actors>
                    <actor>
                        <name>James</name>
                        <surname>Coburn</surname>
                    </actor>
                    <actor>
                        <name>James</name>
                        <surname>Madson</surname>
                    </actor>
                </actors>
            </movie>
        </movies>

Create and customize entity classes
---------------------------------------------------

1. Create ``src/My/FrontendBundle/Entity/Movie.php`` class. Execute:

    .. code-block:: bash
    
        $ php app/console generate:doctrine:entity

    Set the entity shortcut name to ``MyFrontendBundle:Movie`` and add one field
    named ``title`` (type: string, length: 255).

    All other options set to default values.

2. Create ``src/My/FrontendBundle/Entity/Actor.php`` class. Execute:

    .. code-block:: bash
    
        $ php app/console generate:doctrine:entity

    Set the entity shortcut name to ``MyFrontendBundle:Actor`` and add two fields:
    name and surname. Both of them are strings with max length set to 255.

    All other options set to default values.

3. Define sluggable behaviour for ``Movie`` entity

    Inside ``Movie.php`` file add following use instruction:

    .. code-block:: php-annotations

        use Gedmo\Mapping\Annotation as Gedmo;

    and annotations:

    .. code-block:: php-annotations

        /**
         * @var string $title
         *
         * @gedmo:Sluggable
         * @ORM\Column(name="title", type="string", length=255, unique=true)
         */
        private $title;

        /**
         * @var string $slug
         *
         * @Gedmo\Slug(fields={"title"})
         * @ORM\Column(length=255, unique=true)
         */
        private $slug;

4. Define sluggable behaviour for ``Actor`` entity

    Inside ``Actor.php`` file add following use instruction:

    .. code-block:: php-annotations

        use Gedmo\Mapping\Annotation as Gedmo;

    and annotations:

    .. code-block:: php-annotations

        /**
         * @var string $slug
         *
         * @Gedmo\Slug(fields={"name", "surname"})
         * @ORM\Column(length=255, unique=true)
         */
        private $slug;        

5. Add annotation for ``MovieRepository`` class

    Modify ``@Entity`` annotation of ``Movie`` class:

    .. code-block:: php-annotations

        /**
         * My\FrontendBundle\Entity\Movie
         *
         * @ORM\Table()
         * @ORM\Entity(repositoryClass="My\FrontendBundle\Entity\MovieRepository")
         */        

6. Add annotation for ``ActorRepository`` class

    Modify ``@Entity`` annotation of ``Actor`` class:

    .. code-block:: php-annotations

        /**
         * My\FrontendBundle\Entity\Actor
         *
         * @ORM\Table()
         * @ORM\Entity(repositoryClass="My\FrontendBundle\Entity\ActorRepository")
         */

7. Define bidirectional n:m association for ``Movie`` and ``Actor`` entities

    Inside ``Movie.php`` file add property:

    .. code-block:: php-annotations

        /**
         * n:m association with Actor entity
         *
         * @ORM\ManyToMany(targetEntity="Actor", inversedBy="movies")
         * @ORM\JoinTable(name="movie_has_actor")
         * @ORM\OrderBy({"surname" = "ASC", "name" = "ASC"})
         */
        private $actors;

    Inside ``Actor.php`` file add property:

    .. code-block:: php-annotations

        /**
         * n:m association with Movie entity
         *
         * @ORM\ManyToMany(targetEntity="Movie", mappedBy="actors")
         * @ORM\OrderBy({"title" = "ASC"})
         */
        protected $movies;

8. Add ``__toString()`` methotd for ``Movie`` entity

    Inside ``Movie.php`` add following method:

    .. code-block:: php-annotations

        /**
         * Converts Movie entity to string
         */
        public function __toString()
        {
            return $this->getTitle();
        }

9. Add ``__toString()`` methotd for ``Actor`` entity

    Inside ``Actor.php`` add following method:

    .. code-block:: php-annotations

        /**
         * Converts Actor entity to string
         */
        public function __toString()
        {
            return $this->getName() . ' ' . $this->getSurname();
        }

10. Generate set/get methods and repository classes

    Execute:

    .. code-block:: bash
    
        $ php app/console doctrine:generate:entities My

11. Modify ``addMovie()`` method of ``Actor`` entity

    Inside ``Actor.php`` modify ``addMovie()`` method:

    .. code-block:: php-annotations

        /**
         * Add movies
         *
         * @param My\FrontendBundle\Entity\Movie $movies
         */
        public function addMovie(\My\FrontendBundle\Entity\Movie $movies)
        {
            $movies->addActor($this);
            $this->movies[] = $movies;
        }

12. Customize ``MovieRepository`` class

    Inside ``MovieRepository.php`` override ``findAll()`` method:

    .. code-block:: php-annotations

        class MovieRepository extends EntityRepository
        {

            /**
             * Get all movies
             *
             * @return Doctrine\Common\Collections\Collection
             */
            public function findAll()
            {
                return $this->findBy(array(), array('title' => 'ASC'));
            }

        }

13. Customize ``ActorRepository`` class

    Inside ``ActorRepository.php`` override ``findAll()`` method:

    .. code-block:: php-annotations

        class ActorRepository extends EntityRepository
        {

            /**
             * Get all actors
             *
             * @return Doctrine\Common\Collections\Collection
             */
            public function findAll()
            {
                return $this->findBy(array(), array('surname' => 'ASC', 'name' => 'ASC'));
            }

        }

14. Create database tables

    Execute:

    .. code-block:: bash
    
        $ php app/console doctrine:schema:update --force

    Your database should now contain three tables: ``actor``, ``movie``, ``movie_has_actor``.

Fill the database with records
---------------------------------------------------

1. Create ``src/My/FrontendBundle/DataFixtures/ORM/LoadData.php``

    .. code-block:: php-annotations

        namespace My\FrontendBundle\DataFixtures\ORM;

        use Doctrine\Common\DataFixtures\FixtureInterface;
        use My\FrontendBundle\Entity\Actor;
        use My\FrontendBundle\Entity\Movie;

        class LoadData implements FixtureInterface
        {
            public function load($manager)
            {
                $xml = simplexml_load_file('data/movies.xml');
                foreach ($xml->movie as $m) {
                    $Movie = new Movie();
                    $Movie->setTitle($m->title);
                    $manager->persist($Movie);
                    foreach ($m->actors->actor as $a) {
                        $Actor = $manager
                            ->getRepository('MyFrontendBundle:Actor')
                            ->findOneBy(array(
                                'name' => $a->name, 
                                'surname' => $a->surname
                            ));
                        if (!$Actor) {
                            $Actor = new Actor();
                            $Actor->setName($a->name);
                            $Actor->setSurname($a->surname);
                            $manager->persist($Actor);
                        };
                        $Actor->addMovie($Movie);
                        $manager->flush();
                    }
                }
                $manager->flush();
            }
        }

2. Load datafixtures

    Execute:

    .. code-block:: bash
    
        $ php app/console doctrine:fixtures:load

    The database ``movies`` should now contain records.

Create controllers and views
---------------------------------------------------

1. Customize ``app/Resources/views/base.html.twig``

    .. code-block:: html+jinja

        <!DOCTYPE html>
        <html>
          <head>
            <title>{% block title %}Movies/Actors{% endblock %}</title>
            <meta charset="UTF-8" />
          </head>
        <body>
            {% block body %}{% endblock %}
        </body>
        </html>

2. Create ``app/Resources/views/layout.html.twig``

    .. code-block:: html+jinja

        {% extends '::base.html.twig' %}

        {% block body %}
            <h1>Movies/Actors</h1>

            <ul>
                <li><a href="{{ path('movie') }}">Movies</a></li>
                <li><a href="{{ path('actor') }}">Actors</a></li>
            </ul>

            {% block content %}
            {% endblock %}

        {% endblock %}

3. Create ``My/FrontendBundle/Controller/ActorController.php``

    .. code-block:: php-annotations

        <?php

        namespace My\FrontendBundle\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\Controller;
        use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
        use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

        class ActorController extends Controller
        {

            /**
             * Lists all Actor entities.
             *
             * @Route("/", name="actor")
             * @Template()
             */
            public function indexAction()
            {
                $em = $this->getDoctrine()->getEntityManager();

                $entities = $em->getRepository('MyFrontendBundle:Actor')->findAll();

                return array('entities' => $entities);
            }

        }

4. Create ``My/FrontendBundle/Controller/MovieController.php``

    .. code-block:: php-annotations

        <?php

        namespace My\FrontendBundle\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\Controller;
        use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
        use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

        class MovieController extends Controller
        {

            /**
             * Lists all Movie entities.
             *
             * @Route("/movie", name="movie")
             * @Template()
             */
            public function indexAction()
            {
                $em = $this->getDoctrine()->getEntityManager();

                $entities = $em->getRepository('MyFrontendBundle:Movie')->findAll();

                return array('entities' => $entities);
            }

        }

5. Create ``My/FrontendBundle/Resources/views/Actor/index.html.twig``

    .. code-block:: html+jinja

        {% extends '::layout.html.twig' %}

        {% block content %}

        <h2>Actors</h2>

        <ul>
            {% for actor in entities %}
                <li>
                    {{ actor }}
                    <ul>
                        {% for movie in actor.movies %}
                            <li>{{ movie }}</li>
                        {% endfor %}
                    </ul>

                </li>
            {% endfor %}
        </ul>

        {% endblock %}

6. Create ``My/FrontendBundle/Resources/views/Movie/index.html.twig``

    .. code-block:: html+jinja

        {% extends '::layout.html.twig' %}

        {% block content %}

        <h2>Movies</h2>

        <ul>
            {% for movie in entities %}
                <li>
                    {{ movie }}
                    <ul>
                        {% for actor in movie.actors %}
                            <li>{{ actor }}</li>
                        {% endfor %}
                    </ul>

                </li>
            {% endfor %}
        </ul>

        {% endblock %}

Test the application
---------------------------------------------------

1. Visit one of the addresses:

    .. code-block:: text
        http://localhost/path/to/movies/web/
        http://localhost/path/to/movies/web/app_dev.php

    Enjoy!

