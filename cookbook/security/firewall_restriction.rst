.. index::
   single: Security; Restrict Security Firewalls to a Request

How to Restrict Firewalls to a Specific Request
===============================================

When using the Security component, you can create firewalls that match certain request options.
In most cases, matching against the URL is sufficient, but in special cases you can further 
restrict the initialization of a firewall against other options of the request.

.. note::

    You can use any of these restrictions individually or mix them together to get 
    your desired firewall configuration. 

Restricting by Pattern
----------------------

This is the default restriction and restricts a firewall to only be initialized if the request URL 
matches the configured ``pattern``. 

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml

        # ...
        security:
            firewalls:
                secured_area:
                    pattern: ^/admin
                    # ...

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->
                <firewall name="secured_area" pattern="^/admin">
                    <!-- ... -->
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php

        // ...
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'secured_area' => array(
                    'pattern' => '^/admin',
                    // ...
                ),
            ),
        ));

The ``pattern`` is a regular expression. In this example, the firewall will only be 
activated if the URL starts (due to the ``^`` regex character) with ``/admin``. If
the URL does not match this pattern, the firewall will not be activated and subsequent 
firewalls will have the opportunity to be matched for this request.

Restricting by Host
-------------------

If matching against the ``pattern`` only is not enough, the request can also be matched against 
``host``. When the configuration option ``host`` is set, the firewall will be restricted to 
only initialize if the host from the request matches against the configuration.

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml

        # ...
        security:
            firewalls:
                secured_area:
                    host: ^admin\.example\.com$
                    # ...

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->
                <firewall name="secured_area" host="^admin\.example\.com$">
                    <!-- ... -->
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php

        // ...
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'secured_area' => array(
                    'host' => '^admin\.example\.com$',
                    // ...
                ),
            ),
        ));

The ``host`` (like the ``pattern``) is a regular expression. In this example,
the firewall will only be activated if the host is equal exactly (due to
the ``^`` and ``$`` regex characters) to the hostname ``admin.example.com``.
If the hostname does not match this pattern, the firewall will not be activated
and subsequent firewalls will have the opportunity to be matched for this
request.

Restricting by HTTP Methods
---------------------------

The configuration option ``methods`` restricts the initialization of the firewall to
the provided HTTP methods.

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml

        # ...
        security:
            firewalls:
                secured_area:
                    methods: [GET, POST]
                    # ...

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->
                <firewall name="secured_area" methods="GET,POST">
                    <!-- ... -->
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php

        // ...
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'secured_area' => array(
                    'methods' => array('GET', 'POST'),
                    // ...
                ),
            ),
        ));

In this example, the firewall will only be activated if the HTTP method of the
request is either ``GET`` or ``POST``. If the method is not in the array of the
allowed methods, the firewall will not be activated and subsequent firewalls will again
have the opportunity to be matched for this request.
