.. index::
   single: Emails

How to Send an Email
====================

Symfony provides a mailer feature based on the popular `Swift Mailer`_ library
via the `SwiftMailerBundle`_. This mailer supports sending messages with your
own mail servers as well as using popular email providers like `Mandrill`_,
`SendGrid`_, and `Amazon SES`_.

Installation
------------

In applications using :doc:`Symfony Flex </setup/flex>`, run this command to
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
    MAILER_URL=smtp://localhost:25?encryption=ssl&auth_mode=login&username=&password=

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
            /*
             * If you also want to include a plaintext version of the message
            ->addPart(
                $this->renderView(
                    'emails/registration.txt.twig',
                    ['name' => $name]
                ),
                'text/plain'
            )
            */
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

Learn more
----------

.. toctree::
    :maxdepth: 1

    email/dev_environment
    email/spool
    email/testing

.. _`Swift Mailer`: http://swiftmailer.org/
.. _`SwiftMailerBundle`: https://github.com/symfony/swiftmailer-bundle
.. _`Creating Messages`: https://swiftmailer.symfony.com/docs/messages.html
.. _`Mandrill`: https://mandrill.com/
.. _`SendGrid`: https://sendgrid.com/
.. _`Amazon SES`: http://aws.amazon.com/ses/
.. _`generate an App password`: https://support.google.com/accounts/answer/185833
.. _`allow less secure applications to access your Gmail account`: https://support.google.com/accounts/answer/6010255
.. _`RFC 3986`: https://www.ietf.org/rfc/rfc3986.txt
