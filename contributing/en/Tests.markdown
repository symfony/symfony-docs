Running Symfony2 Tests
======================

Before submitting a [patch][1] for inclusion, you need to run the Symfony2
test suite to check that you have not broken anything.

PHPUnit
-------

To run the Symfony2 test suite, install PHPUnit 3.5.0 or later first. As it is
not stable yet, your best bet is to use the latest version from the
repository:

    $ git clone git://github.com/sebastianbergmann/phpunit.git
    $ cd phpunit
    $ pear package
    $ pear install PHPUnit-3.5.XXX.tgz

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

    $ sh install_vendors.sh

>**NOTE**
>Note that the script takes some time to finish.

After installation, you can update the vendors anytime with the
`update_vendors.sh` script:

    $ sh update_vendors.sh

Running
-------

First, update the vendors (see above).

Then, run the test suite from the Symfony2 root directory with the following
command:

    $ phpunit

The output should display `OK`. If not, you need to figure out what's going on
and if the tests are broken because of your modifications.

>**TIP**
>Run the test suite before applying your modifications to check that they run
>fine on your configuration.

Code Coverage
-------------

If you add a new feature, you also need to check the code coverage by using
the `coverage-html` option:

    $ phpunit --coverage-html=cov/

Check the code coverage by opening the generated `cov/index.html` page in a
browser.

>**TIP**
>The code coverage only works if you have XDebug enabled and all dependencies
>installed.

[1]: http://www.symfony-reloaded.org/contributing/Patches
