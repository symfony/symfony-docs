.. index::
   single: Console; Sending emails

How to send emails in a console command
=======================================

Sending emails in a console command works the same way as described in the 
:doc:`/cookbook/email/email` cookbook except if memory spooling is used.

When using memory spooling (see the :doc:`/cookbook/email/spool` cookbook for more
information), you must be aware that because of how symfony handles console 
commands, emails are not sent automatically. You must take care of flushing 
the queue yourself. Use the following code to sent emails inside your 
console command::

    $container = $this->getContainer();
    $mailer = $container->get('mailer');
    $spool = $mailer->getTransport()->getSpool();
    $transport = $container->get('swiftmailer.transport.real');

    $spool->flushQueue($transport);
    
Another option is to create an environment which is only used by console
commands and uses a different spooling method. 
    
.. note::

    Taking care of the spooling is only needed when memory spooling is used. 
    If you are using file spooling (or no spooling at all), there is no need
    to flush the queue manually within the command.