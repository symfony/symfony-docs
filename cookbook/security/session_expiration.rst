.. index::
    single: Security; Expiration of Idle Sessions

Expiration of Idle Sessions
===========================

To be able to expire idle sessions, you have to activate the ``session_expiration``
firewall listener:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            firewalls:
                main:
                    # ...
                    session_expiration: ~

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <firewall>
                    <!-- ... -->
                    <session-expiration />
                </firewall>
            </config>

        </srv:container>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'main'=> array(
                    // ...
                    'session_expiration' => array(),
                ),
            ),
        ));

To adjust the max idle time before the session is marked as expired, you can
set the ``max_idle_time`` option value in seconds. By default, the value of this
option is equal to the ``session.gc_maxlifetime`` configuration option of PHP.
The ``max_idle_time`` option value **should be less or equal** to the
``session.gc_maxlifetime`` value.

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            firewalls:
                main:
                    # ...
                    session_expiration:
                        max_idle_time: 600

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <firewall>
                    <!-- ... -->
                    <session-expiration max-idle-time="600"/>
                </firewall>
            </config>

        </srv:container>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'main'=> array(
                    // ...
                    'session_expiration' => array(
                        'max_idle_time' => 600,
                    ),
                ),
            ),
        ));

By default, when an expired session is detected, an authorization exception is
thrown. If the option ``expiration_url`` is set, the user will be redirected
to this URL and no exception will be thrown:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            firewalls:
                main:
                    # ...
                    session_expiration:
                        expiration_url: /session-expired

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <firewall>
                    <!-- ... -->
                    <session-expiration expiration-url="/session-expired"/>
                </firewall>
            </config>

        </srv:container>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'main'=> array(
                    // ...
                    'session_expiration' => array(
                        'expiration_url' => '/session-expired',
                    ),
                ),
            ),
        ));

To detect idle sessions, the firewall checks the last used timestamp stored in
the session metadata bag. Beware that this value could be not as accurate as
expected if you :doc:`limit metadata writes </cookbook/session/limit_metadata_writes>`.
