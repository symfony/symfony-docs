How to Add a Reset Password Feature
===================================

Using `MakerBundle`_ & `SymfonyCastsResetPasswordBundle`_ you can create a
secure out of the box solution to handle forgotten passwords.

First, make sure you have a security ``User`` class. Follow
the :doc:`Security Guide </security>` if you don't have one already.

Generating the Reset Password Code
----------------------------------

.. code-block:: terminal

    $ composer require symfonycasts/reset-password-bundle
        .....
    $ php bin/console make:reset-password

The `make:reset-password` command will ask you a few questions about your app and
generate all the files you need! After, you'll see a success message and a list
of any other steps you need to do.

You can customize the reset password bundle's behavior by updating the ``reset_password.yaml``
file. For more information on the configuration, check out the
`SymfonyCastsResetPasswordBundle`_  guide.

.. _`MakerBundle`: https://symfony.com/doc/current/bundles/SymfonyMakerBundle/index.html
.. _`SymfonyCastsResetPasswordBundle`: https://github.com/symfonycasts/reset-password-bundle
