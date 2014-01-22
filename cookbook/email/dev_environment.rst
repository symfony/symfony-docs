.. index::
   single: Emails; In development

How to Work with Emails During Development
==========================================

When developing an application which sends email, you will often
not want to actually send the email to the specified recipient during
development. If you are using the SwiftmailerBundle with Symfony2, you
can easily achieve this through configuration settings without having to
make any changes to your application's code at all. There are two main
choices when it comes to handling email during development: (a) disabling the
sending of email altogether or (b) sending all email to a specific
address.

Disabling Sending
-----------------

You can disable sending email by setting the ``disable_delivery`` option
to ``true``. This is the default in the ``test`` environment in the Standard
distribution. If you do this in the ``test`` specific config then email
will not be sent when you run tests, but will continue to be sent in the
``prod`` and ``dev`` environments:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config_test.yml
        swiftmailer:
            disable_delivery:  true

    .. code-block:: xml

        <!-- app/config/config_test.xml -->

        <!--
            xmlns:swiftmailer="http://symfony.com/schema/dic/swiftmailer"
            http://symfony.com/schema/dic/swiftmailer http://symfony.com/schema/dic/swiftmailer/swiftmailer-1.0.xsd
        -->

        <swiftmailer:config
            disable-delivery="true" />

    .. code-block:: php

        // app/config/config_test.php
        $container->loadFromExtension('swiftmailer', array(
            'disable_delivery'  => "true",
        ));

If you'd also like to disable deliver in the ``dev`` environment, simply
add this same configuration to the ``config_dev.yml`` file.

Sending to a Specified Address
------------------------------

You can also choose to have all email sent to a specific address, instead
of the address actually specified when sending the message. This can be done
via the ``delivery_address`` option:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config_dev.yml
        swiftmailer:
            delivery_address: dev@example.com

    .. code-block:: xml

        <!-- app/config/config_dev.xml -->

        <!--
            xmlns:swiftmailer="http://symfony.com/schema/dic/swiftmailer"
            http://symfony.com/schema/dic/swiftmailer http://symfony.com/schema/dic/swiftmailer/swiftmailer-1.0.xsd
        -->

        <swiftmailer:config delivery-address="dev@example.com" />

    .. code-block:: php

        // app/config/config_dev.php
        $container->loadFromExtension('swiftmailer', array(
            'delivery_address'  => "dev@example.com",
        ));

Now, suppose you're sending an email to ``recipient@example.com``.

.. code-block:: php

    public function indexAction($name)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('Hello Email')
            ->setFrom('send@example.com')
            ->setTo('recipient@example.com')
            ->setBody(
                $this->renderView(
                    'HelloBundle:Hello:email.txt.twig',
                    array('name' => $name)
                )
            )
        ;
        $this->get('mailer')->send($message);

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

Viewing from the Web Debug Toolbar
----------------------------------

You can view any email sent during a single response when you are in the
``dev`` environment using the Web Debug Toolbar. The email icon in the toolbar
will show how many emails were sent. If you click it, a report will open
showing the details of the sent emails.

If you're sending an email and then immediately redirecting to another page,
the web debug toolbar will not display an email icon or a report on the next
page.

Instead, you can set the ``intercept_redirects`` option to ``true`` in the
``config_dev.yml`` file, which will cause the redirect to stop and allow
you to open the report with details of the sent emails.

.. configuration-block::

    .. code-block:: yaml

        # app/config/config_dev.yml
        web_profiler:
            intercept_redirects: true

    .. code-block:: xml

        <!-- app/config/config_dev.xml -->

        <!--
            xmlns:webprofiler="http://symfony.com/schema/dic/webprofiler"
            xsi:schemaLocation="http://symfony.com/schema/dic/webprofiler
            http://symfony.com/schema/dic/webprofiler/webprofiler-1.0.xsd">
        -->

        <webprofiler:config
            intercept-redirects="true"
        />

    .. code-block:: php

        // app/config/config_dev.php
        $container->loadFromExtension('web_profiler', array(
            'intercept_redirects' => 'true',
        ));

.. tip::

    Alternatively, you can open the profiler after the redirect and search
    by the submit URL used on the previous request (e.g. ``/contact/handle``).
    The profiler's search feature allows you to load the profiler information
    for any past requests.
