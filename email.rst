.. index::
   single: Emails

Swift Mailer
============

.. note::

    In Symfony 4.3, the :doc:`Mailer </mailer>` component was introduced and can
    be used instead of Swift Mailer.

Symfony provides a mailer feature based on the popular `Swift Mailer`_ library
via the `SwiftMailerBundle`_. This mailer supports sending messages with your
own mail servers as well as using popular email providers like `Mandrill`_,
`SendGrid`_, and `Amazon SES`_.

Installation
------------

In applications using :ref:`Symfony Flex <symfony-flex>`, run this command to
install the Swift Mailer based mailer before using it:

.. code-block:: terminal

    $ composer require symfony/swiftmailer-bundle

If your application doesn't use Symfony Flex, follow the installation
instructions on `SwiftMailerBundle`_.

.. _swift-mailer-configuration:

Configuration
-------------

The ``config/packages/swiftmailer.yaml`` file that's created when installing the
mailer provides all the initial config needed to send emails, except your mail
server connection details. Those parameters are defined in the ``MAILER_URL``
environment variable in the ``.env`` file:

.. code-block:: bash

    # .env (or override MAILER_URL in .env.local to avoid committing your changes)

    # use this to disable email delivery
    MAILER_URL=null://localhost

    # use this to configure a traditional SMTP server
    MAILER_URL=smtp://localhost:465?encryption=ssl&auth_mode=login&username=&password=

