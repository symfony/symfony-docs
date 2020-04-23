How to Add Secure Password Reset Functionality
==============================================

Using `MakerBundle`_ & `Symfony Cast's Reset Password Bundle`_ you can create a
secure out of the box solution to handle forgotten passwords.

.. caution::

    Make sure you have created a ``User`` class with a getter method to retrieve
    the users unique email address. The :doc:`Security Guide </security>` will
    help you install security and create your user class.

Bootstrap reset password functionality
--------------------------------------

.. code-block:: terminal

    $ php composer require symfonycasts/reset-password-bundle
        .....
    $ php bin/console make:reset-password


The reset password maker will then ask you a couple questions about your app and
generate the required files. Afterword's you should see the success message,
a list of the files generated, and any other task's that may need to be performed.

You can customize the reset password bundle's behavior by editing ``reset_password.yaml``.
For more information on the configuration, check out the
`Symfony Cast's Reset Password Bundle`_  guide.

.. _`MakerBundle`: https://symfony.com/doc/current/bundles/SymfonyMakerBundle/index.html
.. _`Symfony Cast's Reset Password Bundle`: https://github.com/symfonycasts/reset-password-bundle
