.. index::
   single: Emails

Emails
======

Symfony2 leverages the power of `Swiftmailer`_ to send emails.

Installation
------------

Enable ``SwiftmailerBundle`` in your kernel::

    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
        );

        // ...
    }

Configuration
-------------

The only mandatory configuration parameter is ``transport``:

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

The following configuration attribute are available:

* ``transport`` (``smtp``, ``mail``, ``sendmail``, or ``gmail``)
* ``username``
* ``password``
* ``host``
* ``port``
* ``encryption`` (``tls``, or ``ssl``)
* ``auth_mode`` (``plain``, ``login``, or ``cram-md5``)
* ``type``
* ``delivery_strategy`` (``realtime``, ``spool``, ``single_address``, or ``none``)
* ``delivery_address`` (an email address where to send ALL emails)
* ``disable_delivery``

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

.. _`Swiftmailer`: http://www.swiftmailer.org/
