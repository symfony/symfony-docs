.. index::
   single: Emails

How to send an Email
====================

Sending emails is a classic task for any web application and one that has
special complications and potential pitfalls. Instead of recreating the wheel,
one solution to send emails is to use the ``SwiftmailerBundle``, which leverages
the power of the `Swiftmailer`_ library.

.. note::

    Don't forget to enable the bundle in your kernel before using it::

        public function registerBundles()
        {
            $bundles = array(
                // ...
                new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            );

            // ...
        }

.. _swift-mailer-configuration:

Configuration
-------------

Before using Swiftmailer, be sure to include its configuration. The only
mandatory configuration parameter is ``transport``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        swiftmailer:
            transport:  smtp
            encryption: ssl
            auth_mode:  login
            host:       smtp.gmail.com
            username:   your_username
            password:   your_password

    .. code-block:: xml

        <!-- app/config/config.xml -->

        <!--
        xmlns:swiftmailer="http://symfony.com/schema/dic/swiftmailer"
        http://symfony.com/schema/dic/swiftmailer http://symfony.com/schema/dic/swiftmailer/swiftmailer-1.0.xsd
        -->

        <swiftmailer:config
            transport="smtp"
            encryption="ssl"
            auth-mode="login"
            host="smtp.gmail.com"
            username="your_username"
            password="your_password" />

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('swiftmailer', array(
            'transport'  => "smtp",
            'encryption' => "ssl",
            'auth_mode'  => "login",
            'host'       => "smtp.gmail.com",
            'username'   => "your_username",
            'password'   => "your_password",
        ));

The majority of the Swiftmailer configuration deals with how the messages
themselves should be delivered.

The following configuration attributes are available:

* ``transport``         (``smtp``, ``mail``, ``sendmail``, or ``gmail``)
* ``username``
* ``password``
* ``host``
* ``port``
* ``encryption``        (``tls``, or ``ssl``)
* ``auth_mode``         (``plain``, ``login``, or ``cram-md5``)
* ``spool``

  * ``type`` (how to queue the messages, only ``file`` is supported currently)
  * ``path`` (where to store the messages)
* ``delivery_address``  (an email address where to send ALL emails)
* ``disable_delivery``  (set to true to disable delivery completely)

Sending Emails
--------------

The Swiftmailer library works by creating, configuring and then sending
``Swift_Message`` objects. The "mailer" is responsible for the actual delivery
of the message and is accessible via the ``mailer`` service. Overall, sending
an email is pretty straightforward::

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

To keep things decoupled, the email body has been stored in a template and
rendered with the ``renderView()`` method.

The ``$message`` object supports many more options, such as including attachments,
adding HTML content, and much more. Fortunately, Swiftmailer covers the topic
of `Creating Messages`_ in great detail in its documentation.

.. tip::

    Several other cookbook articles are available related to sending emails
    in Symfony2:

    * :doc:`gmail`
    * :doc:`dev_environment`
    * :doc:`spool`

.. _`Swiftmailer`: http://www.swiftmailer.org/
.. _`Creating Messages`: http://swiftmailer.org/docs/messages