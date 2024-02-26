Using Docker with Symfony
=========================

Can you use Docker with Symfony? Of course! And several tools exist to help,
depending on your needs.

Complete Docker Environment
---------------------------

If you'd like a complete Docker environment (i.e. where PHP, web server, database,
etc. are all in Docker), check out `https://github.com/dunglas/symfony-docker`_.

Alternatively, you can install PHP on your local machine and use the
:ref:`symfony binary Docker integration <symfony-server-docker>`. In both cases,
you can take advantage of automatic Docker configuration from :ref:`Symfony Flex <symfony-flex>`.

Flex Recipes & Docker Configuration
-----------------------------------

The :ref:`Flex recipe <symfony-flex>` for some packages also include Docker configuration.
For example, when you run ``composer require doctrine`` (to get ``symfony/orm-pack``),
your ``compose.yaml`` file will automatically be updated to include a
``database`` service.

The first time you install a recipe containing Docker config, Flex will ask you
if you want to include it. Or, you can set your preference in ``composer.json``,
by setting the ``extra.symfony.docker`` config to ``true`` or ``false``.

Some recipes also include additions to your ``Dockerfile``. To get those changes,
you need to already have a ``Dockerfile`` at the root of your app *with* the
following code somewhere inside:

.. code-block:: text

    ###> recipes ###
    ###< recipes ###

The recipe will find this section and add the changes inside. If you're using
`https://github.com/dunglas/symfony-docker`_, you'll already have this.

After installing the package, rebuild your containers by running:

.. code-block:: terminal

    $ docker-compose up --build

Symfony Binary Web Server and Docker Support
--------------------------------------------

If you're using the :ref:`symfony binary web server <symfony-local-web-server>` (e.g. ``symfony server:start``),
then it can automatically detect your Docker services and expose them as environment
variables. See :ref:`symfony-server-docker`.

.. note::

    macOS users need to explicitly allow the default Docker socket to be used
    for the Docker integration to work `as explained in the Docker documentation`_.

.. _`https://github.com/dunglas/symfony-docker`: https://github.com/dunglas/symfony-docker
.. _`as explained in the Docker documentation`: https://docs.docker.com/desktop/mac/permission-requirements/
