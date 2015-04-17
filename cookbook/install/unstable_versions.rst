How to Install and Use an Unstable Symfony Version
==================================================

Symfony releases two new minor versions (2.5, 2.6, 2.7, etc.) per year, one in
May and one in November (:doc:`see releases detail <contributing/community/releases>`).
Testing the new Symfony versions in your projects as soon as possible is important
to ensure that they will keep working as expected.

In this article you'll learn how to install and use new Symfony versions before
they are released as stable versions.

Creating a New Project Based on an Unstable Symfony Version
-----------------------------------------------------------

Suppose that Symfony 2.7 version hasn't been released yet and you want to create
a new project to test its features. First, :doc:`install Composer </cookbook/composer>`
package manager. Then, open a command console, enter your projects directory and
execute the following command:

.. code-block:: bash

    $ composer create-project symfony/framework-standard-edition my_project "2.7.*" --stability=dev

Once the command finishes its execution, you'll have a new Symfony project created
in the ``my_project/`` directory and based on the most recent code found in the
``2.7`` branch.

If you want to test a beta version, use ``beta`` as the value of the ``stability``
option:

.. code-block:: bash

    $ composer create-project symfony/framework-standard-edition my_project "2.7.*" --stability=beta

Upgrading your Project to an Unstable Symfony Version
-----------------------------------------------------

Instead of creating a new empty project, in this section you'll update an existing
Symfony application to an unstable framework version. Suppose again that Symfony
2.7 version hasn't been released yet and you want to test it in your project.

First, open the ``composer.json`` file located in the root directory of your
project. Then, edit the value of the version defined for the ``symfony/symfony``
dependency as follows:

.. code-block:: json

    {
        "require": {
            // ...
            "symfony/symfony" : "2.7.*@dev"
        }
    }

Then, open a command console, enter your project directory and execute the following
command to update your project dependencies:

.. code-block:: bash

    $ composer update

If you prefer to test a Symfony beta version, replace the ``"2.7.*@dev"`` constraint
by ``"2.7.*@beta1"`` (or any other beta number).

.. tip::

    If you use Git to manage the project's code, it's a good practice to create
    a new branch to test the new Symfony version. This solution avoids introducing
    any issue in your application and allows you to test the new version with
    total confidence:

    .. code-block:: bash

        $ cd projects/my_project/
        $ git checkout -b testing_new_symfony
        // ... update composer.json configuration
        $ composer update

        // ... after testing the new Symfony version
        $ git checkout master
        $ git branch -D testing_new_symfony
