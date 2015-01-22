.. _running-symfony2-tests:

Running Symfony Tests
=====================

Before submitting a :doc:`patch <patches>` for inclusion, you need to run the
Symfony test suite to check that you have not broken anything.

PHPUnit
-------

To run the Symfony test suite, `install PHPUnit`_ 4.2 (or later) first.

Dependencies (optional)
-----------------------

To run the entire test suite, including tests that depend on external
dependencies, Symfony needs to be able to autoload them. By default, they are
autoloaded from ``vendor/`` under the main root directory (see
``autoload.php.dist``).

The test suite needs the following third-party libraries:

* Doctrine
* Swift Mailer
* Twig
* Monolog

To install them all, use `Composer`_:

Step 1: :doc:`Install Composer globally </cookbook/composer>`

Step 2: Install vendors.

.. code-block:: bash

    $ composer install

.. note::

    Note that the script takes some time to finish.

After installation, you can update the vendors to their latest version with
the follow command:

.. code-block:: bash

    $ composer --dev update

Running
-------

First, update the vendors (see above).

Then, run the test suite from the Symfony root directory with the following
command:

.. code-block:: bash

    $ phpunit

The output should display ``OK``. If not, you need to figure out what's going on
and if the tests are broken because of your modifications.

.. tip::

    If you want to test a single component type its path after the ``phpunit``
    command, e.g.:

    .. code-block:: bash

        $ phpunit src/Symfony/Component/Finder/

.. tip::

    Run the test suite before applying your modifications to check that they
    run fine on your configuration.

Code Coverage
-------------

If you add a new feature, you also need to check the code coverage by using
the ``coverage-html`` option:

.. code-block:: bash

    $ phpunit --coverage-html=cov/

Check the code coverage by opening the generated ``cov/index.html`` page in a
browser.

.. tip::

    The code coverage only works if you have Xdebug enabled and all
    dependencies installed.

.. _install PHPUnit: https://phpunit.de/manual/current/en/installation.html
.. _`Composer`: http://getcomposer.org/
