.. index::
   single: Emails; Spooling

How to Spool Email
==================

When you are using the ``SwiftmailerBundle`` to send an email from a Symfony2
application, it will default to sending the email immediately. You may, however,
want to avoid the performance hit of the communication between ``Swiftmailer``
and the email transport, which could cause the user to wait for the next
page to load while the email is sending. This can be avoided by choosing
to "spool" the emails instead of sending them directly. This means that ``Swiftmailer``
does not attempt to send the email but instead saves the message to somewhere
such as a file. Another process can then read from the spool and take care
of sending the emails in the spool. Currently only spooling to file or memory is supported
by ``Swiftmailer``.

Spool using memory
------------------

When you use spooling to store the emails to memory, they will get sent right
before the kernel terminates. This means the email only gets sent if the whole
request got executed without any unhandled Exception or any errors. To configure
swiftmailer with the memory option, use the following configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        swiftmailer:
            # ...
            spool: { type: memory }

    .. code-block:: xml

        <!-- app/config/config.xml -->

        <!--
            xmlns:swiftmailer="http://symfony.com/schema/dic/swiftmailer"
            http://symfony.com/schema/dic/swiftmailer http://symfony.com/schema/dic/swiftmailer/swiftmailer-1.0.xsd
        -->

        <swiftmailer:config>
             <swiftmailer:spool type="memory" />
        </swiftmailer:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('swiftmailer', array(
             ...,
            'spool' => array('type' => 'memory')
        ));
        
.. note::

    If you are trying to sent emails from within a console command, you must
    trigger the sending of all spooled messages by yourself as the kernel 
    terminate event doesn't get called. Use the following code to sent
    emails inside your console command::

        $container = $this->getContainer();
        $mailer = $container->get('mailer');
        $spool = $mailer->getTransport()->getSpool();
        $transport = $container->get('swiftmailer.transport.real');

        $spool->flushQueue($transport);
        
    Another option is to create an environment which is only used by console
    commands and either deactivate spooling, thus sending messages directly, or
    use file spooling and then send spooled emails as described below.
        
Spool using a file
------------------

In order to use the spool with a file, use the following configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        swiftmailer:
            # ...
            spool:
                type: file
                path: /path/to/spool

    .. code-block:: xml

        <!-- app/config/config.xml -->

        <!--
            xmlns:swiftmailer="http://symfony.com/schema/dic/swiftmailer"
            http://symfony.com/schema/dic/swiftmailer http://symfony.com/schema/dic/swiftmailer/swiftmailer-1.0.xsd
        -->

        <swiftmailer:config>
             <swiftmailer:spool
                 type="file"
                 path="/path/to/spool" />
        </swiftmailer:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('swiftmailer', array(
             ...,
            'spool' => array(
                'type' => 'file',
                'path' => '/path/to/spool',
            )
        ));

.. tip::

    If you want to store the spool somewhere with your project directory,
    remember that you can use the `%kernel.root_dir%` parameter to reference
    the project's root:

    .. code-block:: yaml

        path: "%kernel.root_dir%/spool"

Now, when your app sends an email, it will not actually be sent but instead
added to the spool. Sending the messages from the spool is done separately.
There is a console command to send the messages in the spool:

.. code-block:: bash

    $ php app/console swiftmailer:spool:send --env=prod

It has an option to limit the number of messages to be sent:

.. code-block:: bash

    $ php app/console swiftmailer:spool:send --message-limit=10 --env=prod

You can also set the time limit in seconds:

.. code-block:: bash

    $ php app/console swiftmailer:spool:send --time-limit=10 --env=prod

Of course you will not want to run this manually in reality. Instead, the
console command should be triggered by a cron job or scheduled task and run
at a regular interval.