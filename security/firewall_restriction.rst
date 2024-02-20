How to Restrict Firewalls to a Request
======================================

When using the Security component, firewalls will decide whether they handle a
request based on the result of a request matcher: the first firewall matching
the request will handle it.

The last firewall can be configured without any matcher to handle every incoming
request.

Restricting by Configuration
----------------------------

Most of the time you don't need to create matchers yourself as Symfony can do it
for you based on the firewall configuration.

.. note::

    You can use any of the following restrictions individually or mix them
    together to get your desired firewall configuration.

Restricting by Path
~~~~~~~~~~~~~~~~~~~

This is the default restriction and restricts a firewall to only be initialized
if the request path matches the configured ``pattern``.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml

        # ...
        security:
            firewalls:
                secured_area:
                    pattern: ^/admin
                    # ...

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config>
                <!-- ... -->
                <firewall name="secured_area" pattern="^/admin">
                    <!-- ... -->
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security): void {
            // ....

            $security->firewall('secured_area')
                ->pattern('^/admin')
                // ...
            ;
        };

The ``pattern`` is a regular expression. In this example, the firewall will only be
activated if the path starts (due to the ``^`` regex character) with ``/admin``. If
the path does not match this pattern, the firewall will not be activated and subsequent
firewalls will have the opportunity to be matched for this request.

Restricting by Host
~~~~~~~~~~~~~~~~~~~

If matching against the ``pattern`` only is not enough, the request can also be matched against
``host``. When the configuration option ``host`` is set, the firewall will be restricted to
only initialize if the host from the request matches against the configuration.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml

        # ...
        security:
            firewalls:
                secured_area:
                    host: ^admin\.example\.com$
                    # ...

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config>
                <!-- ... -->
                <firewall name="secured_area" host="^admin\.example\.com$">
                    <!-- ... -->
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security): void {
            // ....

            $security->firewall('secured_area')
                ->host('^admin\.example\.com$')
                // ...
            ;
        };

The ``host`` (like the ``pattern``) is a regular expression. In this example,
the firewall will only be activated if the host is equal exactly (due to
the ``^`` and ``$`` regex characters) to the hostname ``admin.example.com``.
If the hostname does not match this pattern, the firewall will not be activated
and subsequent firewalls will have the opportunity to be matched for this
request.

Restricting by HTTP Methods
~~~~~~~~~~~~~~~~~~~~~~~~~~~

The configuration option ``methods`` restricts the initialization of the firewall to
the provided HTTP methods.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml

        # ...
        security:
            firewalls:
                secured_area:
                    methods: [GET, POST]
                    # ...

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config>
                <!-- ... -->
                <firewall name="secured_area" methods="GET,POST">
                    <!-- ... -->
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security): void {
            // ....

            $security->firewall('secured_area')
                ->methods(['GET', 'POST'])
                // ...
            ;
        };

In this example, the firewall will only be activated if the HTTP method of the
request is either ``GET`` or ``POST``. If the method is not in the array of the
allowed methods, the firewall will not be activated and subsequent firewalls will again
have the opportunity to be matched for this request.

Restricting by Service
----------------------

If the above options don't fit your needs you can configure any service implementing
:class:`Symfony\\Component\\HttpFoundation\\RequestMatcherInterface` as ``request_matcher``.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml

        # ...
        security:
            firewalls:
                secured_area:
                    request_matcher: App\Security\CustomRequestMatcher
                    # ...

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config>
                <!-- ... -->
                <firewall name="secured_area" request-matcher="App\Security\CustomRequestMatcher">
                    <!-- ... -->
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use App\Security\CustomRequestMatcher;
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security): void {
            // ....

            $security->firewall('secured_area')
                ->requestMatcher(CustomRequestMatcher::class)
                // ...
            ;
        };
