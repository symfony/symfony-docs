.. index::
   single: Emails

Emails
======

O Symfony2 aproveita o poder do `Swiftmailer`_ para enviar emails.

Instalação
----------

Ative o ``SwiftmailerBundle`` no seu kernel::

    public function registerBundles()
    {
      $bundles = array(
        // ...
        new Symfony\Framework\SwiftmailerBundle\Bundle(),
      );

      // ...
    }

Configuração
------------

O único parametro obrigatório de configuração é o ``transport``:

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

Os seguintes atributos de configuração estão disponíveis:

* ``transport`` (``smtp``, ``mail``, ``sendmail``, or ``gmail``)
* ``username``
* ``password``
* ``host``
* ``port``
* ``encryption`` (``tls``, or ``ssl``)
* ``auth_mode`` (``plain``, ``login``, or ``cram-md5``)
* ``type``
* ``delivery_strategy`` (``realtime``, ``spool``, ``single_address``, or ``none``)
* ``delivery_address`` (um endereço de email para onde mandar todos os emails)
* ``disable_delivery``

Enviando Emails
---------------

O envio de emails é acessivel através do serviço ``mailer``; de uma action::

    public function indexAction($name)
    {
        // pega primeiro o mailer (obrigatório para inicializar o Swift Mailer)
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
   Para manter as coisas desacopladas, o corpo do email foi guardado em um template,
   renderizado com o método ``renderView()``.

Usando o Gmail
--------------

If you want to use your Gmail account to send emails, use the special
Se você quer usar sua conta do Gmail para enviar emails, use o transporte especial ``gmail``:

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
