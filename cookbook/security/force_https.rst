.. index::
   single: Security; Force HTTPS

How to Force HTTPS or HTTP for different URLs
=============================================

You can force areas of your site to use the HTTPS protocol in the security
config. This is done through the ``access_control`` rules using the ``requires_channel``
option. For example, if you want to force all URLs starting with ``/secure``
to use HTTPS then you could use the following configuration:

.. configuration-block::

        .. code-block:: yaml

            # app/config/security.yml
            security:
                # ...

                access_control:
                    - { path: ^/secure, roles: ROLE_ADMIN, requires_channel: https }

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

                    <rule path="^/secure" role="ROLE_ADMIN" requires_channel="https" />
                </config>
            </srv:container>

        .. code-block:: php

            // app/config/security.php
            $container->loadFromExtension('security', array(
                // ...

                'access_control' => array(
                    array(
                        'path'             => '^/secure',
                        'role'             => 'ROLE_ADMIN',
                        'requires_channel' => 'https',
                    ),
                ),
            ));

The login form itself needs to allow anonymous access, otherwise users will
be unable to authenticate. To force it to use HTTPS you can still use
``access_control`` rules by using the ``IS_AUTHENTICATED_ANONYMOUSLY``
role:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...

            access_control:
                - { path: ^/login, roles: IS_AUTHENTICATED_ANONYMOUSLY, requires_channel: https }

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

                <rule path="^/login"
                    role="IS_AUTHENTICATED_ANONYMOUSLY"
                    requires_channel="https"
                />
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            // ...

            'access_control' => array(
                array(
                    'path'             => '^/login',
                    'role'             => 'IS_AUTHENTICATED_ANONYMOUSLY',
                    'requires_channel' => 'https',
                ),
            ),
        ));

It is also possible to specify using HTTPS in the routing configuration,
see :doc:`/cookbook/routing/scheme` for more details.
