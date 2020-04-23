How To Add Secure Password Reset Functionality
==================================================

Using `MakerBundle`_ & `Symfony Cast's Reset Password Bundle`_ you can create a
secure out of the box solution to handle forgotten passwords.

.. caution::

    Make sure you have created a ``User`` class with a getter method to retrieve
    the users unique email address. The :doc:`Security Guide </security>` will help you
    install security.

Bootstrap reset password functionality
---------------------------------------------

.. code-block:: terminal

    $ php composer require symfonycasts/reset-password-bundle
        .....
    $ php bin/console make:reset-password


The reset password maker will then ask you a couple questions about your app and
generate the required files. Afterword's you should see a list of the files generated.

.. code-block:: terminal

    created: src/Controller/ResetPasswordController.php
    created: src/Entity/ResetPasswordRequest.php
    created: src/Repository/ResetPasswordRequestRepository.php
    updated: config/packages/reset_password.yaml
    created: src/Form/ResetPasswordRequestFormType.php
    created: src/Form/ChangePasswordFormType.php
    created: templates/reset_password/check_email.html.twig
    created: templates/reset_password/email.html.twig
    created: templates/reset_password/request.html.twig
    created: templates/reset_password/reset.html.twig


    Success!


    Next:
    1) Run "php bin/console make:migration" to generate a migration for the new "App\Entity\ResetPasswordRequest" entity.
    2) Review forms in "src/Form" to customize validation and labels.
    3) Review and customize the templates in `templates/reset_password`.
    4) Make sure your MAILER_DSN env var has the correct settings.

    Then open your browser, go to "/reset-password" and enjoy!

You can customize the reset password bundle's behavior by editing ``reset_password.yaml``.
For more information on the configuration, check out the
`Symfony Cast's Reset Password Bundle`_  guide.

.. _`MakerBundle`: https://symfony.com/doc/current/bundles/SymfonyMakerBundle/index.html
.. _`Symfony Cast's Reset Password Bundle`: https://github.com/symfonycasts/reset-password-bundle
