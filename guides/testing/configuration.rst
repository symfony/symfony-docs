.. index::
   pair: Tests; Configuration

Testing Configuration
=====================

.. index::
   pair: PHPUnit; Configuration

PHPUnit Configuration
---------------------

Each application has its own PHPUnit configuration, stored in the
``phpunit.xml.dist`` file. You can edit this file to change the defaults or
create a ``phpunit.xml`` file to tweak the configuration for your local machine.

.. tip::

    Store the ``phpunit.xml.dist`` file in your code repository, and ignore the
    ``phpunit.xml`` file.

By default, only the tests stored in "standard" bundles are run by the
``phpunit`` command (standard being tests under Vendor\\*Bundle\\Tests
namespaces). But you can easily add more namespaces. For instance, the
following configuration adds the tests from the installed third-party bundles:

.. code-block:: xml

    <!-- hello/phpunit.xml.dist -->
    <testsuites>
        <testsuite name="Project Test Suite">
            <directory>../src/*/*Bundle/Tests</directory>
            <directory>../src/Sensio/Bundle/*Bundle/Tests</directory>
        </testsuite>
    </testsuites>

To include other namespaces in the code coverage, also edit the ``<filter>``
section:

.. code-block:: xml

    <filter>
        <whitelist>
            <directory>../src</directory>
            <exclude>
                <directory>../src/*/*Bundle/Resources</directory>
                <directory>../src/*/*Bundle/Tests</directory>
                <directory>../src/Sensio/Bundle/*Bundle/Resources</directory>
                <directory>../src/Sensio/Bundle/*Bundle/Tests</directory>
            </exclude>
        </whitelist>
    </filter>

Client Configuration
--------------------

The Client used by functional tests creates a Kernel that runs in a special
``test`` environment, so you can tweak it as much as you want:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config_test.yml
        imports:
            - { resource: config_dev.yml }

        framework:
            error_handler: false
            test: ~

        web_profiler:
            toolbar: false
            intercept_redirects: false

        zend:
            logger:
                priority: debug

    .. code-block:: xml

        <!-- app/config/config_test.xml -->
        <container>
            <imports>
                <import resource="config_dev.xml" />
            </imports>

            <webprofiler:config
                toolbar="false"
                intercept-redirects="false"
            />

            <app:config error_handler="false">
                <app:test />
            </app:config>

            <zend:config>
                <zend:logger priority="debug" />
            </zend:config>
        </container>

    .. code-block:: php

        // app/config/config_test.php
        $loader->import('config_dev.php');

        $container->loadFromExtension('framework', array(
            'error_handler' => false,
            'test'          => true,
        ));

        $container->loadFromExtension('web_profiler', array(
            'toolbar' => false,
            'intercept-redirects' => false,
        ));

        $container->loadFromExtension('zend', array(
            'logger' => array('priority' => 'debug'),
        ));

You can also change the default environment (``test``) and override the
default debug mode (``true``) by passing them as options to the
``createClient()`` method::

    $client = $this->createClient(array(
        'environment' => 'my_test_env',
        'debug'       => false,
    ));

If your application behaves according to some HTTP headers, pass them as the
second argument of ``createClient()``::

    $client = $this->createClient(array(), array(
        'HTTP_HOST'       => 'en.example.com',
        'HTTP_USER_AGENT' => 'MySuperBrowser/1.0',
    ));

You can also override HTTP headers on a per request basis::

    $client->request('GET', '/', array(), array(
        'HTTP_HOST'       => 'en.example.com',
        'HTTP_USER_AGENT' => 'MySuperBrowser/1.0',
    ));

.. tip::

    To provide your own Client, override the ``test.client.class`` parameter,
    or define a ``test.client`` service.
