How to Configure Monolog to Email Errors
========================================

`Monolog`_ can be configured to send an email when an error occurs within an
application. The configuration for this requires a few nested handlers
in order to avoid receiving too many emails. This configuration looks
complicated at first but each handler is fairly straightforward when
it is broken down.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/prod/monolog.yaml
        monolog:
            handlers:
                main:
                    type:         fingers_crossed
                    # 500 errors are logged at the critical level
                    action_level: critical
                    # to also log 400 level errors (but not 404's):
                    # action_level: error
                    # excluded_http_codes: [404]
                    handler:      deduplicated
                deduplicated:
                    type:    deduplication
                    handler: symfony_mailer
                symfony_mailer:
                    type:       symfony_mailer
                    from_email: 'error@example.com'
                    to_email:   'error@example.com'
                    # or list of recipients
                    # to_email:   ['dev1@example.com', 'dev2@example.com', ...]
                    subject:    'An Error Occurred! %%message%%'
                    level:      debug
                    formatter:  monolog.formatter.html
                    content_type: text/html

    .. code-block:: php

        // config/packages/prod/monolog.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'monolog' => [
                'handlers' => [
                    'main' => [
                        'type' => 'fingers_crossed',
                        // 500 errors are logged at the critical level
                        'action_level' => 'critical',
                        // to also log 400 level errors:
                        // 'action_level' => 'error',
                        // excluded_http_codes: [404]
                        'handler' => 'deduplicated',
                    ],
                    'deduplicated' => [
                        'type' => 'deduplication',
                        'handler' => 'symfony_mailer',
                    ],
                    'symfony_mailer' => [
                        'type' => 'symfony_mailer',
                        'from_email' => 'error@example.com',
                        'to_email' => ['error@example.com'],
                        // or list of recipients
                        // 'to_email' => ['dev1@example.com', 'dev2@example.com', ...]
                        'subject' => 'An Error Occurred! %%message%%',
                        'level' => 'debug',
                        'formatter' => 'monolog.formatter.html',
                        'content_type' => 'text/html',
                    ],
                ],
            ],
        ]);

The ``main`` handler is a ``fingers_crossed`` handler which means that
it is only triggered when the action level, in this case ``critical`` is reached.
The ``critical`` level is only triggered for 5xx HTTP code errors. If this level
is reached once, the ``fingers_crossed`` handler will log all messages
regardless of their level. The ``handler`` setting means that the output
is then passed onto the ``deduplicated`` handler.

.. tip::

    If you want both 400 level and 500 level errors to trigger an email,
    set the ``action_level`` to ``error`` instead of ``critical``. See the
    code above for an example.

The ``deduplicated`` handler keeps all the messages for a request and then
passes them onto the nested handler in one go, but only if the records are
unique over a given period of time (60 seconds by default). Duplicated records are
discarded. Adding this handler reduces the amount of
notifications to a manageable level, specially in critical failure scenarios.
You can adjust the time period using the ``time`` option:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/prod/monolog.yaml
        monolog:
            handlers:
                # ...
                deduplicated:
                    type: deduplication
                    # the time in seconds during which duplicate entries are discarded (default: 60)
                    time: 10
                    handler: symfony_mailer

    .. code-block:: php

        // config/packages/prod/monolog.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'monolog' => [
                'handlers' => [
                    // ...
                    'deduplicated' => [
                        'type' => 'deduplication',
                        // the time in seconds during which duplicate entries are discarded (default: 60)
                        'time' => 10,
                        'handler' => 'symfony_mailer',
                    ],
                ],
            ],
        ]);

The messages are then passed to the ``symfony_mailer`` handler. This is the handler that
actually deals with emailing you the error. The settings for this are
straightforward, the to and from addresses, the formatter, the content type
and the subject.

