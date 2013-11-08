.. index::
   single: Emails; Gmail

How to use Gmail to send Emails
===============================

During development, instead of using a regular SMTP server to send emails, you
might find using Gmail easier and more practical. The Swiftmailer bundle makes
it really easy.

.. tip::

    Instead of using your regular Gmail account, it's of course recommended
    that you create a special account.

In the development configuration file, change the ``transport`` setting to
``gmail`` and set the ``username`` and ``password`` to the Google credentials:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config_dev.yml
        swiftmailer:
            transport: gmail
            username:  your_gmail_username
            password:  your_gmail_password

    .. code-block:: xml

        <!-- app/config/config_dev.xml -->

        <!--
            xmlns:swiftmailer="http://symfony.com/schema/dic/swiftmailer"
            http://symfony.com/schema/dic/swiftmailer http://symfony.com/schema/dic/swiftmailer/swiftmailer-1.0.xsd
        -->

        <swiftmailer:config
            transport="gmail"
            username="your_gmail_username"
            password="your_gmail_password" />

    .. code-block:: php

        // app/config/config_dev.php
        $container->loadFromExtension('swiftmailer', array(
            'transport' => "gmail",
            'username'  => "your_gmail_username",
            'password'  => "your_gmail_password",
        ));

You're done!

.. tip::

    If you are using the Symfony Standard Edition, configure the parameters at ``parameters.yml``:

    .. code-block:: yaml

        # app/config/parameters.yml
        parameters:
            ...
            mailer_transport: gmail
            mailer_host:      ~
            mailer_user:      your_gmail_username
            mailer_password:  your_gmail_password

.. note::

    The ``gmail`` transport is simply a shortcut that uses the ``smtp`` transport
    and sets ``encryption``, ``auth_mode`` and ``host`` to work with Gmail.
