Configuration
=============

Configuration usually involves different application parts (such as infrastructure
and security credentials) and different environments (development, production).
That's why Symfony recommends that you split the application configuration into
three parts.

.. _config-parameters.yml:

Infrastructure-Related Configuration
------------------------------------

.. best-practice::

    Define the infrastructure-related configuration options in the
    ``app/config/parameters.yml`` file.

The default ``parameters.yml`` file follows this recommendation and defines the
options related to the database and mail server infrastructure:

.. code-block:: yaml

    # app/config/parameters.yml
    parameters:
        database_driver:   pdo_mysql
        database_host:     127.0.0.1
        database_port:     ~
        database_name:     symfony
        database_user:     root
        database_password: ~

        mailer_transport:  smtp
        mailer_host:       127.0.0.1
        mailer_user:       ~
        mailer_password:   ~

        # ...

These options aren't defined inside the ``app/config/config.yml`` file because
they have nothing to do with the application's behavior. In other words, your
application doesn't care about the location of your database or the credentials
to access to it, as long as the database is correctly configured.

.. _best-practices-canonical-parameters:

Canonical Parameters
~~~~~~~~~~~~~~~~~~~~

.. best-practice::

    Define all your application's parameters in the
    ``app/config/parameters.yml.dist`` file.

Since version 2.3, Symfony includes a configuration file called ``parameters.yml.dist``,
which stores the canonical list of configuration parameters for the application.

Whenever a new configuration parameter is defined for the application, you
should also add it to this file and submit the changes to your version control
system. Then, whenever a developer updates the project or deploys it to a server,
Symfony will check if there is any difference between the canonical
``parameters.yml.dist`` file and your local ``parameters.yml`` file. If there
is a difference, Symfony will ask you to provide a value for the new parameter
and it will add it to your local ``parameters.yml`` file.

Application-Related Configuration
---------------------------------

.. best-practice::

    Define the application behavior related configuration options in the
    ``app/config/config.yml`` file.

The ``config.yml`` file contains the options used by the application to modify
its behavior, such as the sender of email notifications, or the enabled
`feature toggles`_. Defining these values in ``parameters.yml`` file would
add an extra layer of configuration that's not needed because you don't need
or want these configuration values to change on each server.

The configuration options defined in the ``config.yml`` file usually vary from
one :doc:`environment </cookbook/configuration/environments>` to another. That's
why Symfony already includes ``app/config/config_dev.yml`` and ``app/config/config_prod.yml``
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

    # app/config/config.yml
    parameters:
        homepage.num_items: 10

If you've done something like this in the past, it's likely that you've in fact
*never* actually needed to change that value. Creating a configuration
option for a value that you are never going to configure just isn't necessary.
Our recommendation is to define these values as constants in your application.
You could, for example, define a ``NUM_ITEMS`` constant in the ``Post`` entity:

.. code-block:: php

    // src/AppBundle/Entity/Post.php
    namespace AppBundle\Entity;

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

    namespace AppBundle\Repository;

    use Doctrine\ORM\EntityRepository;
    use AppBundle\Entity\Post;

    class PostRepository extends EntityRepository
    {
        public function findLatest($limit = Post::NUM_ITEMS)
        {
            // ...
        }
    }

The only notable disadvantage of using constants for this kind of configuration
values is that you cannot redefine them easily in your tests.

Semantic Configuration: Don't Do It
-----------------------------------

.. best-practice::

    Don't define a semantic dependency injection configuration for your bundles.

As explained in :doc:`/cookbook/bundles/extension` article, Symfony bundles
have two choices on how to handle configuration: normal service configuration
through the ``services.yml`` file and semantic configuration through a special
``*Extension`` class.

Although semantic configuration is much more powerful and provides nice features
such as configuration validation, the amount of work needed to define that
configuration isn't worth it for bundles that aren't meant to be shared as
third-party bundles.

Moving Sensitive Options Outside of Symfony Entirely
----------------------------------------------------

When dealing with sensitive options, like database credentials, we also recommend
that you store them outside the Symfony project and make them available
through environment variables. Learn how to do it in the following article:
:doc:`/cookbook/configuration/external_parameters`

.. _`feature toggles`: https://en.wikipedia.org/wiki/Feature_toggle
.. _`constant() function`: http://twig.sensiolabs.org/doc/functions/constant.html
