Running Symfony2 Tests
======================

Before submitting a :doc:`patch <patches>` for inclusion, you need to run the
Symfony2 test suite to check that you have not broken anything.

PHPUnit
-------

To run the Symfony2 test suite, install PHPUnit 3.5.0 or later first. As it is
not stable yet, your best bet is to use the latest version from the
repository, which is for now the 3.5.0RC2 :

.. code-block:: bash

    $ git clone git://github.com/sebastianbergmann/phpunit.git
    $ cd phpunit

.. note::
   You can list the tags of PHPUnit's repo with 'git tag', then quickly point out the last one.

At this point you are working on the master branch, so let's switch to the last tag for some stability:

.. code-block:: bash

    $ git checkout -b origin/3.5.0RC2 3.5.0RC2
    
Finally, use PEAR to install the binaries of the last tag (use 'pear install -f' to force the installation of betas versions) :

.. code-block:: bash

    $ pear channel-discover pear.phpunit.de
    $ pear package
    $ pear install -f PHPUnit-3.5.0RC2.tgz

As you're using an unstable version of PHPUnit, you should really run it on itself :
.. code-block:: bash

    $ phpunit

Dependencies (optional)
-----------------------

To run the entire test suite, including tests that depend on external
dependencies, Symfony2 needs to be able to autoload them. By default, they are
autoloaded from `vendor/` under the main root directory (see
`autoload.php.dist`).

The test suite need the following third-party libraries:

* Doctrine
* Doctrine Migrations
* Phing
* Propel
* Swiftmailer
* Twig
* Zend Framework

To install them all, run the `install_vendors.sh` script:

.. code-block:: bash

    $ sh install_vendors.sh

.. note::
   Note that the script takes some time to finish.

After installation, you can update the vendors anytime with the
`update_vendors.sh` script:

.. code-block:: bash

    $ sh update_vendors.sh

Running
-------

First, update the vendors (see above).

Then, run the test suite from the Symfony2 root directory with the following
command:

.. code-block:: bash

    $ phpunit

The output should display `OK`. If not, you need to figure out what's going on
and if the tests are broken because of your modifications.

.. tip::
   Run the test suite before applying your modifications to check that they run
   fine on your configuration.

Code Coverage
-------------

If you add a new feature, you also need to check the code coverage by using
the `coverage-html` option:

.. code-block:: bash

    $ phpunit --coverage-html=cov/

Check the code coverage by opening the generated `cov/index.html` page in a
browser.

.. tip::
   The code coverage only works if you have XDebug enabled and all dependencies
   installed.

