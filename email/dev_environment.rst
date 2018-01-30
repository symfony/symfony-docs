.. index::
   single: Emails; In development

How to Work with Emails during Development
==========================================

When developing an application which sends email, you will often
not want to actually send the email to the specified recipient during
development. If you are using the default Symfony mailer, you
can easily achieve this through configuration settings without having to
make any changes to your application's code at all. There are two main
choices when it comes to handling email during development: (a) disabling the
sending of email altogether or (b) sending all email to a specific
address (with optional exceptions).

Disabling Sending
-----------------

You can disable sending email by setting the ``disable_delivery`` option to
``true``, which is the default value used by Symfony in the ``test`` environment
(email messages will continue to be sent in the other environments):

.. configuration-block::

    .. code-block:: yaml

        # config/packages/test/swiftmailer.yaml
        swiftmailer:
            disable_delivery: true

    .. code-block:: xml

        <!-- config/packages/test/swiftmailer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:swiftmailer="http://symfony.com/schema/dic/swiftmailer"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/swiftmailer http://symfony.com/schema/dic/swiftmailer/swiftmailer-1.0.xsd">

            <swiftmailer:config disable-delivery="true" />
        </container>

    .. code-block:: php

        // config/packages/test/swiftmailer.php
        $container->loadFromExtension('swiftmailer', array(
            'disable_delivery' => "true",
        ));

.. _sending-to-a-specified-address:

Sending to a Specified Address(es)
----------------------------------

You can also choose to have all email sent to a specific address or a list of addresses, instead
of the address actually specified when sending the message. This can be done
via the ``delivery_addresses`` option:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/dev/swiftmailer.yaml
        swiftmailer:
            delivery_addresses: ['dev@example.com']

    .. code-block:: xml

        <!-- config/packages/dev/swiftmailer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:swiftmailer="http://symfony.com/schema/dic/swiftmailer"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/swiftmailer
                http://symfony.com/schema/dic/swiftmailer/swiftmailer-1.0.xsd">

            <swiftmailer:config>
                <swiftmailer:delivery-address>dev@example.com</swiftmailer:delivery-address>
            </swiftmailer:config>
        </container>

    .. code-block:: php

        // config/packages/dev/swiftmailer.php
        $container->loadFromExtension('swiftmailer', array(
            'delivery_addresses' => array("dev@example.com"),
        ));

Now, suppose you're sending an email to ``recipient@example.com`` in a controller::

    public function index($name, \Swift_Mailer $mailer)
    {
        $message = (new \Swift_Message('Hello Email'))
            ->setFrom('send@example.com')
            ->setTo('recipient@example.com')
            ->setBody(
                $this->renderView(
                    'HelloBundle:Hello:email.txt.twig',
                    array('name' => $name)
                )
            )
        ;
        $mailer->send($message);

        return $this->render(...);
    }

In the ``dev`` environment, the email will instead be sent to ``dev@example.com``.
Swift Mailer will add an extra header to the email, ``X-Swift-To``, containing
the replaced address, so you can still see who it would have been sent to.

.. note::

    In addition to the ``to`` addresses, this will also stop the email being
    sent to any ``CC`` and ``BCC`` addresses set for it. Swift Mailer will add
    additional headers to the email with the overridden addresses in them.
    These are ``X-Swift-Cc`` and ``X-Swift-Bcc`` for the ``CC`` and ``BCC``
    addresses respectively.

.. _sending-to-a-specified-address-but-with-exceptions:

Sending to a Specified Address but with Exceptions
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Suppose you want to have all email redirected to a specific address,
(like in the above scenario to ``dev@example.com``). But then you may want
email sent to some specific email addresses to go through after all, and
not be redirected (even if it is in the dev environment). This can be done
by adding the ``delivery_whitelist`` option:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/dev/swiftmailer.yaml
        swiftmailer:
            delivery_addresses: ['dev@example.com']
            delivery_whitelist:
               # all email addresses matching these regexes will be delivered
               # like normal, as well as being sent to dev@example.com
               - '/@specialdomain\.com$/'
               - '/^admin@mydomain\.com$/'

    .. code-block:: xml

        <!-- config/packages/dev/swiftmailer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:swiftmailer="http://symfony.com/schema/dic/swiftmailer"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/swiftmailer
                http://symfony.com/schema/dic/swiftmailer/swiftmailer-1.0.xsd">

            <swiftmailer:config>
                <!-- all email addresses matching these regexes will be delivered
                     like normal, as well as being sent to dev@example.com -->
                <swiftmailer:delivery-whitelist-pattern>/@specialdomain\.com$/</swiftmailer:delivery-whitelist-pattern>
                <swiftmailer:delivery-whitelist-pattern>/^admin@mydomain\.com$/</swiftmailer:delivery-whitelist-pattern>
                <swiftmailer:delivery-address>dev@example.com</swiftmailer:delivery-address>
            </swiftmailer:config>
        </container>

    .. code-block:: php

        // config/packages/dev/swiftmailer.php
        $container->loadFromExtension('swiftmailer', array(
            'delivery_addresses' => array("dev@example.com"),
            'delivery_whitelist' => array(
                // all email addresses matching these regexes will be delivered
                // like normal, as well as being sent to dev@example.com
                '/@specialdomain\.com$/',
                '/^admin@mydomain\.com$/',
            ),
        ));

In the above example all email messages will be redirected to ``dev@example.com``
and messages sent to the ``admin@mydomain.com`` address or to any email address
belonging to the domain ``specialdomain.com`` will also be delivered as normal.

.. caution::

    The ``delivery_whitelist`` option is ignored unless the ``delivery_addresses`` option is defined.

Viewing from the Web Debug Toolbar
----------------------------------

You can view any email sent during a single response when you are in the
``dev`` environment using the web debug toolbar. The email icon in the toolbar
will show how many emails were sent. If you click it, a report will open
showing the details of the sent emails.

If you're sending an email and then immediately redirecting to another page,
the web debug toolbar will not display an email icon or a report on the next
page.

Instead, you can set the ``intercept_redirects`` option to ``true`` in the
``dev`` environment, which will cause the redirect to stop and allow you to open
the report with details of the sent emails.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/dev/swiftmailer.yaml
        web_profiler:
            intercept_redirects: true

    .. code-block:: xml

        <!-- config/packages/dev/swiftmailer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:webprofiler="http://symfony.com/schema/dic/webprofiler"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/webprofiler
                http://symfony.com/schema/dic/webprofiler/webprofiler-1.0.xsd">

            <webprofiler:config
                intercept-redirects="true"
            />
        </container>

    .. code-block:: php

        // config/packages/dev/swiftmailer.php
        $container->loadFromExtension('web_profiler', array(
            'intercept_redirects' => 'true',
        ));

.. tip::

    Alternatively, you can open the profiler after the redirect and search
    by the submit URL used on the previous request (e.g. ``/contact/handle``).
    The profiler's search feature allows you to load the profiler information
    for any past requests.
