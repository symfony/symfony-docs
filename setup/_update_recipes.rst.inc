3) Updating Recipes
-------------------

Over time - and especially when you upgrade to a new version of a library - an
updated version of the :ref:`recipe <recipes-description>` may be available.
These updates are usually minor - e.g. new comments in a configuration file - but
it's a good idea to keep your files in sync with the recipes.

Symfony Flex provides several commands to help upgrade your recipes. Be sure to
commit any unrelated changes you're working on before starting:

.. versionadded:: 1.6

    The recipes commands were introduced in Symfony Flex 1.6.

.. code-block:: terminal

    # see a list of all installed recipes and which have updates available
    $ composer recipes

    # see detailed information about a specific recipes
    $ composer recipes symfony/framework-bundle

    # update a specific recipes
    $ composer recipes:install symfony/framework-bundle --force -v

The tricky part of this process is that the recipe "update" does not perform
any intelligent "upgrading" of your code. Instead, **the updates process re-installs
the latest version of the recipe** which means that **your custom code will be
overridden completely**. After updating a recipe, you need to carefully choose
which changes you want, and undo the rest.

.. admonition:: Screencast
    :class: screencast

    For a detailed example, see the `SymfonyCasts Symfony 5 Upgrade Tutorial`_.

.. _`SymfonyCasts Symfony 5 Upgrade Tutorial`: https://symfonycasts.com/screencast/symfony5-upgrade
