Built-in Authentication Providers
=================================

If you need to add authentication to your app, we recommend using
:doc:`Guard authentication </security/guard_authentication>` because it gives you
full control over the process.

But, Symfony also offers a number of built-in authentication providers: systems
that are easier to implement, but harder to customize. If your authentication
use-case matches one of these exactly, they're a great option:

.. toctree::
    :hidden:

    form_login
    json_login_setup

* :doc:`form_login </security/form_login>`
* :ref:`http_basic <security-http_basic>`
* :doc:`LDAP via HTTP Basic or Form Login </security/ldap>`
* :doc:`json_login </security/json_login_setup>`
* :ref:`X.509 Client Certificate Authentication (x509) <security-x509>`
* :ref:`REMOTE_USER Based Authentication (remote_user) <security-remote_user>`

.. _security-http_basic:

HTTP Basic Authentication
-------------------------

`HTTP Basic authentication`_ asks credentials (username and password) using a dialog
in the browser. The credentials are sent without any hashing or encryption, so
it's recommended to use it with HTTPS.

To support HTTP Basic authentication, add the ``http_basic`` key to your firewall:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    http_basic:
                        realm: Secured Area

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

                <firewall name="main">
                    <http-basic realm="Secured Area"/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        $container->loadFromExtension('security', [
            // ...

            'firewalls' => [
                'main' => [
                    'http_basic' => [
                        'realm' => 'Secured Area',
                    ],
                ],
            ],
        ]);

That's it! Symfony will now be listening for any HTTP basic authentication data.
To load user information, it will use your configured :doc:`user provider </security/user_provider>`.

Note: you cannot use the :ref:`log out <security-logging-out>` with ``http_basic``.
Even if you log out, your browser "remembers" your credentials and will send them
on every request.

.. _security-x509:

X.509 Client Certificate Authentication
---------------------------------------

When using client certificates, your web server is doing all the authentication
process itself. With Apache, for example, you would use the
``SSLVerifyClient Require`` directive.

Enable the x509 authentication for a particular firewall in the security configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    x509:
                        provider: your_user_provider

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

                <firewall name="main">
                    <!-- ... -->
                    <x509 provider="your_user_provider"/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        $container->loadFromExtension('security', [
            // ...

            'firewalls' => [
                'main' => [
                    // ...
                    'x509' => [
                        'provider' => 'your_user_provider',
                    ],
                ],
            ],
        ]);

By default, the firewall provides the ``SSL_CLIENT_S_DN_Email`` variable to
the user provider, and sets the ``SSL_CLIENT_S_DN`` as credentials in the
:class:`Symfony\\Component\\Security\\Core\\Authentication\\Token\\PreAuthenticatedToken`.
You can override these by setting the ``user`` and the ``credentials`` keys
in the x509 firewall configuration respectively.

.. _security-pre-authenticated-user-provider-note:

.. note::

    An authentication provider will only inform the user provider of the username
    that made the request. You will need to create (or use) a "user provider" that
    is referenced by the ``provider`` configuration parameter (``your_user_provider``
    in the configuration example). This provider will turn the username into a User
    object of your choice. For more information on creating or configuring a user
    provider, see:

    * :doc:`/security/user_provider`

.. _security-remote_user:

REMOTE_USER Based Authentication
--------------------------------

A lot of authentication modules, like ``auth_kerb`` for Apache, provide the username
using the ``REMOTE_USER`` environment variable. This variable can be trusted by
the application since the authentication happened before the request reached it.

To configure Symfony using the ``REMOTE_USER`` environment variable, enable the
corresponding firewall in your security configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    # ...
                    remote_user:
                        provider: your_user_provider

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
                <firewall name="main">
                    <remote-user provider="your_user_provider"/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        $container->loadFromExtension('security', [
            'firewalls' => [
                'main' => [
                    'remote_user' => [
                        'provider' => 'your_user_provider',
                    ],
                ],
            ],
        ]);

The firewall will then provide the ``REMOTE_USER`` environment variable to
your user provider. You can change the variable name used by setting the ``user``
key in the ``remote_user`` firewall configuration.

.. note::

    Just like for X509 authentication, you will need to configure a "user provider".
    See :ref:`the previous note <security-pre-authenticated-user-provider-note>`
    for more information.

.. _`HTTP Basic authentication`: https://en.wikipedia.org/wiki/Basic_access_authentication
