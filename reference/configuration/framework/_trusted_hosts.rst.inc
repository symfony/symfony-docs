.. _configuration-framework-trusted-hosts:

trusted_hosts
~~~~~~~~~~~~~

**type**: ``array`` | ``string`` **default**: ``['%env(default::SYMFONY_TRUSTED_HOSTS)%']``

.. versionadded:: 7.2

    In Symfony 7.2, the default value of this option was changed from ``[]`` to the
    value stored in the ``SYMFONY_TRUSTED_HOSTS`` environment variable.

A lot of different attacks have been discovered relying on inconsistencies
in handling the ``Host`` header by various software (web servers, reverse
proxies, web frameworks, etc.). In short, every time the framework is
generating an absolute URL (when sending an email to reset a password for
instance), the host might have been manipulated by an attacker.

.. seealso::

    You can read `HTTP Host header attacks`_ for more information about
    these kinds of attacks.

The Symfony :method:`Request::getHost() <Symfony\\Component\\HttpFoundation\\Request::getHost>`
method might be vulnerable to some of these attacks because it depends on
the configuration of your web server. One simple solution to avoid these
attacks is to configure a list of hosts that your Symfony application can respond
to. That's the purpose of this ``trusted_hosts`` option. If the incoming
request's hostname doesn't match one of the regular expressions in this list,
the application won't respond and the user will receive a 400 response.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            trusted_hosts:  ['^example\.com$', '^example\.org$']

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:trusted-host>^example\.com$</framework:trusted-host>
                <framework:trusted-host>^example\.org$</framework:trusted-host>
                <!-- ... -->
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->trustedHosts(['^example\.com$', '^example\.org$']);
        };

Hosts can also be configured to respond to any subdomain, via
``^(.+\.)?example\.com$`` for instance.

In addition, you can also set the trusted hosts in the front controller
using the ``Request::setTrustedHosts()`` method::

    // public/index.php
    Request::setTrustedHosts(['^(.+\.)?example\.com$', '^(.+\.)?example\.org$']);

The default value for this option is an empty array, meaning that the application
can respond to any given host.

.. seealso::

    Read more about this in the `Security Advisory Blog post`_.

.. _`HTTP Host header attacks`: https://www.skeletonscribe.net/2013/05/practical-http-host-header-attacks.html
.. _`Security Advisory Blog post`: https://symfony.com/blog/security-releases-symfony-2-0-24-2-1-12-2-2-5-and-2-3-3-released#cve-2013-4752-request-gethost-poisoning
