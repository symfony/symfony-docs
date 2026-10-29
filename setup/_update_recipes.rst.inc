3) Updating Recipes
-------------------

Over time - and especially when you upgrade to a new version of a library - an
updated version of the :ref:`recipe <recipes-description>` may be available.
These updates are usually minor - e.g. new comments in a configuration file - but
it's a good idea to keep your files in sync with the recipes.

Symfony Flex provides several commands to help upgrade your recipes. Be sure to
commit any unrelated changes you're working on (including new untracked files)
before starting:

.. code-block:: terminal

    # choose an outdated recipe to update
    $ composer recipes:update

    # update a specific recipe
    $ composer recipes:update symfony/framework-bundle

    # see a list of all installed recipes and which have updates available
    $ composer recipes

    # see only the recipes that have updates available
    $ composer recipes --outdated

    # see detailed information about a specific recipe, including the
    # instructions it displayed when it was installed
    $ composer recipes symfony/framework-bundle

The ``recipes:update`` command compares the version of the recipe you installed
with its latest version. It then creates a patch and applies it to your app. If
there are any conflicts, resolve them like any other ``git`` conflict (e.g. with
``git mergetool`` or the merge tool of your IDE) and commit the result.

When the latest version of the recipe no longer contains some file, the command
removes it from your app. If you modified that file, the command keeps it and
lists it in a warning so you can decide whether to remove it.
