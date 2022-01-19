3) Updating Recipes
-------------------

Over time - and especially when you upgrade to a new version of a library - an
updated version of the :ref:`recipe <recipes-description>` may be available.
These updates are usually minor - e.g. new comments in a configuration file - but
it's a good idea to keep your files in sync with the recipes.

Symfony Flex provides several commands to help upgrade your recipes. Be sure to
commit any unrelated changes you're working on before starting:

.. versionadded:: 1.18

    The ``recipes:update`` command was introduced in Symfony Flex 1.18.

.. code-block:: terminal

    # choose an outdated recipe to update
    $ composer recipes:update

    # update a specific recipe
    $ composer recipes:update symfony/framework-bundle

    # see a list of all installed recipes and which have updates available
    $ composer recipes

    # see detailed information about a specific recipes
    $ composer recipes symfony/framework-bundle

The ``recipes:update`` command is smart: it looks at the difference between the
recipe when you installed it and the latest version. It then creates a patch and
applies it to your app. If there are any conflicts, you can resolve them like a
normal ``git`` conflict and commit like normal.
