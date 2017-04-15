.. index::
   single: Bundle; Inheritance

How to Use Bundle Inheritance to Override Parts of a Bundle
===========================================================

When working with third-party bundles, you'll probably come across a situation
where you want to override a file in that third-party bundle with a file
in one of your own bundles. Symfony gives you a very convenient way to override
things like controllers, templates, and other files in a bundle's
``Resources/`` directory.

For example, suppose that you have installed `FOSUserBundle`_, but you want to
override its base ``layout.html.twig`` template, as well as one of its
controllers.

First, create a new bundle called UserBundle and enable it in your application.
Then, register the third-party FOSUserBundle as the "parent" of your bundle::

    // src/UserBundle/UserBundle.php
    namespace UserBundle;

    use Symfony\Component\HttpKernel\Bundle\Bundle;

    class UserBundle extends Bundle
    {
        public function getParent()
        {
            return 'FOSUserBundle';
        }
    }

By making this simple change, you can now override several parts of the FOSUserBundle
simply by creating a file with the same name.

.. note::

    Despite the method name, there is no parent/child relationship between
    the bundles, it is just a way to extend and override an existing bundle.

Overriding Controllers
~~~~~~~~~~~~~~~~~~~~~~

Suppose you want to add some functionality to the ``registerAction()`` of a
``RegistrationController`` that lives inside FOSUserBundle. To do so,
just create your own ``RegistrationController.php`` file, override the bundle's
original method, and change its functionality::

    // src/UserBundle/Controller/RegistrationController.php
    namespace UserBundle\Controller;

    use FOS\UserBundle\Controller\RegistrationController as BaseController;

    class RegistrationController extends BaseController
    {
        public function registerAction()
        {
            $response = parent::registerAction();

            // ... do custom stuff
            return $response;
        }
    }

.. tip::

    Depending on how severely you need to change the behavior, you might
    call ``parent::registerAction()`` or completely replace its logic with
    your own.

.. note::

    Overriding controllers in this way only works if the bundle refers to
    the controller using the standard ``FOSUserBundle:Registration:register``
    syntax in routes and templates. This is the best practice.

Overriding Resources: Templates, Routing, etc
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Most resources can also be overridden, simply by creating a file in the same
location as your parent bundle.

For example, it's very common to need to override the FOSUserBundle's
``layout.html.twig`` template so that it uses your application's base layout.
Since the file lives at ``Resources/views/layout.html.twig`` in the FOSUserBundle,
you can create your own file in the same location of UserBundle. Symfony will
ignore the file that lives inside the FOSUserBundle entirely, and use your file
instead.

The same goes for routing files and some other resources.

.. note::

    The overriding of resources only works when you refer to resources with
    the ``@FOSUserBundle/Resources/config/routing/security.xml`` method.
    You need to use the ``@BundleName`` shortcut when refering to resources
    so they can be successfully overridden (except templates, which are
    overridden in a different way, as explained in :doc:`/templating/overriding`).

.. caution::

   Translation and validation files do not work in the same way as described
   above. Read ":ref:`override-translations`" if you want to learn how to
   override translations and see ":ref:`override-validation`" for tricks to
   override the validation.

.. _`FOSUserBundle`: https://github.com/friendsofsymfony/fosuserbundle
