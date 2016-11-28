.. index::
   single: Emails

How to Send an Email
====================

Sending emails is a classic task for any web application and one that has
special complications and potential pitfalls. Instead of recreating the wheel,
one solution to send emails is to use the SwiftmailerBundle, which leverages
the power of the `Swift Mailer`_ library. This bundle comes with the Symfony
Standard Edition.

.. _swift-mailer-configuration:

Configuration
-------------

To use Swift Mailer, you'll need to configure it for your mail server.

.. tip::

    Instead of setting up/using your own mail server, you may want to use
    a hosted mail provider such as `Mandrill`_, `SendGrid`_, `Amazon SES`_
    or others. These give you an SMTP server, username and password (sometimes
    called keys) that can be used with the Swift Mailer configuration.

In a standard Symfony installation, some ``swiftmailer`` configuration is
already included:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        swiftmailer:
            transport: "%mailer_transport%"
            host:      "%mailer_host%"
            username:  "%mailer_user%"
            password:  "%mailer_password%"

    .. code-block:: xml

        <!-- app/config/config.xml -->

        <!--
            xmlns:swiftmailer="http://symfony.com/schema/dic/swiftmailer"
            http://symfony.com/schema/dic/swiftmailer http://symfony.com/schema/dic/swiftmailer/swiftmailer-1.0.xsd
        -->

        <swiftmailer:config
            transport="%mailer_transport%"
            host="%mailer_host%"
            username="%mailer_user%"
            password="%mailer_password%" />

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('swiftmailer', array(
            'transport'  => "%mailer_transport%",
            'host'       => "%mailer_host%",
            'username'   => "%mailer_user%",
            'password'   => "%mailer_password%",
        ));

These values (e.g. ``%mailer_transport%``), are reading from the parameters
that are set in the :ref:`parameters.yml <config-parameters.yml>` file. You
can modify the values in that file, or set the values directly here.

The following configuration attributes are available:

* ``transport``         (``smtp``, ``mail``, ``sendmail``, or ``gmail``)
* ``username``
* ``password``
* ``host``
* ``port``
* ``encryption``        (``tls``, or ``ssl``)
* ``auth_mode``         (``plain``, ``login``, or ``cram-md5``)
* ``spool``

  * ``type`` (how to queue the messages, ``file`` or ``memory`` is supported, see :doc:`/cookbook/email/spool`)
  * ``path`` (where to store the messages)
* ``delivery_address``  (an email address where to send ALL emails)
* ``disable_delivery``  (set to true to disable delivery completely)

Sending Emails
--------------

The Swift Mailer library works by creating, configuring and then sending
``Swift_Message`` objects. The "mailer" is responsible for the actual delivery
of the message and is accessible via the ``mailer`` service. Overall, sending
an email is pretty straightforward::

    public function indexAction($name)
    {
        $mailer = $this->get('mailer');
        $message = $mailer->createMessage()
            ->setSubject('You have Completed Registration!')
            ->setFrom('send@example.com')
            ->setTo('recipient@example.com')
            ->setBody(
                $this->renderView(
                    // app/Resources/views/Emails/registration.html.twig
                    'Emails/registration.html.twig',
                    array('name' => $name)
                ),
                'text/html'
            )
            /*
             * If you also want to include a plaintext version of the message
            ->addPart(
                $this->renderView(
                    'Emails/registration.txt.twig',
                    array('name' => $name)
                ),
                'text/plain'
            )
            */
        ;
        $mailer->send($message);

        return $this->render(...);
    }

To keep things decoupled, the email body has been stored in a template and
rendered with the ``renderView()`` method.

The ``$message`` object supports many more options, such as including attachments,
adding HTML content, and much more. Fortunately, Swift Mailer covers the topic
of `Creating Messages`_ in great detail in its documentation.

.. tip::

    Several other cookbook articles are available related to sending emails
    in Symfony:

    * :doc:`gmail`
    * :doc:`dev_environment`
    * :doc:`spool`

.. _`Swift Mailer`: http://swiftmailer.org/
.. _`Creating Messages`: http://swiftmailer.org/docs/messages.html
.. _`Mandrill`: https://mandrill.com/
.. _`SendGrid`: https://sendgrid.com/
.. _`Amazon SES`: http://aws.amazon.com/ses/
