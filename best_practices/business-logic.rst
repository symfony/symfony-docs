Organizing Your Business Logic
==============================

In computer software, **business logic** or domain logic is "the part of the
program that encodes the real-world business rules that determine how data can
be created, displayed, stored, and changed" (read `full definition`_).

In Symfony applications, business logic is all the custom code you write for
your app that's not specific to the framework (e.g. routing and controllers).
Domain classes, Doctrine entities and regular PHP classes that are used as
services are good examples of business logic.

For most projects, you should store everything inside the AppBundle.
Inside here, you can create whatever directories you want to organize things:

.. code-block:: text

    symfony2-project/
    ├─ app/
    ├─ src/
    │  └─ AppBundle/
    │     └─ Utils/
    │        └─ MyClass.php
    ├─ tests/
    ├─ var/
    ├─ vendor/
    └─ web/

Storing Classes Outside of the Bundle?
--------------------------------------

But there's no technical reason for putting business logic inside of a bundle.
If you like, you can create your own namespace inside the ``src/`` directory
and put things there:

.. code-block:: text

    symfony2-project/
    ├─ app/
    ├─ src/
    │  ├─ Acme/
    │  │   └─ Utils/
    │  │      └─ MyClass.php
    │  └─ AppBundle/
    ├─ tests/
    ├─ var/
    ├─ vendor/
    └─ web/

.. tip::

    The recommended approach of using the ``AppBundle/`` directory is for
    simplicity. If you're advanced enough to know what needs to live in
    a bundle and what can live outside of one, then feel free to do that.

Services: Naming and Format
---------------------------

The blog application needs a utility that can transform a post title (e.g.
"Hello World") into a slug (e.g. "hello-world"). The slug will be used as
part of the post URL.

Let's create a new ``Slugger`` class inside ``src/AppBundle/Utils/`` and
add the following ``slugify()`` method:

.. code-block:: php

    // src/AppBundle/Utils/Slugger.php
    namespace AppBundle\Utils;

    class Slugger
    {
        public function slugify($string)
        {
            return preg_replace(
                '/[^a-z0-9]/', '-', strtolower(trim(strip_tags($string)))
            );
        }
    }

Next, define a new service for that class.

.. code-block:: yaml

    # app/config/services.yml
    services:
        # keep your service names short
        app.slugger:
            class: AppBundle\Utils\Slugger

Traditionally, the naming convention for a service involved following the
class name and location to avoid name collisions. Thus, the service
*would have been* called ``app.utils.slugger``. But by using short service names,
your code will be easier to read and use.

.. best-practice::

    The name of your application's services should be as short as possible,
    but unique enough that you can search your project for the service if
    you ever need to.

Now you can use the custom slugger in any controller class, such as the
``AdminController``:

.. code-block:: php

    public function createAction(Request $request)
    {
        // ...

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $this->get('app.slugger')->slugify($post->getTitle());
            $post->setSlug($slug);

            // ...
        }
    }

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

Service: No Class Parameter
---------------------------

You may have noticed that the previous service definition doesn't configure
the class namespace as a parameter:

.. code-block:: yaml

    # app/config/services.yml

    # service definition with class namespace as parameter
    parameters:
        slugger.class: AppBundle\Utils\Slugger

    services:
        app.slugger:
            class: '%slugger.class%'

This practice is cumbersome and completely unnecessary for your own services:

.. best-practice::

    Don't define parameters for the classes of your services.

This practice was wrongly adopted from third-party bundles. When Symfony
introduced its service container, some developers used this technique to easily
allow overriding services. However, overriding a service by just changing its
class name is a very rare use case because, frequently, the new service has
different constructor arguments.

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

    symfony2-project/
    ├─ ...
    └─ src/
       └─ AppBundle/
          └─ Entity/
             ├─ Comment.php
             ├─ Post.php
             └─ User.php

.. tip::

    If you're more advanced, you can of course store them under your own
    namespace in ``src/``.

Doctrine Mapping Information
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Doctrine Entities are plain PHP objects that you store in some "database".
Doctrine only knows about your entities through the mapping metadata configured
for your model classes. Doctrine supports four metadata formats: YAML, XML,
PHP and annotations.

.. best-practice::

    Use annotations to define the mapping information of the Doctrine entities.

Annotations are by far the most convenient and agile way of setting up and
looking for mapping information:

.. code-block:: php

    namespace AppBundle\Entity;

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

.. code-block:: bash

    $ composer require "doctrine/doctrine-fixtures-bundle"

Then, enable the bundle in ``AppKernel.php``, but only for the ``dev`` and
``test`` environments:

.. code-block:: php

    use Symfony\Component\HttpKernel\Kernel;

    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            $bundles = array(
                // ...
            );

            if (in_array($this->getEnvironment(), array('dev', 'test'))) {
                // ...
                $bundles[] = new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle();
            }

            return $bundles;
        }

        // ...
    }

We recommend creating just *one* `fixture class`_ for simplicity, though
you're welcome to have more if that class gets quite large.

Assuming you have at least one fixtures class and that the database access
is configured properly, you can load your fixtures by executing the following
command:

.. code-block:: bash

    $ php bin/console doctrine:fixtures:load

    Careful, database will be purged. Do you want to continue Y/N ? Y
      > purging database
      > loading AppBundle\DataFixtures\ORM\LoadFixtures

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
