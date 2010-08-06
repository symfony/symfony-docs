.. index::
   single: Emails

Emails
======

Installation & Configuration
----------------------------

Sending emails with Symfony is a snap. First, enable ``SwiftmailerBundle`` in
your kernel::

    public function registerBundles()
    {
      $bundles = array(
        // ...
        new Symfony\Framework\SwiftmailerBundle\Bundle(),
      );

      // ...
    }

Then, configure how you want emails to be sent. The only mandatory parameter
is ``transport``; it can be any of ``smtp``, ``mail``, ``sendmail``, or
``gmail``:

.. configuration-block::

    .. code-block:: yaml

        # hello/config/config.yml
        swift.mailer:
            transport:  smtp
            encryption: ssl
            auth_mode:  login
            host:       smtp.gmail.com
            username:   your_username
            password:   your_password

    .. code-block:: xml

        <!--
        xmlns:swift="http://www.symfony-project.org/schema/dic/swiftmailer"
        http://www.symfony-project.org/schema/dic/swiftmailer http://www.symfony-project.org/schema/dic/swiftmailer/swiftmailer-1.0.xsd
        -->

        <swift:mailer
            transport="smtp"
            encryption="ssl"
            auth_mode="login"
            host="smtp.gmail.com"
            username="your_username"
            password="your_password" />

    .. code-block:: php

        // hello/config/config.php
        $container->loadFromExtension('swift', 'mailer', array(
            'transport'  => "smtp",
            'encryption' => "ssl",
            'auth_mode'  => "login",
            'host'       => "smtp.gmail.com",
            'username'   => "your_username",
            'password'   => "your_password",
        ));

Sending Emails
--------------

The mailer is accessible via the ``mailer`` service; from an action::

    public function indexAction($name)
    {
        // get the mailer first (mandatory to initialize Swift Mailer)
        $mailer = $this->container['mailer'];

        $message = \Swift_Message::newInstance()
            ->setSubject('Hello Email')
            ->setFrom('send@example.com')
            ->setTo('recipient@example.com')
            ->setBody($this->renderView('HelloBundle:Hello:email', array('name' => $name)))
        ;
        $mailer->send($message);

        return $this->render(...);
    }

.. note::
   To keep things decoupled, the email body has been stored in a template,
   rendered with the ``renderView()`` method.

Using Gmail
-----------

If you want to use your Gmail account to send emails, use the special
``gmail`` transport:

.. configuration-block::

    .. code-block:: yaml

        # hello/config/config.yml
        swift.mailer:
            transport: gmail
            username:  your_gmail_username
            password:  your_gmail_password

    .. code-block:: xml

        <!--
        xmlns:swift="http://www.symfony-project.org/schema/dic/swiftmailer"
        http://www.symfony-project.org/schema/dic/swiftmailer http://www.symfony-project.org/schema/dic/swiftmailer/swiftmailer-1.0.xsd
        -->

        <!-- hello/config/config.yml -->

        <swift:mailer
            transport="gmail"
            username="your_gmail_username"
            password="your_gmail_password" />

    .. code-block:: php

        // hello/config/config.php
        $container->loadFromExtension('swift', 'mailer', array(
            'transport' => "gmail",
            'username'  => "your_gmail_username",
            'password'  => "your_gmail_password",
        ));
