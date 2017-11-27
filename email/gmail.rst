.. index::
   single: Emails; Gmail

How to Use Gmail to Send Emails
===============================

During development, instead of using a regular SMTP server to send emails, you
might find using Gmail easier and more practical. The Symfony mailer makes
it really easy.

In the ``.env`` file used in your development machine, change the ``MAILER_URL``
environment variable to this:

.. code-block:: bash

    MAILER_URL=gmail://YOUR_GMAIL_USERNAME:YOUR_GMAIL_PASSWORD@localhost

Redefining the Default Configuration Parameters
-----------------------------------------------

The ``gmail`` transport is simply a shortcut that uses the ``smtp`` transport
and sets these options:

==============  ==================
Option          Value
==============  ==================
``encryption``  ``ssl``
``auth_mode``   ``login``
``host``        ``smtp.gmail.com``
==============  ==================

If your application uses ``tls`` encryption or ``oauth`` authentication, you
must override the default options by defining the ``encryption`` and ``auth_mode``
parameters.

If your Gmail account uses 2-Step-Verification, you must `generate an App password`_
and use it as the value of the ``mailer_password`` parameter. You must also ensure
that you `allow less secure apps to access your Gmail account`_.

.. seealso::

    See the :doc:`Swiftmailer configuration reference </reference/configuration/swiftmailer>`
    for more details.

.. _`generate an App password`: https://support.google.com/accounts/answer/185833
.. _`allow less secure apps to access your Gmail account`: https://support.google.com/accounts/answer/6010255
