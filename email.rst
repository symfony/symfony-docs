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

In applications using :doc:`Symfony Flex </setup/flex>`, execute this command to
install and enable the mailer:

.. code-block:: terminal

    $ composer require mailer

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

    # use this to disable email delivery
    MAILER_URL=null://localhost

    # use this to send emails via Gmail (don't use this in production)
    MAILER_URL=gmail://username:password@localhost

    # use this to configure a traditional SMTP server
    MAILER_URL=smtp://localhost:25?encryption=ssl&auth_mode=login&username=&password=

Refer to the :doc:`SwiftMailer configuration reference </reference/configuration/swiftmailer>`
for the detailed explanation of all the available config options.

Sending Emails
--------------

The Swift Mailer library works by creating, configuring and then sending
``Swift_Message`` objects. The "mailer" is responsible for the actual delivery
of the message and is accessible via the ``Swift_Mailer`` service. Overall,
sending an email is pretty straightforward::

    public function indexAction($name, \Swift_Mailer $mailer)
    {
        $message = (new \Swift_Message('Hello Email'))
            ->setFrom('send@example.com')
            ->setTo('recipient@example.com')
            ->setBody(
                $this->renderView(
                    // templates/emails/registration.html.twig
                    'emails/registration.html.twig',
                    array('name' => $name)
                ),
                'text/html'
            )
            /*
             * If you also want to include a plaintext version of the message
            ->addPart(
                $this->renderView(
                    'emails/registration.txt.twig',
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
rendered with the ``renderView()`` method. The ``registration.html.twig``
template might look something like this:

.. code-block:: html+jinja

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

Learn more
----------

.. toctree::
    :maxdepth: 1

    email/dev_environment
    email/gmail
    email/cloud
    email/spool
    email/testing

.. _`Swift Mailer`: http://swiftmailer.org/
.. _`SwiftMailerBundle`: https://github.com/symfony/swiftmailer-bundle
.. _`Creating Messages`: http://swiftmailer.org/docs/messages.html
.. _`Mandrill`: https://mandrill.com/
.. _`SendGrid`: https://sendgrid.com/
.. _`Amazon SES`: http://aws.amazon.com/ses/