You can combine these handlers with other handlers so that the errors still
get logged on the server as well as the emails being sent:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/prod/monolog.yaml
        monolog:
            handlers:
                main:
                    type:         fingers_crossed
                    action_level: critical
                    handler:      grouped
                grouped:
                    type:    group
                    members: [streamed, deduplicated]
                streamed:
                    type:  stream
                    path:  '%kernel.logs_dir%/%kernel.environment%.log'
                    level: debug
                deduplicated:
                    type:    deduplication
                    handler: symfony_mailer
                symfony_mailer:
                    type:         symfony_mailer
                    from_email:   'error@example.com'
                    to_email:     'error@example.com'
                    subject:      'An Error Occurred! %%message%%'
                    level:        debug
                    formatter:    monolog.formatter.html
                    content_type: text/html

    .. code-block:: php

        // config/packages/prod/monolog.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'monolog' => [
                'handlers' => [
                    'main' => [
                        'type' => 'fingers_crossed',
                        'action_level' => 'critical',
                        'handler' => 'grouped',
                    ],
                    'grouped' => [
                        'type' => 'group',
                        'members' => ['streamed', 'deduplicated'],
                    ],
                    'streamed' => [
                        'type' => 'stream',
                        'path' => '%kernel.logs_dir%/%kernel.environment%.log',
                        'level' => 'debug',
                    ],
                    'deduplicated' => [
                        'type' => 'deduplication',
                        'handler' => 'symfony_mailer',
                    ],
                    'symfony_mailer' => [
                        'type' => 'symfony_mailer',
                        'from_email' => 'error@example.com',
                        'to_email' => ['error@example.com'],
                        'subject' => 'An Error Occurred! %%message%%',
                        'level' => 'debug',
                        'formatter' => 'monolog.formatter.html',
                        'content_type' => 'text/html',
                    ],
                ],
            ],
        ]);

This uses the ``grouped`` handler to send the messages to the two
group members, the ``deduplicated`` and the ``stream`` handlers. The messages will
now be both written to the log file and emailed.

Truncating Long Subjects in MailerHandler
-----------------------------------------

When the configured ``subject`` embeds the formatted log message (for example
through the ``%%message%%`` placeholder), a long log line can produce a subject
that exceeds mail server limits. The
:class:`Symfony\\Bridge\\Monolog\\Handler\\MailerHandler` constructor accepts
a ``$subjectMaxLength`` argument (default: ``200``) that caps the final subject
length: when the formatted subject is longer than ``$subjectMaxLength`` bytes,
everything past that offset is replaced with the literal ``[...]`` ellipsis,
so the resulting subject is exactly ``$subjectMaxLength + 5`` bytes. Set the
argument to ``0`` to disable truncation entirely. Comparison and slicing are
byte-based (``strlen`` / ``substr_replace``), so a value that lands inside a
multi-byte UTF-8 sequence can yield a malformed subject. If your subjects may
contain multi-byte characters, prefer ``0`` to disable truncation, or pick a
length large enough that you do not risk landing inside a multi-byte sequence
in practice.

.. versionadded:: 8.1

    The ``$subjectMaxLength`` argument of ``MailerHandler`` was introduced in
    Symfony 8.1. Because its default is ``200``, applications whose final
    subjects exceed 200 bytes will start seeing the ``[...]`` ellipsis after
    byte 200 once they upgrade. Pass ``0`` (or a higher value) to keep the
    previous behavior.

If the MonologBundle ``symfony_mailer`` handler type does not expose this
option in your version, register ``MailerHandler`` as a regular service and
plug it into ``monolog.handlers`` with the ``service`` type. The
``$messageTemplate`` constructor argument can be either an
:class:`Symfony\\Component\\Mime\\Email` instance (or service id) used as a
template, or a callable returning one; in the example below,
``app.monolog.error_email`` is your own service returning the prepared
``Email``:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            app.monolog.mailer_handler:
                class: Symfony\Bridge\Monolog\Handler\MailerHandler
                arguments:
                    $mailer: '@mailer'
                    $messageTemplate: '@app.monolog.error_email'
                    $subjectMaxLength: 120

        # config/packages/prod/monolog.yaml
        monolog:
            handlers:
                mailer:
                    type: service
                    id:   app.monolog.mailer_handler

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Bridge\Monolog\Handler\MailerHandler;

        return function (ContainerConfigurator $container): void {
            $container->services()
                ->set('app.monolog.mailer_handler', MailerHandler::class)
                    ->arg('$mailer', service('mailer'))
                    ->arg('$messageTemplate', service('app.monolog.error_email'))
                    ->arg('$subjectMaxLength', 120);
        };

        // config/packages/prod/monolog.php
        return App::config([
            'monolog' => [
                'handlers' => [
                    'mailer' => [
                        'type' => 'service',
                        'id'   => 'app.monolog.mailer_handler',
                    ],
                ],
            ],
        ]);

See :doc:`/logging/handlers` for the general ``service`` handler type
reference.

.. _Monolog: https://github.com/Seldaek/monolog
