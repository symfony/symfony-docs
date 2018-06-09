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

Before using the CSRF protection, install it in your project (which in turn
requires installing the Symfony Form component):

.. code-block:: terminal

    $ composer require symfony/security-csrf symfony/form

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

`Login CSRF attacks`_ can be prevented using the same technique of adding hidden
CSRF tokens into the login forms. The Security component already provides CSRF
protection, but you need to configure some options before using it.

.. tip::

    If you're using a :doc:`Guard Authenticator </security/guard_authentication>`,
    you'll need to validate the CSRF token manually inside of that class. See
    :ref:`guard-csrf-protection` for details.

First, configure the CSRF token provider used by the form login in your security
configuration. You can set this to use the default provider available in the
security component:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                secured_area:
                    # ...
                    form_login:
                        # ...
                        csrf_token_generator: security.csrf.token_manager

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->

                <firewall name="secured_area">
                    <!-- ... -->
                    <form-login csrf-token-generator="security.csrf.token_manager" />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        $container->loadFromExtension('security', array(
            // ...

            'firewalls' => array(
                'secured_area' => array(
                    // ...
                    'form_login' => array(
                        // ...
                        'csrf_token_generator' => 'security.csrf.token_manager',
                    ),
                ),
            ),
        ));

.. _csrf-login-template:

Then, use the ``csrf_token()`` function in the Twig template to generate a CSRF
token and store it as a hidden field of the form. By default, the HTML field
must be called ``_csrf_token`` and the string used to generate the value must
be ``authenticate``:

.. configuration-block::

    .. code-block:: html+twig

        {# templates/security/login.html.twig #}

        {# ... #}
        <form action="{{ path('login') }}" method="post">
            {# ... the login fields #}

            <input type="hidden" name="_csrf_token"
                value="{{ csrf_token('authenticate') }}"
            >

            <button type="submit">login</button>
        </form>

    .. code-block:: html+php

        <!-- templates/security/login.html.php -->

        <!-- ... -->
        <form action="<?php echo $view['router']->path('login') ?>" method="post">
            <!-- ... the login fields -->

            <input type="hidden" name="_csrf_token"
                value="<?php echo $view['form']->csrfToken('authenticate') ?>"
            >

            <button type="submit">login</button>
        </form>

After this, you have protected your login form against CSRF attacks.

.. tip::

    You can change the name of the field by setting ``csrf_parameter`` and change
    the token ID by setting  ``csrf_token_id`` in your configuration:

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/security.yaml
            security:
                # ...

                firewalls:
                    secured_area:
                        # ...
                        form_login:
                            # ...
                            csrf_parameter: _csrf_security_token
                            csrf_token_id: a_private_string

        .. code-block:: xml

            <!-- config/packages/security.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <srv:container xmlns="http://symfony.com/schema/dic/security"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:srv="http://symfony.com/schema/dic/services"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    http://symfony.com/schema/dic/services/services-1.0.xsd">

                <config>
                    <!-- ... -->

                    <firewall name="secured_area">
                        <!-- ... -->
                        <form-login csrf-parameter="_csrf_security_token"
                            csrf-token-id="a_private_string"
                        />
                    </firewall>
                </config>
            </srv:container>

        .. code-block:: php

            // config/packages/security.php
            $container->loadFromExtension('security', array(
                // ...

                'firewalls' => array(
                    'secured_area' => array(
                        // ...
                        'form_login' => array(
                            // ...
                            'csrf_parameter' => '_csrf_security_token',
                            'csrf_token_id'  => 'a_private_string',
                        ),
                    ),
                ),
            ));

CSRF Protection in HTML Forms
-----------------------------

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
:method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller::isCsrfTokenValid`
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
.. _`Login CSRF attacks`: https://en.wikipedia.org/wiki/Cross-site_request_forgery#Forging_login_requests
