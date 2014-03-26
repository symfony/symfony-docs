.. index::
    single: Limit Metadata Writes; Session

Limit Session Metadata Writes
=============================

.. versionadded:: 2.4
    The ability to limit session metadata writes was introduced in Symfony 2.4.

The default behavior of PHP session is to persist the session regardless of
whether the session data has changed or not. In Symfony, each time the session
is accessed, metadata is recorded (session created/last used) which can be used
to determine session age and idle time.

If for performance reasons you wish to limit the frequency at which the session
persists, this feature can adjust the granularity of the metadata updates and
persist the session less often while still maintaining relatively accurate
metadata. If other session data is changed, the session will always persist.

You can tell Symfony not to update the metadata "session last updated" time
until a certain amount of time has passed, by setting
``framework.session.metadata_update_threshold`` to a value in seconds greater
than zero:

.. configuration-block::

    .. code-block:: yaml

        framework:
            session:
                metadata_update_threshold: 120

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:session metadata-update-threshold="120" />
            </framework:config>

        </container>

    .. code-block:: php

        $container->loadFromExtension('framework', array(
            'session' => array(
                'metadata_update_threshold' => 120,
            ),
        ));

.. note::

    PHP default's behavior is to save the session whether it has been changed or
    not. When using ``framework.session.metadata_update_threshold`` Symfony
    will wrap the session handler (configured at
    ``framework.session.handler_id``) into the WriteCheckSessionHandler. This
    will prevent any session write if the session was not modified.

.. caution::

    Be aware that if the session is not written at every request, it may be
    garbage collected sooner than usual. This means that your users may be
    logged out sooner than expected.