.. caution::

    If the username, password or host contain any character considered special in a
    URI (such as ``+``, ``@``, ``$``, ``#``, ``/``, ``:``, ``*``, ``!``), you must
    encode them. See `RFC 3986`_ for the full list of reserved characters or use the
    :phpfunction:`urlencode` function to encode them.

Refer to the :doc:`SwiftMailer configuration reference </reference/configuration/swiftmailer>`
for the detailed explanation of all the available config options.

Sending Emails
--------------

The Swift Mailer library works by creating, configuring and then sending
``Swift_Message`` objects. The "mailer" is responsible for the actual delivery
of the message and is accessible via the ``Swift_Mailer`` service. Overall,
sending an email is pretty straightforward::

    public function index($name, \Swift_Mailer $mailer)
    {
        $message = (new \Swift_Message('Hello Email'))
            ->setFrom('send@example.com')
            ->setTo('recipient@example.com')
            ->setBody(
                $this->renderView(
                    // templates/emails/registration.html.twig
                    'emails/registration.html.twig',
                    ['name' => $name]
                ),
                'text/html'
            )

            // you can remove the following code if you don't define a text version for your emails
            ->addPart(
                $this->renderView(
                    // templates/emails/registration.txt.twig
                    'emails/registration.txt.twig',
                    ['name' => $name]
                ),
                'text/plain'
            )
        ;

        $mailer->send($message);

        return $this->render(...);
    }

To keep things decoupled, the email body has been stored in a template and
rendered with the ``renderView()`` method. The ``registration.html.twig``
template might look something like this:

.. code-block:: html+twig

    {# templates/emails/registration.html.twig #}
    <h3>You did it! You registered!</h3>

    Hi {{ name }}! You're successfully registered.

    {# example, assuming you have a route named "login" #}
    To login, go to: <a href="{{ url('login') }}">...</a>.

    Thanks!

    {# Makes an absolute URL to the /images/logo.png file #}
    <img src="{{ absolute_url(asset('images/logo.png')) }}">

The ``$message`` object supports many more options, such as including attachments,
adding HTML content, and much more. Refer to the `Creating Messages`_ section
of the Swift Mailer documentation for more details.

.. _email-using-gmail:

Using Gmail to Send Emails
--------------------------

During development, you might prefer to send emails using Gmail instead of
setting up a regular SMTP server. To do that, update the ``MAILER_URL`` of your
``.env`` file to this:

.. code-block:: bash

    # username is your full Gmail or Google Apps email address
    MAILER_URL=gmail://username:password@localhost

The ``gmail`` transport is a shortcut that uses the ``smtp`` transport, ``ssl``
encryption, ``login`` auth mode and ``smtp.gmail.com`` host. If your app uses
other encryption or auth mode, you must override those values
(:doc:`see mailer config reference </reference/configuration/swiftmailer>`):

.. code-block:: bash

    # username is your full Gmail or Google Apps email address
    MAILER_URL=gmail://username:password@localhost?encryption=tls&auth_mode=oauth

If your Gmail account uses 2-Step-Verification, you must `generate an App password`_
and use it as the value of the mailer password. You must also ensure that you
`allow less secure applications to access your Gmail account`_.

Using Cloud Services to Send Emails
-----------------------------------

Cloud mailing services are a popular option for companies that don't want to set
up and maintain their own reliable mail servers. To use these services in a
Symfony app, update the value of ``MAILER_URL`` in the ``.env``
file. For example, for `Amazon SES`_ (Simple Email Service):

.. code-block:: bash

    # The host will be different depending on your AWS zone
    # The username/password credentials are obtained from the Amazon SES console
    MAILER_URL=smtp://email-smtp.us-east-1.amazonaws.com:587?encryption=tls&username=YOUR_SES_USERNAME&password=YOUR_SES_PASSWORD

Use the same technique for other mail services, as most of the time there is
nothing more to it than configuring an SMTP endpoint.

How to Work with Emails during Development
------------------------------------------

When developing an application which sends email, you will often
not want to actually send the email to the specified recipient during
development. If you are using the SwiftmailerBundle with Symfony, you
can achieve this through configuration settings without having to make
any changes to your application's code at all. There are two main choices
when it comes to handling email during development: (a) disabling the
sending of email altogether or (b) sending all email to a specific
address (with optional exceptions).

Disabling Sending
~~~~~~~~~~~~~~~~~

You can disable sending email by setting the ``disable_delivery`` option to
``true``, which is the default value used by Symfony in the ``test`` environment
(email messages will continue to be sent in the other environments):

.. configuration-block::

    .. code-block:: yaml

        # config/packages/test/swiftmailer.yaml
        swiftmailer:
            disable_delivery: true

    .. code-block:: xml

        <!-- config/packages/test/swiftmailer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:swiftmailer="http://symfony.com/schema/dic/swiftmailer"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/swiftmailer https://symfony.com/schema/dic/swiftmailer/swiftmailer-1.0.xsd">

            <swiftmailer:config disable-delivery="true"/>
        </container>

    .. code-block:: php

        // config/packages/test/swiftmailer.php
        $container->loadFromExtension('swiftmailer', [
            'disable_delivery' => "true",
        ]);

.. _sending-to-a-specified-address:

Sending to a Specified Address(es)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can also choose to have all email sent to a specific address or a list of addresses, instead
of the address actually specified when sending the message. This can be done
via the ``delivery_addresses`` option:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/dev/swiftmailer.yaml
        swiftmailer:
            delivery_addresses: ['dev@example.com']

    .. code-block:: xml

        <!-- config/packages/dev/swiftmailer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:swiftmailer="http://symfony.com/schema/dic/swiftmailer"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/swiftmailer
                https://symfony.com/schema/dic/swiftmailer/swiftmailer-1.0.xsd">

            <swiftmailer:config>
                <swiftmailer:delivery-address>dev@example.com</swiftmailer:delivery-address>
            </swiftmailer:config>
        </container>

    .. code-block:: php

        // config/packages/dev/swiftmailer.php
        $container->loadFromExtension('swiftmailer', [
            'delivery_addresses' => ['dev@example.com'],
        ]);

Now, suppose you're sending an email to ``recipient@example.com`` in a controller::

    public function index($name, \Swift_Mailer $mailer)
    {
        $message = (new \Swift_Message('Hello Email'))
            ->setFrom('send@example.com')
            ->setTo('recipient@example.com')
            ->setBody(
                $this->renderView(
                    // templates/hello/email.txt.twig
                    'hello/email.txt.twig',
                    ['name' => $name]
                )
            )
        ;
        $mailer->send($message);

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

.. _sending-to-a-specified-address-but-with-exceptions:

Sending to a Specified Address but with Exceptions
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Suppose you want to have all email redirected to a specific address,
(like in the above scenario to ``dev@example.com``). But then you may want
email sent to some specific email addresses to go through after all, and
not be redirected (even if it is in the dev environment). This can be done
by adding the ``delivery_whitelist`` option:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/dev/swiftmailer.yaml
        swiftmailer:
            delivery_addresses: ['dev@example.com']
            delivery_whitelist:
                # all email addresses matching these regexes will be delivered
                # like normal, as well as being sent to dev@example.com
                - '/@specialdomain\.com$/'
                - '/^admin@mydomain\.com$/'

    .. code-block:: xml

        <!-- config/packages/dev/swiftmailer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:swiftmailer="http://symfony.com/schema/dic/swiftmailer"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/swiftmailer
                https://symfony.com/schema/dic/swiftmailer/swiftmailer-1.0.xsd">

            <swiftmailer:config>
                <!-- all email addresses matching these regexes will be delivered
                     like normal, as well as being sent to dev@example.com -->
                <swiftmailer:delivery-whitelist-pattern>/@specialdomain\.com$/</swiftmailer:delivery-whitelist-pattern>
                <swiftmailer:delivery-whitelist-pattern>/^admin@mydomain\.com$/</swiftmailer:delivery-whitelist-pattern>
                <swiftmailer:delivery-address>dev@example.com</swiftmailer:delivery-address>
            </swiftmailer:config>
        </container>

    .. code-block:: php

        // config/packages/dev/swiftmailer.php
        $container->loadFromExtension('swiftmailer', [
            'delivery_addresses' => ["dev@example.com"],
            'delivery_whitelist' => [
                // all email addresses matching these regexes will be delivered
                // like normal, as well as being sent to dev@example.com
                '/@specialdomain\.com$/',
                '/^admin@mydomain\.com$/',
            ],
        ]);

In the above example all email messages will be redirected to ``dev@example.com``
and messages sent to the ``admin@mydomain.com`` address or to any email address
belonging to the domain ``specialdomain.com`` will also be delivered as normal.

.. caution::

    The ``delivery_whitelist`` option is ignored unless the ``delivery_addresses`` option is defined.

Viewing from the Web Debug Toolbar
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can view any email sent during a single response when you are in the
``dev`` environment using the web debug toolbar. The email icon in the toolbar
will show how many emails were sent. If you click it, a report will open
showing the details of the sent emails.

If you're sending an email and then immediately redirecting to another page,
the web debug toolbar will not display an email icon or a report on the next
page.

Instead, you can set the ``intercept_redirects`` option to ``true`` in the
``dev`` environment, which will cause the redirect to stop and allow you to open
the report with details of the sent emails.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/dev/web_profiler.yaml
        web_profiler:
            intercept_redirects: true

    .. code-block:: xml

        <!-- config/packages/dev/web_profiler.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:webprofiler="http://symfony.com/schema/dic/webprofiler"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/webprofiler
                https://symfony.com/schema/dic/webprofiler/webprofiler-1.0.xsd">

            <webprofiler:config
                intercept-redirects="true"
            />
        </container>

    .. code-block:: php

        // config/packages/dev/web_profiler.php
        use Symfony\Config\WebProfilerConfig;

        return static function (WebProfilerConfig $webProfiler) {
            $webProfiler->interceptRedirects(true);
        };

.. tip::

    Alternatively, you can open the profiler after the redirect and search
    by the submit URL used on the previous request (e.g. ``/contact/handle``).
    The profiler's search feature allows you to load the profiler information
    for any past requests.

.. tip::

    In addition to the features provided by Symfony, there are applications that
    can help you test emails during application development, like `MailCatcher`_,
    `Mailtrap`_ and `MailHog`_.

How to Spool Emails
-------------------

The default behavior of the Symfony mailer is to send the email messages
immediately. You may, however, want to avoid the performance hit of the
communication to the email server, which could cause the user to wait for the
next page to load while the email is sending. This can be avoided by choosing to
"spool" the emails instead of sending them directly.

This makes the mailer to not attempt to send the email message but instead save
it somewhere such as a file. Another process can then read from the spool and
take care of sending the emails in the spool. Currently only spooling to file or
memory is supported.

.. _email-spool-memory:

Spool Using Memory
~~~~~~~~~~~~~~~~~~

When you use spooling to store the emails to memory, they will get sent right
before the kernel terminates. This means the email only gets sent if the whole
request got executed without any unhandled exception or any errors. To configure
this spool, use the following configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/swiftmailer.yaml
        swiftmailer:
            # ...
            spool: { type: memory }

    .. code-block:: xml

        <!-- config/packages/swiftmailer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:swiftmailer="http://symfony.com/schema/dic/swiftmailer"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/swiftmailer https://symfony.com/schema/dic/swiftmailer/swiftmailer-1.0.xsd">

            <swiftmailer:config>
                <swiftmailer:spool type="memory"/>
            </swiftmailer:config>
        </container>

    .. code-block:: php

        // config/packages/swiftmailer.php
        $container->loadFromExtension('swiftmailer', [
            // ...
            'spool' => ['type' => 'memory'],
        ]);

.. _spool-using-a-file:

Spool Using Files
~~~~~~~~~~~~~~~~~

When you use the filesystem for spooling, Symfony creates a folder in the given
path for each mail service (e.g. "default" for the default service). This folder
will contain files for each email in the spool. So make sure this directory is
writable by Symfony (or your webserver/php)!

In order to use the spool with files, use the following configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/swiftmailer.yaml
        swiftmailer:
            # ...
            spool:
                type: file
                path: /path/to/spooldir

    .. code-block:: xml

        <!-- config/packages/swiftmailer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:swiftmailer="http://symfony.com/schema/dic/swiftmailer"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/swiftmailer https://symfony.com/schema/dic/swiftmailer/swiftmailer-1.0.xsd">

            <swiftmailer:config>
                <swiftmailer:spool
                    type="file"
                    path="/path/to/spooldir"
                />
            </swiftmailer:config>
        </container>

    .. code-block:: php

        // config/packages/swiftmailer.php
        $container->loadFromExtension('swiftmailer', [
            // ...

            'spool' => [
                'type' => 'file',
                'path' => '/path/to/spooldir',
            ],
        ]);

.. tip::

    If you want to store the spool somewhere with your project directory,
    remember that you can use the ``%kernel.project_dir%`` parameter to reference
    the project's root:

    .. code-block:: yaml

        path: '%kernel.project_dir%/var/spool'

Now, when your app sends an email, it will not actually be sent but instead
added to the spool. Sending the messages from the spool is done separately.
There is a console command to send the messages in the spool:

.. code-block:: terminal

    $ APP_ENV=prod php bin/console swiftmailer:spool:send

It has an option to limit the number of messages to be sent:

.. code-block:: terminal

    $ APP_ENV=prod php bin/console swiftmailer:spool:send --message-limit=10

You can also set the time limit in seconds:

.. code-block:: terminal

    $ APP_ENV=prod php bin/console swiftmailer:spool:send --time-limit=10

In practice you will not want to run this manually. Instead, the console command
should be triggered by a cron job or scheduled task and run at a regular
interval.

.. caution::

    When you create a message with SwiftMailer, it generates a ``Swift_Message``
    class. If the ``swiftmailer`` service is lazy loaded, it generates instead a
    proxy class named ``Swift_Message_<someRandomCharacters>``.

    If you use the memory spool, this change is transparent and has no impact.
    But when using the filesystem spool, the message class is serialized in
    a file with the randomized class name. The problem is that this random
    class name changes on every cache clear.

    So if you send a mail and then you clear the cache, on the next execution of
    ``swiftmailer:spool:send`` an error will raise because the class
    ``Swift_Message_<someRandomCharacters>`` doesn't exist (anymore).

    The solutions are either to use the memory spool or to load the
    ``swiftmailer`` service without the ``lazy`` option (see :doc:`/service_container/lazy_services`).

How to Test that an Email is Sent in a Functional Test
------------------------------------------------------

Sending emails with Symfony is pretty straightforward thanks to the
SwiftmailerBundle, which leverages the power of the `Swift Mailer`_ library.

To functionally test that an email was sent, and even assert the email subject,
content or any other headers, you can use :doc:`the Symfony Profiler </profiler>`.

Start with a controller action that sends an email::

    public function sendEmail($name, \Swift_Mailer $mailer)
    {
        $message = (new \Swift_Message('Hello Email'))
            ->setFrom('send@example.com')
            ->setTo('recipient@example.com')
            ->setBody('You should see me from the profiler!')
        ;

        $mailer->send($message);

        // ...
    }

In your functional test, use the ``swiftmailer`` collector on the profiler
to get information about the messages sent on the previous request::

    // tests/Controller/MailControllerTest.php
    namespace App\Tests\Controller;

    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

    class MailControllerTest extends WebTestCase
    {
        public function testMailIsSentAndContentIsOk()
        {
            $client = static::createClient();

            // enables the profiler for the next request (it does nothing if the profiler is not available)
            $client->enableProfiler();

            $crawler = $client->request('POST', '/path/to/above/action');

            $mailCollector = $client->getProfile()->getCollector('swiftmailer');

            // checks that an email was sent
            $this->assertSame(1, $mailCollector->getMessageCount());

            $collectedMessages = $mailCollector->getMessages();
            $message = $collectedMessages[0];

            // Asserting email data
            $this->assertInstanceOf('Swift_Message', $message);
            $this->assertSame('Hello Email', $message->getSubject());
            $this->assertSame('send@example.com', key($message->getFrom()));
            $this->assertSame('recipient@example.com', key($message->getTo()));
            $this->assertSame(
                'You should see me from the profiler!',
                $message->getBody()
            );
        }
    }

Troubleshooting
~~~~~~~~~~~~~~~

Problem: The Collector Object Is ``null``
.........................................

The email collector is only available when the profiler is enabled and collects
information, as explained in :doc:`/testing/profiling`.

Problem: The Collector Doesn't Contain the Email
................................................

If a redirection is performed after sending the email (for example when you send
an email after a form is processed and before redirecting to another page), make
sure that the test client doesn't follow the redirects, as explained in
:doc:`/testing`. Otherwise, the collector will contain the information of the
redirected page and the email won't be accessible.

.. _`MailCatcher`: https://github.com/sj26/mailcatcher
.. _`MailHog`: https://github.com/mailhog/MailHog
.. _`Mailtrap`: https://mailtrap.io/
.. _`Swift Mailer`: https://swiftmailer.symfony.com/
.. _`SwiftMailerBundle`: https://github.com/symfony/swiftmailer-bundle
.. _`Creating Messages`: https://swiftmailer.symfony.com/docs/messages.html
.. _`Mandrill`: https://mandrill.com/
.. _`SendGrid`: https://sendgrid.com/
.. _`Amazon SES`: https://aws.amazon.com/ses/
.. _`generate an App password`: https://support.google.com/accounts/answer/185833
.. _`allow less secure applications to access your Gmail account`: https://support.google.com/accounts/answer/6010255
.. _`RFC 3986`: https://www.ietf.org/rfc/rfc3986.txt
