.. index::
   single: Security; Restrict Security Firewalls to a Host

How to Restrict Firewalls to a Specific Host
============================================

.. versionadded:: 2.4
    Support for restricting security firewalls to a specific host was introduced in
    Symfony 2.4.

When using the Security component, you can create firewalls that match certain
URL patterns and therefore are activated for all pages whose URL matches
that pattern. Additionally, you can restrict the initialization of a firewall
to a host using the ``host`` key:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml

        # ...

        security:
            firewalls:
                secured_area:
                    pattern:    ^/
                    host:       ^admin\.example\.com$
                    http_basic: true

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
                <firewall name="secured_area" pattern="^/" host="^admin\.example\.com$">
                    <http-basic />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php

        // ...

        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'secured_area' => array(
                    'pattern'    => '^/',
                    'host'       => '^admin\.example\.com$',
                    'http_basic' => true,
                ),
            ),
        ));

The ``host`` (like the ``pattern``) is a regular expression. In this example,
the firewall will only be activated if the host is equal exactly (due to
the ``^`` and ``$`` regex characters) to the hostname ``admin.example.com``.
If the hostname does not match this pattern, the firewall will not be activated
and subsequent firewalls will have the opportunity to be matched for this
request.
