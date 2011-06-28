How to Work with Emails During Development
==========================================

When you are creating an application which sends emails you will often
not want to actually send the emails to the specified recipient during
development. If you are using the ``SwiftmailerBundle`` with Symfony2 you
can easily achieve this through configuration settings without having to
make any changes to your application's code at all. There are two main
choices to disable sending emails altogether and to send them all to a
specified address.

Disabling Sending
-----------------

You can disable sending emails by setting the ``disable_delivery`` option
to ``true``. If you do this in the ``dev`` specific config then emails will
not be sent when you are in the ``dev`` environment and will continue to be
sent in the ``prod`` environment:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config_dev.yml
        swiftmailer:
            disable_delivery:  true

    .. code-block:: xml

        <!-- app/config/config_dev.xml -->

        <!--
        xmlns:swiftmailer="http://symfony.com/schema/dic/swiftmailer"
        http://symfony.com/schema/dic/swiftmailer http://symfony.com/schema/dic/swiftmailer/swiftmailer-1.0.xsd
        -->

        <swiftmailer:config
            disable-delivery="true" />

    .. code-block:: php

        // app/config/config_dev.php
        $container->loadFromExtension('swiftmailer', array(
            'disable_delivery'  => "true",
        ));


Sending to a Specifed Address
-----------------------------

You can also choose to have all emails sent to an address set in the config,
instead of the address they are set to be sent to, with the ``delivery_address``
option:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config_dev.yml
        swiftmailer:
            delivery_address:  dev@example.com

    .. code-block:: xml

        <!-- app/config/config_dev.xml -->

        <!--
        xmlns:swiftmailer="http://symfony.com/schema/dic/swiftmailer"
        http://symfony.com/schema/dic/swiftmailer http://symfony.com/schema/dic/swiftmailer/swiftmailer-1.0.xsd
        -->

        <swiftmailer:config
            delivery-address="dev@example.com" />

    .. code-block:: php

        // app/config/config_dev.php
        $container->loadFromExtension('swiftmailer', array(
            'delivery_address'  => "dev@example.com",
        ));

So if an email is sent from your app:

.. code-block:: php

    public function indexAction($name)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('Hello Email')
            ->setFrom('send@example.com')
            ->setTo('recipient@example.com')
            ->setBody($this->renderView('HelloBundle:Hello:email.txt.twig', array('name' => $name)))
        ;
        $this->get('mailer')->send($message);

        return $this->render(...);
    }

Then instead of being sent to ``recipient@example.com`` it will be sent
to ``dev@example.com``. Swiftmailer will add an extra header to the email,
``X-Swift-To`` containing the replaced address, so you will still be able
to see who it would have been sent to.

.. note::

    As well as ``to`` addresses, this will also stop the email being sent
    to any ``CC`` and ``BCC`` addresses set for it. Swiftmailer will add
    additional headers to the email with the overridden addresses in them.
    These are ``X-Swift-Cc`` and ``X-Swift-Bcc`` for the ``CC`` and ``BCC``
    addresses respectively.

Viewing from the Web Debug Toolbar
----------------------------------

You can view any emails sent by a page when you are in the ``dev`` environment
using the Web Debug Toolbar. The email icon in the toolbar will show how
many emails were sent. If you click it a report showing the details of the
emails will open.
