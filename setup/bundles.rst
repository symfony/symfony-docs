Upgrading a Third-Party Bundle for a Major Symfony Version
==========================================================

According to the `Symfony releases plan`_, Symfony publishes a new major version
every two years. Your third-party bundle can support more than one major version
(e.g. 5.x and 6.x), but you must apply some techniques to do so, as explained in
this article.

Allowing to Install New Symfony Components
------------------------------------------

Consider a bundle that requires three Symfony components, locked to version ``5.4``:

.. code-block:: json

    {
        "require": {
            "symfony/framework-bundle": "^5.4",
            "symfony/finder": "^5.4",
            "symfony/validator": "^5.4"
        }
    }

When Symfony releases a new major version (e.g. ``6.4``) and an application uses
it, your bundle will no longer be installable in that application. The first
step is to allow that new major version in your bundle:

.. code-block:: json

    {
        "require": {
            "symfony/framework-bundle": "^5.4|^6.4",
            "symfony/finder": "^5.4|^6.4",
            "symfony/validator": "^5.4|^6.4"
        }
    }

Look for Deprecations and Fix Them
----------------------------------

After making the changes shown above, your bundle becomes installable in
applications using the new major version. However, it may still fail at runtime.
This happens because Symfony deprecates features in minor versions and removes
them in the next major version. If your code uses deprecated features, it will
break once those features are removed.

You can **detect deprecations** in two ways:

#. Add the ``--display-deprecations`` option when running your tests with PHPUnit
   (``./bin/phpunit --display-deprecations``)
#. Install the :doc:`Symfony PHPUnit Bridge </components/phpunit_bridge>`
   (``composer require --dev symfony/phpunit-bridge``) and run your tests using:
   ``./vendor/bin/simple-phpunit``

Fix the reported deprecations, rerun the test suite, and repeat the process
until no deprecations remain.

Fixing Deprecations
~~~~~~~~~~~~~~~~~~~

Sometimes fixing a deprecation simply means using the new API instead of the
deprecated one. However, in some cases, major Symfony versions introduce larger
API changes that require conditional logic.

**Avoid relying on the Symfony Kernel version for compatibility checks**, as it
doesn't reflect the version of individual components and leads to fragile,
hard-to-maintain code::

    // ❌ don't do this - resulting code is fragile
    if (Kernel::VERSION_ID <= 50400) {
        // code for Symfony 5.x
    } else {
        // code for Symfony 6.x
    }

Instead, **use feature-based checks**, which are more accurate, robust, and
forward-compatible. For example, if a new method was added to a component in a
given version, check for that feature rather than the kernel version::

    use Symfony\Component\OptionsResolver\OptionsResolver;

    // ✅ this approach is stable across major versions
    if (!method_exists(OptionsResolver::class, 'setDefined')) {
        // code for the old OptionsResolver API
    } else {
        // code for the new OptionsResolver API
    }

Testing your Bundle in Symfony Applications
-------------------------------------------

Before publishing the new version of your bundle, test it locally in a Symfony
application. You have two options:

#. Use the `Composer path repository option`_ to make the application load the
   bundle from a local directory.
#. Create a symbolic link from your bundle directory to the corresponding
   location inside the application's ``vendor/`` directory:

   .. code-block:: terminal

       $ ln -s /path/to/your/local/bundle/ vendor/your-vendor-name/your-bundle-name

Updating the GitHub CI Configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In addition to local tests, it's recommended to configure continuous integration
to test your bundle against multiple Symfony versions. Use the following example
as a starting point for your own GitHub CI configuration:

.. code-block:: yaml

    jobs:
        phpunit:
            strategy:
                fail-fast: false
                matrix:
                    include:
                        - php_version: "7.2"
                          symfony_version: "5.4"
                          stability: "stable"
                        - php_version: "8.1"
                          symfony_version: "6.4"
                          stability: "stable"
                        # ...
            runs-on: ubuntu-latest
            continue-on-error: ${{ matrix.stability == 'dev' }}
            steps:
                - uses: actions/checkout@v6

                - name: Setup PHP, with composer and extensions
                  uses: shivammathur/setup-php@v2
                  with:
                      php-version: ${{ matrix.php_version }}
                      coverage: none
                      extensions: mbstring, intl, pdo
                      ini-values: date.timezone=UTC

                - name: symfony/flex is required to install the correct symfony version
                  if: ${{ matrix.symfony_version }}
                  run: |
                      composer global config --no-plugins allow-plugins.symfony/flex true
                      composer global require symfony/flex

                - name: Configure Composer stability
                  run: |
                      composer config minimum-stability ${{ matrix.stability }}

                - name: Configure Symfony version for symfony/flex
                  if: ${{ matrix.symfony_version }}
                  run: composer config extra.symfony.require "${{ matrix.symfony_version }}.*"

                - name: Install dependencies
                  run: |
                      composer update ${{ matrix.composer_args }};

.. _`Symfony releases plan`: https://symfony.com/releases
.. _`Composer path repository option`: https://getcomposer.org/doc/05-repositories.md#path
