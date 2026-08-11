How to Implement CSRF Protection
================================

CSRF, or `Cross-site request forgery`_, is a type of attack where a malicious actor
tricks a user into performing actions on a web application without their knowledge
or consent.

The attack is based on the trust that a web application has in a user's browser
(e.g. on session cookies). Here's a real example of a CSRF attack: a malicious
actor could create the following website:

.. code-block:: html

    <html>
        <body>
            <form action="https://example.com/settings/update-email" method="POST">
                <input type="hidden" name="email" value="malicious-actor-address@some-domain.com"/>
            </form>
            <script>
                document.forms[0].submit();
            </script>

            <!-- some content here to distract the user -->
        </body>
    </html>

If you visit this website (e.g. by clicking on some email link or some social
network post) and you were already logged in on the ``https://example.com`` site,
the malicious actor could change the email address associated with your account
(effectively taking over your account) without you even being aware of it.

An effective way of preventing CSRF attacks is to use anti-CSRF tokens. These are
unique tokens added to forms as hidden fields. The legitimate server validates them to
ensure that the request originated from the expected source and not some other
malicious website.

Anti-CSRF tokens can be managed in two ways: using a **stateful** approach,
where tokens are stored in the session and are unique per user and action; or a
**stateless** approach, where tokens are generated on the client side.

Installation
------------

Symfony provides all the needed features to generate and validate the anti-CSRF
tokens. Before using them, install this package in your project:

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
            csrf_protection: true

    .. code-block:: php

        // config/packages/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'csrf_protection' => true,
            ],
        ]);

By default, the tokens used for CSRF protection are stored in the session.
That's why a session is started automatically as soon as you render a form
with CSRF protection.

.. _caching-pages-that-contain-csrf-protected-forms:

This leads to many strategies to help with caching pages that include CSRF
protected forms, among them:

* Embed the form inside an uncached :doc:`ESI fragment </http_cache/esi>` and
  cache the rest of the page contents;
* Cache the entire page and load the form via an uncached AJAX request;
* Cache the entire page and use :ref:`hinclude.js <templates-hinclude>` to
  load the CSRF token with an uncached AJAX request and replace the form
  field value with it.

The most effective way to cache pages that need CSRF protected forms is to use
:ref:`stateless CSRF tokens <csrf-stateless-tokens>`, as explained below.

.. _csrf-protection-forms:

CSRF Protection in Symfony Forms
--------------------------------

:doc:`Symfony Forms </forms>` include CSRF tokens by default and Symfony also
checks them automatically for you. So, when using Symfony Forms, you don't have
to do anything to be protected against CSRF attacks.

.. note::

    According to `OWASP best practices`_, CSRF protection is only required for
    **state-changing operations**, which must not use ``GET`` requests (as per the
    HTTP specification). Moreover, including CSRF tokens in ``GET`` request
    parameters can cause them to leak through browser history, log files, network
    utilities, and Referer headers.

    If one of your forms uses GET (for example, a read-only search form), you
    can :ref:`configure the form to disable CSRF protection <form-csrf-configuration>`.

.. _form-csrf-customization:

By default Symfony adds the CSRF token in a hidden field called ``_token``, but
this can be customized (1) globally for all forms and (2) on a form-by-form basis.
Globally, you can configure it under the ``framework.form`` option:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # ...
            form:
                csrf_protection:
                    enabled: true
                    field_name: 'custom_token_name'

    .. code-block:: php

        // config/packages/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'form' => [
                    'csrf_protection' => [
                        'enabled' => true,
                        'field_name' => 'custom_token_name',
                    ],
                ],
            ],
        ]);

.. _form-csrf-configuration:

On a form-by-form basis, you can configure the CSRF protection in the ``setDefaults()``
method of each form::

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
                'csrf_field_name' => 'custom_token_name',
                // an arbitrary string used to generate the value of the token
                // using a different string for each form improves its security
                // when using stateful tokens (which is the default)
                'csrf_token_id'   => 'task_item',
            ]);
        }

        // ...
    }

You can also customize the rendering of the CSRF form field by creating a custom
:doc:`form theme </form/form_themes>` and using ``csrf_token`` as the prefix of
the field (e.g. define ``{% block csrf_token_widget %} ... {% endblock %}`` to
customize the entire form field contents).

.. _csrf-protection-in-login-forms:

CSRF Protection in Login Form and Logout Action
-----------------------------------------------

Read the following:

* :ref:`CSRF Protection in Login Forms <form_login-csrf>`;
* :ref:`CSRF protection for the logout action <reference-security-logout-csrf>`.

.. _csrf-protection-in-html-forms:

Generating and Checking CSRF Tokens Manually
--------------------------------------------

Although Symfony Forms provide automatic CSRF protection by default, you may
need to generate and check CSRF tokens manually for example when using regular
HTML forms not managed by the Symfony Form component.

