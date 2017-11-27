.. index::
   single: Emails; Using the cloud

How to Use the Cloud to Send Emails
===================================

Requirements for sending emails from a production system differ from your
development setup as you don't want to be limited in the number of emails,
the sending rate or the sender address. Thus,
:doc:`using Gmail </email/gmail>` or similar services is not an
option. If setting up and maintaining your own reliable mail server causes
you a headache there's a simple solution: Leverage the cloud to send your
emails.

This article shows how easy it is to integrate
`Amazon's Simple Email Service (SES)`_ into Symfony.

.. note::

    You can use the same technique for other mail services, as most of the
    time there is nothing more to it than configuring an SMTP endpoint.

Symfony's mailer uses the ``MAILER_URL`` environment variable to store the
SMTP connection parameters, including the security credentials. Get those
parameters from the `SES console`_ and update the value of ``MAILER_URL`` in
the ``.env`` file:

.. code-block:: bash

    MAILER_URL=smtp://email-smtp.us-east-1.amazonaws.com:587?encryption=tls&username=YOUR_SES_USERNAME&password=YOUR_SES_PASSWORD

And that's it, you're ready to start sending emails through the cloud!

.. note::

    If you intend to use Amazon SES, please note the following:

    * You have to sign up to `Amazon Web Services (AWS)`_;

    * Every sender address used in the ``From`` or ``Return-Path`` (bounce
      address) header needs to be confirmed by the owner. You can also
      confirm an entire domain;

    * Initially you are in a restricted sandbox mode. You need to request
      production access before being allowed to send to arbitrary
      recipients;

    * SES may be subject to a charge.

.. _`Amazon's Simple Email Service (SES)`: http://aws.amazon.com/ses
.. _`SES console`: https://console.aws.amazon.com/ses
.. _`Amazon Web Services (AWS)`: http://aws.amazon.com
