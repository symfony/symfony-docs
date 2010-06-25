Testing Configuration
=====================

PHPUnit Configuration
---------------------

Each application has its own PHPUnit configuration, stored in the
`phpunit.xml.dist` file. You can edit this file to change the defaults or
create a `phpunit.xml` file to tweak the configuration for your local machine.

>**TIP**
>Store the `phpunit.xml.dist` file in your code repository, and ignore the
>`phpunit.xml` file.

By default, only the tests stored in the `Application` namespace are run by
the `phpunit` command. But you can easily add more namespaces. For instance,
the following configuration adds the tests from the installed third-party
bundles:

    [xml]
    <!-- hello/phpunit.xml.dist -->
    <testsuites>
        <testsuite name="Project Test Suite">
            <directory>../src/Application/*/Tests</directory>
            <directory>../src/Bundle/*/Tests</directory>
        </testsuite>
    </testsuites>

To include other namespaces in the code coverage, also edit the `<filter>`
section:

    [xml]
    <filter>
        <whitelist>
            <directory>../src/Application</directory>
            <directory>../src/Bundle</directory>
            <exclude>
                <directory>../src/Application/*/Resources</directory>
                <directory>../src/Application/*/Tests</directory>
                <directory>../src/Bundle/*/Resources</directory>
                <directory>../src/Bundle/*/Tests</directory>
            </exclude>
        </whitelist>
    </filter>

Client Configuration
--------------------

The Client used by functional tests creates a Kernel that runs in a special
`test` environment, so you can tweak it as much as you want:

    [yml]
    # config_test.yml
    imports:
        - { resource: config_dev.yml }

    web.config:
        toolbar: false

    zend.logger:
        priority: debug

    kernel.test: ~

You can also change the default environment (`test`) and override the default
debug mode (`true`) by passing them as options to the createClient() method:

    [php]
    $client = $this->createClient(array(
        'environment' => 'my_test_env',
        'debug'       => false,
    ));

If your application behaves according to some HTTP headers, pass them as the
second argument of `createClient()`:

    [php]
    $client = $this->createClient(array(), array(
        'HTTP_HOST'       => 'en.example.com',
        'HTTP_USER_AGENT' => 'MySuperBrowser/1.0',
    ));

>**TIP**
>To provide your own Client, override the `test.client.class` parameter, or
>define a `test.client` service.
