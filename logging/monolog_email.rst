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
notifications to a manageable level, especially in critical failure scenarios.
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

.. note::

    The ``MailerHandler`` automatically truncates the formatted email subject
    when it exceeds 200 characters, replacing the extra characters with ``[...]``.
    This prevents errors with mail servers that limit subject length when
    ``%%message%%`` produces a very long subject.

    You can adjust this limit to match what your mail server accepts via the
    ``$subjectMaxLength`` constructor argument of
    :class:`Symfony\\Bridge\\Monolog\\Handler\\MailerHandler`. Setting it to
    ``0`` disables the limit.

    .. versionadded:: 8.1

        Automatic truncation of long email subjects in ``MailerHandler`` was
        introduced in Symfony 8.1.

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

.. _Monolog: https://github.com/Seldaek/monolog
