.. index::
   single: Emails; Gmail

How to Use Gmail to Send Emails
===============================

During development, instead of using a regular SMTP server to send emails, you
might find using Gmail easier and more practical. The SwiftmailerBundle makes
it really easy.

In the development configuration file, change the ``transport`` setting to
``gmail`` and set the ``username`` and ``password`` to the Google credentials:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config_dev.yml
        swiftmailer:
            transport: gmail
            username:  your_gmail_username
            password:  your_gmail_password

    .. code-block:: xml

        <!-- app/config/config_dev.xml -->
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
                transport="gmail"
                username="your_gmail_username"
                password="your_gmail_password"
            />
        </container>

    .. code-block:: php

        // app/config/config_dev.php
        $container->loadFromExtension('swiftmailer', array(
            'transport' => 'gmail',
            'username'  => 'your_gmail_username',
            'password'  => 'your_gmail_password',
        ));

If you are using the Symfony Standard Edition, it's more convenient to configure
these parameters in ``parameters.yml``:

.. code-block:: yaml

    # app/config/parameters.yml
    parameters:
        # ...
        mailer_user:      your_gmail_username
        mailer_password:  your_gmail_password

.. configuration-block::

    .. code-block:: yaml

        # app/config/config_dev.yml
        swiftmailer:
            transport: gmail
            username:  %mailer_user%
            password:  %mailer_password%

    .. code-block:: xml

        <!-- app/config/config_dev.xml -->
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
                transport="gmail"
                username="%mailer_user%"
                password="%mailer_password%"
            />
        </container>

    .. code-block:: php

        // app/config/config_dev.php
        $container->loadFromExtension('swiftmailer', array(
            'transport' => 'gmail',
            'username'  => '%mailer_user%',
            'password'  => '%mailer_password%',
        ));

Redefining the Default Configuration Parameters
-----------------------------------------------

The ``gmail`` transport is simply a shortcut that uses the ``smtp`` transport
and sets ``encryption`` to ``ssl``, ``auth_mode`` to ``login`` and ``host`` to
``smtp.gmail.com`` to work with Gmail.

If your application uses ``tls`` encryption or ``oauth`` authentication, you
must override the default options by defining the ``encryption`` and ``auth_mode``
parameters.

If your Gmail account uses 2-Step-Verification, you must `generate an App password`_
to use for your ``mailer_password`` parameter. You must also ensure that you
`allow less secure apps to access your Gmail account`_.

See the :doc:`Swiftmailer configuration reference </reference/configuration/swiftmailer>`
for more details.

.. _`generate an App password`: https://support.google.com/accounts/answer/185833
.. _`allow less secure apps to access your Gmail account`: https://support.google.com/accounts/answer/6010255
