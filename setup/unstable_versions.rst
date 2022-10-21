How to Install or Upgrade to the Latest, Unreleased Symfony Version
===================================================================

In this article, you'll learn how to install and use new Symfony versions before
they are released as stable versions.

Creating a New Project Based on an Unstable Symfony Version
-----------------------------------------------------------

Suppose that the Symfony 6.0 version hasn't been released yet and you want to create
a new project to test its features. First, `install the Composer package manager`_.
Then, open a command console, enter your project's directory and
run the following command:

.. code-block:: terminal

    # Download the absolute latest commit
    $ composer create-project symfony/skeleton my_project -s dev

Once the command finishes, you'll have a new Symfony project created
in the ``my_project/`` directory.

Upgrading your Project to an Unstable Symfony Version
-----------------------------------------------------

Suppose again that Symfony 6.0 hasn't been released yet and you want to upgrade
an existing application to test that your project works with it.

First, open the ``composer.json`` file located in the root directory of your
project. Then, edit the value of all of the ``symfony/*`` libraries to the
new version and change your ``minimum-stability`` to ``beta``:

.. code-block:: diff

      {
          "require": {
    +         "symfony/framework-bundle": "^6.0",
    +         "symfony/finder": "^6.0",
              "...": "..."
          },
    +     "minimum-stability": "beta"
      }

You can also use set ``minimum-stability`` to ``dev``, or omit this line
entirely, and opt into your stability on each package by using constraints
like ``6.0.*@beta``.

Finally, from a terminal, update your project's dependencies:

.. code-block:: terminal

    $ composer update

After upgrading the Symfony version, read the :ref:`Symfony Upgrading Guide <upgrade-major-symfony-deprecations>`
to learn how you should proceed to update your application's code in case the new
Symfony version has deprecated some of its features.

.. tip::

    If you use Git to manage the project's code, it's a good practice to create
    a new branch to test the new Symfony version. This solution avoids introducing
    any issue in your application and allows you to test the new version with
    total confidence:

    .. code-block:: terminal

        $ cd projects/my_project/
        $ git checkout -b testing_new_symfony
        # ... update composer.json configuration
        $ composer update "symfony/*"

        # ... after testing the new Symfony version
        $ git checkout master
        $ git branch -D testing_new_symfony

.. _`install the Composer package manager`: https://getcomposer.org/download/
