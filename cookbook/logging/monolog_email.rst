How to Configure Monolog to Email Errors
========================================

Monolog_ can be configured to send an email when an error occurs with an
application. The configuration for this requires a few nested handlers
in order to avoid receiving too many emails. This configuration looks
complicated at first but each handler is fairly straight forward when
it is broken down.

.. configuration-block::

    .. code-block:: yaml

        monolog:
            handlers:
                mail:
                    type:         fingers_crossed
                    action_level: critical
                    handler:      buffered
                buffered:
                    type:    buffer
                    handler: swift
                swift:
                    type:       swift_mailer
                    from_email: error@example.com
                    to_email:   error@example.com
                    subject:    An Error Occurred!
                    level:      debug

    .. code-block:: xml

        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                                http://symfony.com/schema/dic/monolog http://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <monolog:config>
                <monolog:handler
                    name="mail"
                    type="fingerscrossed"
                    action-level="critical"
                    handler="buffered"
                />
                <monolog:handler
                    name="buffered"
                    type="buffer"
                    handler="swift"
                />
                <monolog:handler
                    name="swift"
                    from-email="error@example.com"
                    to-email="error@example.com"
                    subject="An Error Occurred!"
                    level="debug"
                />
            </monolog:config>
        </container>

The ``mail`` handler is a ``fingerscrossed`` handler which means that
it is only triggered when the action level, in this case ``critical`` is reached.
It then logs everything including messages below the action level.  The
``critical`` level is only triggered for 5xx HTTP code errors, if you only
set it to ``error`` then you will also receive emails for any 4xx code
errors as well. The ``handler`` setting means that the output is then passed
onto the ``buffered``handler.

The ``buffered`` handler simply keeps all the messages for a request and
then passes them onto the nested handler in one go. If you do not use this
handler then each message will be emailed separately. This is then passed
to the ``swift`` handler. This is the handler that actually deals with
emailing you the error. The settings for this are straightforward, the
to and from addresses and the subject.

You can combine these handlers with other handlers so that the errors still
get logged on the server as well as the emails being sent:

.. configuration-block::

    .. code-block:: yaml

        monolog:
            handlers:
                main:
                    type:         fingers_crossed
                    action_level: error
                    handler:      nested
                nested:
                    type:  stream
                    path:  %kernel.logs_dir%/%kernel.environment%.log
                    level: debug
                mail:
                    type:         fingers_crossed
                    action_level: critical
                    handler:      buffered
                buffered:
                    type:    buffer
                    handler: swift
                swift:
                    type:       swift_mailer
                    from_email: error@example.com
                    to_email:   error@example.com
                    subject:    An Error Occurred!
                    level:      debug

    .. code-block:: xml

        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                                http://symfony.com/schema/dic/monolog http://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <monolog:config>
                <monolog:handler
                    name="main"
                    type="fingers_crossed"
                    action_level="error"
                    handler="nested"
                <monolog:handler
                    name="nested"
                    type="stream"
                    path="%kernel.logs_dir%/%kernel.environment%.log"
                    level="debug"
                <monolog:handler
                    name="mail"
                    type="fingerscrossed"
                    action-level="critical"
                    handler="buffered"
                />
                <monolog:handler
                    name="buffered"
                    type="buffer"
                    handler="swift"
                />
                <monolog:handler
                    name="swift"
                    from-email="error@example.com"
                    to-email="error@example.com"
                    subject="An Error Occurred!"
                    level="debug"
                />
            </monolog:config>
        </container>

.. _Monolog: https://github.com/Seldaek/monolog
