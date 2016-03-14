How to Install or Upgrade to the Latest, Unreleased Symfony Version
===================================================================

In this article, you'll learn how to install and use new Symfony versions before
they are released as stable versions.

Creating a New Project Based on an Unstable Symfony Version
-----------------------------------------------------------

Suppose that Symfony 2.7 version hasn't been released yet and you want to create
a new project to test its features. First, :doc:`install the Composer </cookbook/composer>`
package manager. Then, open a command console, enter your project's directory and
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

Suppose again that Symfony 2.7 hasn't been released yet and you want to upgrade
an existing application to test that your project works with it.

First, open the ``composer.json`` file located in the root directory of your
project. Then, edit the value of the version defined for the ``symfony/symfony``
dependency as follows:

.. code-block:: json

    {
        "require": {
            "symfony/symfony" : "2.7.*@dev"
        }
    }

Finally, open a command console, enter your project directory and execute the
following command to update your project dependencies:

.. code-block:: bash

    $ composer update symfony/symfony

If you prefer to test a Symfony beta version, replace the ``"2.7.*@dev"`` constraint
by ``"2.7.0-beta1"`` to install a specific beta number or ``2.7.*@beta`` to get
the most recent beta version.

After upgrading the Symfony version, read the :doc:`Symfony Upgrading Guide </cookbook/upgrade/index>`
to learn how you should proceed to update your application's code in case the new
Symfony version has deprecated some of its features.

.. tip::

    If you use Git to manage the project's code, it's a good practice to create
    a new branch to test the new Symfony version. This solution avoids introducing
    any issue in your application and allows you to test the new version with
    total confidence:

    .. code-block:: bash

        $ cd projects/my_project/
        $ git checkout -b testing_new_symfony
        # ... update composer.json configuration
        $ composer update symfony/symfony

        # ... after testing the new Symfony version
        $ git checkout master
        $ git branch -D testing_new_symfony
