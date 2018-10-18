.. index::
    single: CSRF; CSRF protection

How to Implement CSRF Protection
================================

CSRF - or `Cross-site request forgery`_ - is a method by which a malicious
user attempts to make your legitimate users unknowingly submit data that
they don't intend to submit.

CSRF protection works by adding a hidden field to your form that contains a
value that only you and your user know. This ensures that the user - not some
other entity - is submitting the given data.

Before using the CSRF protection, install it in your project:

.. code-block:: terminal

    $ composer require symfony/security-csrf

Then, enable/disable the CSRF protection with the ``csrf_protection`` option
(see the :ref:`CSRF configuration reference <reference-framework-csrf-protection>`
for more information):

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # ...
            csrf_protection: ~

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:csrf-protection enabled="true" />
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        $container->loadFromExtension('framework', array(
            'csrf_protection' => null,
        ));

CSRF Protection in Symfony Forms
--------------------------------

Forms created with the Symfony Form component include CSRF tokens by default
and Symfony checks them automatically, so you don't have to do anything to be
protected against CSRF attacks.

.. _form-csrf-customization:

By default Symfony adds the CSRF token in a hidden field called ``_token``, but
this can be customized on a form-by-form basis::

    // ...
    use App\Entity\Task;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class TaskType extends AbstractType
    {
        // ...

        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults(array(
                'data_class'      => Task::class,
                // enable/disable CSRF protection for this form
                'csrf_protection' => true,
                // the name of the hidden HTML field that stores the token
                'csrf_field_name' => '_token',
                // an arbitrary string used to generate the value of the token
                // using a different string for each form improves its security
                'csrf_token_id'   => 'task_item',
            ));
        }

        // ...
    }

.. caution::

    Since the token is stored in the session, a session is started automatically
    as soon as you render a form with CSRF protection.

.. caution::

    CSRF tokens are meant to be different for every user. Beware of that when
    caching pages that include forms containing CSRF tokens. For more
    information, see :doc:`/http_cache/form_csrf_caching`.

CSRF Protection in Login Forms
------------------------------

See :doc:`/security/form_login_setup` for a login form that is protected from
CSRF attacks.

CSRF Protection in HTML Forms
-----------------------------

.. versionadded:: 4.1
    In Symfony versions prior to 4.1, CSRF support required installing the
    Symfony Form component even if you didn't use it.

It's also possible to add CSRF protection to regular HTML forms not managed by
the Symfony Form component, for example the simple forms used to delete items.
First, use the ``csrf_token()`` function in the Twig template to generate a CSRF
token and store it as a hidden field of the form:

.. code-block:: twig

    <form action="{{ url('admin_post_delete', { id: post.id }) }}" method="post">
        {# the argument of csrf_token() is an arbitrary value used to generate the token #}
        <input type="hidden" name="token" value="{{ csrf_token('delete-item') }}" />

        <button type="submit">Delete item</button>
    </form>

Then, get the value of the CSRF token in the controller action and use the
:method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController::isCsrfTokenValid`
to check its validity::

    use Symfony\Component\HttpFoundation\Request;
    // ...

    public function delete(Request $request)
    {
        $submittedToken = $request->request->get('token');

        // 'delete-item' is the same value used in the template to generate the token
        if ($this->isCsrfTokenValid('delete-item', $submittedToken)) {
            // ... do something, like deleting an object
        }
    }

.. _`Cross-site request forgery`: http://en.wikipedia.org/wiki/Cross-site_request_forgery
