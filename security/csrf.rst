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
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:csrf-protection enabled="true"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->csrfProtection()
                ->enabled(true)
            ;
        };

The tokens used for CSRF protection are meant to be different for every user and
they are stored in the session. That's why a session is started automatically as
soon as you render a form with CSRF protection.

.. _caching-pages-that-contain-csrf-protected-forms:

Moreover, this means that you cannot fully cache pages that include CSRF
protected forms. As an alternative, you can:

* Embed the form inside an uncached :doc:`ESI fragment </http_cache/esi>` and
  cache the rest of the page contents;
* Cache the entire page and load the form via an uncached AJAX request;
* Cache the entire page and use :doc:`hinclude.js </templating/hinclude>` to
  load the CSRF token with an uncached AJAX request and replace the form
  field value with it.

CSRF Protection in Symfony Forms
--------------------------------

Forms created with the Symfony Form component include CSRF tokens by default
and Symfony checks them automatically, so you don't have to do anything to be
protected against CSRF attacks.

.. _form-csrf-customization:

By default Symfony adds the CSRF token in a hidden field called ``_token``, but
this can be customized on a form-by-form basis::

    // src/Form/TaskType.php
    namespace App\Form;

    // ...
    use App\Entity\Task;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class TaskType extends AbstractType
    {
        // ...

        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'data_class'      => Task::class,
                // enable/disable CSRF protection for this form
                'csrf_protection' => true,
                // the name of the hidden HTML field that stores the token
                'csrf_field_name' => '_token',
                // an arbitrary string used to generate the value of the token
                // using a different string for each form improves its security
                'csrf_token_id'   => 'task_item',
            ]);
        }

        // ...
    }

You can also customize the rendering of the CSRF form field creating a custom
:doc:`form theme </form/form_themes>` and using ``csrf_token`` as the prefix of
the field (e.g. define ``{% block csrf_token_widget %} ... {% endblock %}`` to
customize the entire form field contents).

CSRF Protection in Login Forms
------------------------------

See :doc:`/security/form_login_setup` for a login form that is protected from
CSRF attacks. You can also configure the
:ref:`CSRF protection for the logout action <reference-security-logout-csrf>`.

.. _csrf-protection-in-html-forms:

Generating and Checking CSRF Tokens Manually
--------------------------------------------

Although Symfony Forms provide automatic CSRF protection by default, you may
need to generate and check CSRF tokens manually for example when using regular
HTML forms not managed by the Symfony Form component.

Consider a HTML form created to allow deleting items. First, use the
:ref:`csrf_token() Twig function <reference-twig-function-csrf-token>` to
generate a CSRF token in the template and store it as a hidden form field:

.. code-block:: html+twig

    <form action="{{ url('admin_post_delete', { id: post.id }) }}" method="post">
        {# the argument of csrf_token() is an arbitrary string used to generate the token #}
        <input type="hidden" name="token" value="{{ csrf_token('delete-item') }}"/>

        <button type="submit">Delete item</button>
    </form>

Then, get the value of the CSRF token in the controller action and use the
:method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController::isCsrfTokenValid`
to check its validity::

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    // ...

    public function delete(Request $request): Response
    {
        $submittedToken = $request->request->get('token');

        // 'delete-item' is the same value used in the template to generate the token
        if ($this->isCsrfTokenValid('delete-item', $submittedToken)) {
            // ... do something, like deleting an object
        }
    }

CSRF Tokens and Compression Side-Channel Attacks
------------------------------------------------

`BREACH`_ and `CRIME`_ are security exploits against HTTPS when using HTTP
compression. Attackers can leverage information leaked by compression to recover
targeted parts of the plaintext. To mitigate these attacks, and prevent an
attacker from guessing the CSRF tokens, a random mask is prepended to the token
and used to scramble it.

.. versionadded:: 5.3

    The randomization of tokens was introduced in Symfony 5.3

.. _`Cross-site request forgery`: https://en.wikipedia.org/wiki/Cross-site_request_forgery
.. _`BREACH`: https://en.wikipedia.org/wiki/BREACH
.. _`CRIME`: https://en.wikipedia.org/wiki/CRIME
