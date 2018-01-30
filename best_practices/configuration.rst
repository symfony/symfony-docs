Configuration
=============

Configuration usually involves different application parts (such as infrastructure
and security credentials) and different environments (development, production).
That's why Symfony recommends that you split the application configuration into
three parts.

.. _config-parameters.yml:

Infrastructure-Related Configuration
------------------------------------

These are the options that change from one machine to another (e.g. from your
development machine to the production server) but which don't change the
application behavior.

.. best-practice::

    Define the infrastructure-related configuration options as environment
    variables. During development, use the ``.env`` file at the root of your
    project to set these.

By default, Symfony adds these types of options to the ``.env`` file when
installing new dependencies in the app:

.. code-block:: bash

    # .env
    ###> doctrine/doctrine-bundle ###
    DATABASE_URL=sqlite:///%kernel.project_dir%/var/data/blog.sqlite
    ###< doctrine/doctrine-bundle ###

    ###> symfony/swiftmailer-bundle ###
    MAILER_URL=smtp://localhost?encryption=ssl&auth_mode=login&username=&password=
    ###< symfony/swiftmailer-bundle ###

    # ...

These options aren't defined inside the ``config/services.yaml`` file because
they have nothing to do with the application's behavior. In other words, your
application doesn't care about the location of your database or the credentials
to access to it, as long as the database is correctly configured.

.. _best-practices-canonical-parameters:

Canonical Parameters
~~~~~~~~~~~~~~~~~~~~

.. best-practice::

    Define all your application's env vars in the ``.env.dist`` file.

Symfony includes a configuration file called ``.env.dist`` at the project root,
which stores the canonical list of environment variables for the application.

Whenever a new env var is defined for the application, you should also add it to
this file and submit the changes to your version control system so your
workmates can update their ``.env`` files.

Application-Related Configuration
---------------------------------

.. best-practice::

    Define the application behavior related configuration options in the
    ``config/services.yaml`` file.

The ``services.yaml`` file contains the options used by the application to
modify its behavior, such as the sender of email notifications, or the enabled
`feature toggles`_. Defining these values in ``.env`` file would add an extra
layer of configuration that's not needed because you don't need or want these
configuration values to change on each server.

The configuration options defined in the ``services.yaml`` may vary from one
:doc:`environment </configuration/environments>` to another. That's why Symfony
supports defining ``config/services_dev.yaml`` and ``config/services_prod.yaml``
files so that you can override specific values for each environment.

Constants vs Configuration Options
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

One of the most common errors when defining application configuration is to
create new options for values that never change, such as the number of items for
paginated results.

.. best-practice::

    Use constants to define configuration options that rarely change.

The traditional approach for defining configuration options has caused many
Symfony apps to include an option like the following, which would be used
to control the number of posts to display on the blog homepage:

.. code-block:: yaml

    # config/services.yaml
    parameters:
        homepage.num_items: 10

If you've done something like this in the past, it's likely that you've in fact
*never* actually needed to change that value. Creating a configuration
option for a value that you are never going to configure just isn't necessary.
Our recommendation is to define these values as constants in your application.
You could, for example, define a ``NUM_ITEMS`` constant in the ``Post`` entity::

    // src/Entity/Post.php
    namespace App\Entity;

    class Post
    {
        const NUM_ITEMS = 10;

        // ...
    }

The main advantage of defining constants is that you can use their values
everywhere in your application. When using parameters, they are only available
from places with access to the Symfony container.

Constants can be used for example in your Twig templates thanks to the
`constant() function`_:

.. code-block:: html+twig

    <p>
        Displaying the {{ constant('NUM_ITEMS', post) }} most recent results.
    </p>

And Doctrine entities and repositories can now easily access these values,
whereas they cannot access the container parameters:

.. code-block:: php

    namespace App\Repository;

    use App\Entity\Post;
    use Doctrine\ORM\EntityRepository;

    class PostRepository extends EntityRepository
    {
        public function findLatest($limit = Post::NUM_ITEMS)
        {
            // ...
        }
    }

The only notable disadvantage of using constants for this kind of configuration
values is that you cannot redefine them easily in your tests.

Parameter Naming
----------------

.. best-practice::

    The name of your configuration parameters should be as short as possible and
    should include a common prefix for the entire application.

Using ``app.`` as the prefix of your parameters is a common practice to avoid
collisions with Symfony and third-party bundles/libraries parameters. Then, use
just one or two words to describe the purpose of the parameter:

.. code-block:: yaml

    # config/services.yaml
    parameters:
        # don't do this: 'dir' is too generic and it doesn't convey any meaning
        app.dir: '...'
        # do this: short but easy to understand names
        app.contents_dir: '...'
        # it's OK to use dots, underscores, dashes or nothing, but always
        # be consistent and use the same format for all the parameters
        app.dir.contents: '...'
        app.contents-dir: '...'

----

Next: :doc:`/best_practices/business-logic`

.. _`feature toggles`: https://en.wikipedia.org/wiki/Feature_toggle
.. _`constant() function`: http://twig.sensiolabs.org/doc/functions/constant.html