Consider an HTML form created to allow deleting items. First, use the
:ref:`csrf_token() Twig function <reference-twig-function-csrf-token>` to
generate a CSRF token in the template and store it as a hidden form field:

.. code-block:: html+twig

    <form action="{{ url('admin_post_delete', { id: post.id }) }}" method="post">
        {# the argument of csrf_token() is the token ID, an arbitrary string used to generate the token #}
        <input type="hidden" name="token" value="{{ csrf_token('delete-item') }}">

        <button type="submit">Delete item</button>
    </form>

Then, get the value of the CSRF token in the controller action and use the
:method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController::isCsrfTokenValid`
method to check its validity, passing the same token ID used in the template::

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    // ...

    public function delete(Request $request): Response
    {
        $submittedToken = $request->getPayload()->get('token');

        // 'delete-item' is the same token ID used in the template to generate the token
        if ($this->isCsrfTokenValid('delete-item', $submittedToken)) {
            // ... do something, like deleting an object
        }
    }

.. _csrf-controller-attributes:

Alternatively you can use the
:class:`Symfony\\Component\\Security\\Http\\Attribute\\IsCsrfTokenValid`
attribute on the controller action::

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
    // ...

    #[IsCsrfTokenValid('delete-item', tokenKey: 'token')]
    public function delete(): Response
    {
        // ... do something, like deleting an object
    }

Suppose you want a CSRF token per item, so in the template you have something like the following:

.. code-block:: html+twig

    <form action="{{ url('admin_post_delete', { id: post.id }) }}" method="post">
        {# the argument of csrf_token() is the token ID, an arbitrary string used to generate the token #}
        <input type="hidden" name="token" value="{{ csrf_token('delete-item-' ~ post.id) }}">

        <button type="submit">Delete item</button>
    </form>

This attribute can also be applied to a controller class. When used this way,
the CSRF token validation will be applied to **all actions** defined in that
controller::

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
    // ...

    #[IsCsrfTokenValid('the token ID')]
    final class SomeController extends AbstractController
    {
        // ...
    }

The :class:`Symfony\\Component\\Security\\Http\\Attribute\\IsCsrfTokenValid`
attribute also accepts an :class:`Symfony\\Component\\ExpressionLanguage\\Expression`
object evaluated to the id::

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
    // ...

    #[IsCsrfTokenValid(new Expression('"delete-item-" ~ args["post"].getId()'), tokenKey: 'token')]
    public function delete(Post $post): Response
    {
        // ... do something, like deleting an object
    }

By default, the ``IsCsrfTokenValid`` attribute performs the CSRF token check for
all HTTP methods. You can restrict this validation to specific methods using the
``methods`` parameter. If the request uses a method not listed in the ``methods``
array, the attribute is ignored for that request, and no CSRF validation occurs::

    #[IsCsrfTokenValid('delete-item', tokenKey: 'token', methods: ['DELETE'])]
    public function delete(Post $post): Response
    {
        // ... delete the object
    }

You can also choose where the CSRF token is read from using the ``tokenSource``
parameter. This is a bitfield that allows you to combine different sources:

* ``IsCsrfTokenValid::SOURCE_PAYLOAD`` (default): request payload (POST body / json)
* ``IsCsrfTokenValid::SOURCE_QUERY``: query string
* ``IsCsrfTokenValid::SOURCE_HEADER``: request header

Example::

    #[IsCsrfTokenValid(
        'delete-item',
        tokenKey: 'token',
        tokenSource: IsCsrfTokenValid::SOURCE_PAYLOAD | IsCsrfTokenValid::SOURCE_QUERY
    )]
    public function delete(Post $post): Response
    {
        // ... delete the object
    }

The token is checked against each selected source, and validation fails if none match.

Generating and Checking CSRF Tokens in Services
-----------------------------------------------

The ``csrf_token()`` Twig function and the ``isCsrfTokenValid()`` controller
shortcut shown in the previous examples use the ``security.csrf.token_manager``
service internally. If you need to generate or check CSRF tokens in your own
services, use :doc:`autowiring </service_container/autowiring>` to inject that
service by type-hinting the
:class:`Symfony\\Component\\Security\\Csrf\\CsrfTokenManagerInterface`::

    // src/Service/SomeService.php
    namespace App\Service;

    use Symfony\Component\Security\Csrf\CsrfToken;
    use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

    class SomeService
    {
        public function __construct(
            private CsrfTokenManagerInterface $csrfTokenManager,
        ) {
        }

        public function someMethod(string $submittedToken): void
        {
            // gets the token value for the given token ID; if that ID doesn't have
            // a token yet, a new token is generated for it
            $csrfToken = $this->csrfTokenManager->getToken('delete-item');
            $tokenValue = $csrfToken->getValue();

            // checks if the given token value is valid for the given token ID
            $csrfToken = new CsrfToken('delete-item', $submittedToken);
            $isValid = $this->csrfTokenManager->isTokenValid($csrfToken);

            // ...
        }
    }

The token manager also provides the ``refreshToken()`` and ``removeToken()``
methods to regenerate or invalidate the token of the given ID.

CSRF Tokens and Compression Side-Channel Attacks
------------------------------------------------

`BREACH`_ and `CRIME`_ are security exploits against HTTPS when using HTTP
compression. Attackers can leverage information leaked by compression to recover
targeted parts of the plaintext. To mitigate these attacks, and prevent an
attacker from guessing the CSRF tokens, a random mask is prepended to the token
and used to scramble it.

.. _csrf-stateless-tokens:

Stateless CSRF Tokens
---------------------

Traditionally, CSRF tokens are stateful, meaning they're stored in the session.
However, some token IDs can be declared as stateless using the
``stateless_token_ids`` option. Stateless CSRF tokens are enabled by default
in applications using :ref:`Symfony Flex <symfony-flex>`.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/csrf.yaml
        framework:
            # ...
            csrf_protection:
                stateless_token_ids: ['submit', 'authenticate', 'logout']

    .. code-block:: php

        // config/packages/csrf.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'csrf_protection' => [
                    'stateless_token_ids' => ['submit', 'authenticate', 'logout'],
                ],
            ],
        ]);

Stateless CSRF tokens provide protection without relying on the session. This
allows you to fully cache pages while still protecting against CSRF attacks.

When validating a stateless CSRF token, Symfony checks the ``Origin`` and
``Referer`` headers of the incoming HTTP request. If either header matches the
application's target origin (i.e. its domain), the token is considered valid.

This mechanism relies on the application being able to determine its own origin.
If you're behind a reverse proxy, make sure it's properly configured. See
:doc:`/deployment/proxies`.

Using a Default Token ID
~~~~~~~~~~~~~~~~~~~~~~~~

Stateful CSRF tokens are typically scoped per form or action, while stateless
tokens don't require many identifiers.

In the example above, the ``authenticate`` and ``logout`` identifiers are listed
because they are used by default in the Symfony Security component. The ``submit``
identifier is included so that form types defined by the application can also use
CSRF protection by default.

The following configuration applies only to form types registered via
:ref:`autoconfiguration <services-autoconfigure>` (which is the default for your
own services), and it sets ``submit`` as their default token identifier:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/csrf.yaml
        framework:
            form:
                csrf_protection:
                    token_id: 'submit'

    .. code-block:: php

        // config/packages/csrf.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'form' => [
                    'csrf_protection' => [
                        'token_id' => 'submit',
                    ],
                ],
            ],
        ]);

Forms configured with a token identifier listed in the above ``stateless_token_ids``
option will use the stateless CSRF protection.

.. _generating-csrf-token-using-javascript:

Generating a CSRF Token Using JavaScript
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In addition to the ``Origin`` and ``Referer`` HTTP headers, stateless CSRF protection
can also validate tokens using a cookie and a header (named ``csrf-token`` by
default; see the :ref:`CSRF configuration reference <reference-framework-csrf-protection>`).

These additional checks are part of the **defense-in-depth** strategy provided by
stateless CSRF protection. They are optional and require `some JavaScript`_ to
be enabled. This JavaScript generates a cryptographically secure random token
when a form is submitted. It then inserts the token into the form's hidden CSRF
field and sends it in both a cookie and a request header.

On the server side, CSRF token validation compares the values in the cookie and
the header. This "double-submit" protection relies on the browser's same-origin
policy and is further hardened by:

* generating a new token for each submission (to prevent cookie fixation);
* using ``samesite=strict`` and ``__Host-`` cookie attributes (to enforce HTTPS
  and limit the cookie to the current domain).

By default, the Symfony JavaScript snippet expects the hidden CSRF field to be
named ``_csrf_token`` or to include the ``data-controller="csrf-protection"``
attribute. You can adapt this logic to your needs as long as the same protocol
is followed.

To prevent validation from being downgraded, an extra behavioral check is performed:
if (and only if) a session already exists, successful "double-submit" is remembered
and becomes required for future requests. This ensures that once the optional cookie/header
validation has been proven effective, it remains enforced for that session.

.. note::

    Enforcing "double-submit" validation on all requests is not recommended,
    as it may lead to a broken user experience. The opportunistic approach
    described above is preferred, allowing the application to gracefully
    fall back to ``Origin`` / ``Referer`` checks when JavaScript is unavailable.

.. _`Cross-site request forgery`: https://en.wikipedia.org/wiki/Cross-site_request_forgery
.. _`OWASP best practices`: https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html
.. _`BREACH`: https://en.wikipedia.org/wiki/BREACH
.. _`CRIME`: https://en.wikipedia.org/wiki/CRIME
.. _`some JavaScript`: https://github.com/symfony/recipes/blob/main/symfony/stimulus-bundle/2.20/assets/controllers/csrf_protection_controller.js
