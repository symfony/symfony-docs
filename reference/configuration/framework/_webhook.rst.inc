webhook
~~~~~~~

The ``webhook`` option (and its children) are used to configure the webhooks
defined in your application. Read more about the options in the
:doc:`Webhook documentation </webhook>`.

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

Whether to enable the webhook support in the framework. This is automatically
enabled when the ``symfony/webhook`` package is installed.

message_bus
...........

**type**: ``string`` **default**: ``'messenger.default_bus'``

The service id of the Messenger bus used to dispatch incoming webhook messages.

routing
.......

**type**: ``array``

Defines the mapping between webhook types and their request parsers. Each key
is the webhook type name (used in the URL ``/webhook/{type}``) and its value
is an array with the following options:

.. code-block:: yaml

    framework:
        webhook:
            routing:
                github:
                    service: App\Webhook\GithubRequestParser
                    secret: '%env(GITHUB_WEBHOOK_SECRET)%'
                stripe:
                    service: App\Webhook\StripeRequestParser
                    secret: '%env(STRIPE_WEBHOOK_SECRET)%'

service
'''''''

**type**: ``string``

The service id of the request parser (a class implementing
:class:`Symfony\\Component\\Webhook\\Client\\RequestParserInterface`) that will
handle incoming requests for this webhook type. This option is required.

secret
''''''

**type**: ``string`` **default**: ``''``

The secret used to verify the webhook signature. The value depends on the
third-party service sending the webhook.
