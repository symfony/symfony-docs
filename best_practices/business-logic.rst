Organizing Your Business Logic
==============================

In computer software, **business logic** or domain logic is "the part of the
program that encodes the real-world business rules that determine how data can
be created, displayed, stored, and changed" (read `full definition`_).

In Symfony applications, business logic is all the custom code you write for
your app that's not specific to the framework (e.g. routing and controllers).
Domain classes, Doctrine entities and regular PHP classes that are used as
services are good examples of business logic.

For most projects, you should store all your code inside the ``src/`` directory.
Inside here, you can create whatever directories you want to organize things:

.. code-block:: text

    symfony-project/
    ├─ config/
    ├─ public/
    ├─ src/
    │  └─ Utils/
    │     └─ MyClass.php
    ├─ tests/
    ├─ var/
    └─ vendor/

.. _services-naming-and-format:

Services: Naming and Configuration
----------------------------------

.. best-practice::

    Use autowiring to automate the configuration of application services.

:doc:`Service autowiring </service_container/autowiring>` is a feature provided
by Symfony's Service Container to manage services with minimal configuration.
It reads the type-hints on your constructor (or other methods) and automatically
passes you the correct services. It can also add :doc:`service tags </service_container/tags>`
to the services needed them, such as Twig extensions, event subscribers, etc.

The blog application needs a utility that can transform a post title (e.g.
"Hello World") into a slug (e.g. "hello-world") to include it as part of the
post URL. Let's create a new ``Slugger`` class inside ``src/Utils/``:

.. code-block:: php

    // src/Utils/Slugger.php
    namespace App\Utils;

    class Slugger
    {
        public function slugify(string $value): string
        {
            // ...
        }
    }

If you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
this class is auto-registered as a service whose ID is ``App\Utils\Slugger`` (or
simply ``Slugger::class`` if the class is already imported in your code).

Traditionally, the naming convention for a service was a short, but unique
snake case key - e.g. ``app.utils.slugger``. But for most services, you should now
use the class name.

.. best-practice::

    The id of your application's services should be equal to their class name,
    except when you have multiple services configured for the same class (in that
    case, use a snake case id).

Now you can use the custom slugger in any other service or controller class,
such as the ``AdminController``:

.. code-block:: php

    use App\Utils\Slugger;

    public function create(Request $request, Slugger $slugger)
    {
        // ...

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $slugger->slugify($post->getTitle());
            $post->setSlug($slug);

            // ...
        }
    }

Services can also be :ref:`public or private <container-public>`. If you use the
:ref:`default services.yaml configuration <service-container-services-load-example>`,
all services are private by default.

.. best-practice::

    Services should be ``private`` whenever possible. This will prevent you from
    accessing that service via ``$container->get()``. Instead, you will need to use
    dependency injection.

Service Format: YAML
--------------------

In the previous section, YAML was used to define the service.

.. best-practice::

    Use the YAML format to define your own services.

This is controversial, and in our experience, YAML and XML usage is evenly
distributed among developers, with a slight preference towards YAML.
Both formats have the same performance, so this is ultimately a matter of
personal taste.

We recommend YAML because it's friendly to newcomers and concise. You can
of course use whatever format you like.

Using a Persistence Layer
-------------------------

Symfony is an HTTP framework that only cares about generating an HTTP response
for each HTTP request. That's why Symfony doesn't provide a way to talk to
a persistence layer (e.g. database, external API). You can choose whatever
library or strategy you want for this.

In practice, many Symfony applications rely on the independent
`Doctrine project`_ to define their model using entities and repositories.
Just like with business logic, we recommend storing Doctrine entities in the
AppBundle.

The three entities defined by our sample blog application are a good example:

.. code-block:: text

    symfony-project/
    ├─ ...
    └─ src/
       └─ Entity/
          ├─ Comment.php
          ├─ Post.php
          └─ User.php

Doctrine Mapping Information
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Doctrine entities are plain PHP objects that you store in some "database".
Doctrine only knows about your entities through the mapping metadata configured
for your model classes. Doctrine supports four metadata formats: YAML, XML,
PHP and annotations.

.. best-practice::

    Use annotations to define the mapping information of the Doctrine entities.

Annotations are by far the most convenient and agile way of setting up and
looking for mapping information:

.. code-block:: php

    namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Doctrine\Common\Collections\ArrayCollection;

    /**
     * @ORM\Entity
     */
    class Post
    {
        const NUM_ITEMS = 10;

        /**
         * @ORM\Id
         * @ORM\GeneratedValue
         * @ORM\Column(type="integer")
         */
        private $id;

        /**
         * @ORM\Column(type="string")
         */
        private $title;

        /**
         * @ORM\Column(type="string")
         */
        private $slug;

        /**
         * @ORM\Column(type="text")
         */
        private $content;

        /**
         * @ORM\Column(type="string")
         */
        private $authorEmail;

        /**
         * @ORM\Column(type="datetime")
         */
        private $publishedAt;

        /**
         * @ORM\OneToMany(
         *      targetEntity="Comment",
         *      mappedBy="post",
         *      orphanRemoval=true
         * )
         * @ORM\OrderBy({"publishedAt" = "ASC"})
         */
        private $comments;

        public function __construct()
        {
            $this->publishedAt = new \DateTime();
            $this->comments = new ArrayCollection();
        }

        // getters and setters ...
    }

All formats have the same performance, so this is once again ultimately a
matter of taste.

Data Fixtures
~~~~~~~~~~~~~

As fixtures support is not enabled by default in Symfony, you should execute
the following command to install the Doctrine fixtures bundle:

.. code-block:: terminal

    $ composer require "doctrine/doctrine-fixtures-bundle"

Then, this bundle is enabled automatically, but only for the ``dev`` and
``test`` environments:

.. code-block:: php

    // config/bundles.php

    return [
        // ...
        Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle::class => ['dev' => true, 'test' => true],
    ];

We recommend creating just *one* `fixture class`_ for simplicity, though
you're welcome to have more if that class gets quite large.

Assuming you have at least one fixtures class and that the database access
is configured properly, you can load your fixtures by executing the following
command:

.. code-block:: terminal

    $ php bin/console doctrine:fixtures:load

    Careful, database will be purged. Do you want to continue Y/N ? Y
      > purging database
      > loading App\DataFixtures\ORM\LoadFixtures

Coding Standards
----------------

The Symfony source code follows the `PSR-1`_ and `PSR-2`_ coding standards that
were defined by the PHP community. You can learn more about
:doc:`the Symfony Coding standards </contributing/code/standards>` and even
use the `PHP-CS-Fixer`_, which is a command-line utility that can fix the
coding standards of an entire codebase in a matter of seconds.

.. _`full definition`: https://en.wikipedia.org/wiki/Business_logic
.. _`Doctrine project`: http://www.doctrine-project.org/
.. _`fixture class`: https://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html#writing-simple-fixtures
.. _`PSR-1`: http://www.php-fig.org/psr/psr-1/
.. _`PSR-2`: http://www.php-fig.org/psr/psr-2/
.. _`PHP-CS-Fixer`: https://github.com/FriendsOfPHP/PHP-CS-Fixer
