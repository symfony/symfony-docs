.. index::
   single: Emails; Using the cloud

How to Use the Cloud to Send Emails
===================================

Requirements for sending emails from a production system differ from your
development setup as you don't want to be limited in the number of emails,
the sending rate or the sender address. Thus,
:doc:`using Gmail </cookbook/email/gmail>` or similar services is not an
option. If setting up and maintaining your own reliable mail server causes
you a headache there's a simple solution: Leverage the cloud to send your
emails.

This cookbook shows how easy it is to integrate
`Amazon's Simple Email Service (SES)`_ into Symfony.

.. note::

    You can use the same technique for other mail services, as most of the
    time there is nothing more to it than configuring an SMTP endpoint for
    Swift Mailer.

In the Symfony configuration, change the Swift Mailer settings ``transport``,
``host``, ``port`` and ``encryption`` according to the information provided in
the `SES console`_. Create your individual SMTP credentials in the SES console
and complete the configuration with the provided ``username`` and ``password``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        swiftmailer:
            transport:  smtp
            host:       email-smtp.us-east-1.amazonaws.com
            port:       465 # different ports are available, see SES console
            encryption: tls # TLS encryption is required
            username:   AWS_ACCESS_KEY  # to be created in the SES console
            password:   AWS_SECRET_KEY  # to be created in the SES console

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:swiftmailer="http://symfony.com/schema/dic/swiftmailer"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/swiftmailer
                http://symfony.com/schema/dic/swiftmailer/swiftmailer-1.0.xsd">

            <!-- ... -->
            <swiftmailer:config
                transport="smtp"
                host="email-smtp.us-east-1.amazonaws.com"
                port="465"
                encryption="tls"
                username="AWS_ACCESS_KEY"
                password="AWS_SECRET_KEY"
            />
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('swiftmailer', array(
            'transport'  => 'smtp',
            'host'       => 'email-smtp.us-east-1.amazonaws.com',
            'port'       => 465,
            'encryption' => 'tls',
            'username'   => 'AWS_ACCESS_KEY',
            'password'   => 'AWS_SECRET_KEY',
        ));

The ``port`` and ``encryption`` keys are not present in the Symfony Standard
Edition configuration by default, but you can simply add them as needed.

And that's it, you're ready to start sending emails through the cloud!

.. tip::

    If you are using the Symfony Standard Edition, configure the parameters in
    ``parameters.yml`` and use them in your configuration files. This allows
    for different Swift Mailer configurations for each installation of your
    application. For instance, use Gmail during development and the cloud in
    production.

    .. code-block:: yaml

        # app/config/parameters.yml
        parameters:
            # ...
            mailer_transport:  smtp
            mailer_host:       email-smtp.us-east-1.amazonaws.com
            mailer_port:       465 # different ports are available, see SES console
            mailer_encryption: tls # TLS encryption is required
            mailer_user:       AWS_ACCESS_KEY # to be created in the SES console
            mailer_password:   AWS_SECRET_KEY # to be created in the SES console

.. note::

    If you intend to use Amazon SES, please note the following:

    * You have to sign up to `Amazon Web Services (AWS)`_;

    * Every sender address used in the ``From`` or ``Return-Path`` (bounce
      address) header needs to be confirmed by the owner. You can also
      confirm an entire domain;

    * Initially you are in a restricted sandbox mode. You need to request
      production access before being allowed to send to arbitrary
      recipients;

    * SES may be subject to a charge.

.. _`Amazon's Simple Email Service (SES)`: http://aws.amazon.com/ses
.. _`SES console`: https://console.aws.amazon.com/ses
.. _`Amazon Web Services (AWS)`: http://aws.amazon.com
