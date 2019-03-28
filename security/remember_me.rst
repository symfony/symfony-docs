.. index::
   single: Security; "Remember me"

How to Add "Remember Me" Login Functionality
============================================

Once a user is authenticated, their credentials are typically stored in the
session. This means that when the session ends they will be logged out and
have to provide their login details again next time they wish to access the
application. You can allow users to choose to stay logged in for longer than
the session lasts using a cookie with the ``remember_me`` firewall option:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    remember_me:
                        secret:   '%kernel.secret%'
                        lifetime: 604800 # 1 week in seconds
                        path:     /
                        # by default, the feature is enabled by checking a
                        # checkbox in the login form (see below), uncomment the
                        # following line to always enable it.
                        #always_remember_me: true

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->

                <firewall name="main">
                    <!-- ... -->

                    <!-- 604800 is 1 week in seconds -->
                    <remember-me
                        secret="%kernel.secret%"
                        lifetime="604800"
                        path="/"/>
                    <!-- by default, the feature is enabled by checking a checkbox
                         in the login form (see below), add always-remember-me="true"
                         to always enable it. -->
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        $container->loadFromExtension('security', [
            // ...

            'firewalls' => [
                'main' => [
                    // ...
                    'remember_me' => [
                        'secret'   => '%kernel.secret%',
                        'lifetime' => 604800, // 1 week in seconds
                        'path'     => '/',
                        // by default, the feature is enabled by checking a
                        // checkbox in the login form (see below), uncomment
                        // the following line to always enable it.
                        //'always_remember_me' => true,
                    ],
                ],
            ],
        ]);

The ``remember_me`` firewall defines the following configuration options:

``secret`` (**required**)
    The value used to encrypt the cookie's content. It's common to use the
    ``secret`` value defined in the ``APP_SECRET`` environment variable.

``name`` (default value: ``REMEMBERME``)
    The name of the cookie used to keep the user logged in. If you enable the
    ``remember_me`` feature in several firewalls of the same application, make sure
    to choose a different name for the cookie of each firewall. Otherwise, you'll
    face lots of security related problems.

``lifetime`` (default value: ``31536000``)
    The number of seconds during which the user will remain logged in. By default
    users are logged in for one year.

``path`` (default value: ``/``)
    The path where the cookie associated with this feature is used. By default
    the cookie will be applied to the entire website but you can restrict to a
    specific section (e.g. ``/forum``, ``/admin``).

``domain`` (default value: ``null``)
    The domain where the cookie associated with this feature is used. By default
    cookies use the current domain obtained from ``$_SERVER``.

``secure`` (default value: ``false``)
    If ``true``, the cookie associated with this feature is sent to the user
    through an HTTPS secure connection.

``httponly`` (default value: ``true``)
    If ``true``, the cookie associated with this feature is accessible only
    through the HTTP protocol. This means that the cookie won't be accessible
    by scripting languages, such as JavaScript.

``samesite`` (default value: ``null``)
    If set to ``strict``, the cookie associated with this feature will not
    be sent along with cross-site requests, even when following a regular link.

``remember_me_parameter`` (default value: ``_remember_me``)
    The name of the form field checked to decide if the "Remember Me" feature
    should be enabled or not. Keep reading this article to know how to enable
    this feature conditionally.

``always_remember_me`` (default value: ``false``)
    If ``true``, the value of the ``remember_me_parameter`` is ignored and the
    "Remember Me" feature is always enabled, regardless of the desire of the
    end user.

``token_provider`` (default value: ``null``)
    Defines the service id of a token provider to use. By default, tokens are
    stored in a cookie. For example, you might want to store the token in a
    database, to not have a (hashed) version of the password in a cookie. The
    DoctrineBridge comes with a
    ``Symfony\Bridge\Doctrine\Security\RememberMe\DoctrineTokenProvider`` that
    you can use.

Forcing the User to Opt-Out of the Remember Me Feature
------------------------------------------------------

It's a good idea to provide the user with the option to use or not use the
remember me functionality, as it will not always be appropriate. The usual
way of doing this is to add a checkbox to the login form. By giving the checkbox
the name ``_remember_me`` (or the name you configured using ``remember_me_parameter``),
the cookie will automatically be set when the checkbox is checked and the user
successfully logs in. So, your specific login form might ultimately look like
this:

.. code-block:: html+twig

    {# templates/security/login.html.twig #}
    <form method="post">
        {# ... your form fields #}

        <input type="checkbox" id="remember_me" name="_remember_me" checked/>
        <label for="remember_me">Keep me logged in</label>

        {# ... #}
    </form>

The user will then automatically be logged in on subsequent visits while
the cookie remains valid.

Forcing the User to Re-Authenticate before Accessing certain Resources
----------------------------------------------------------------------

When the user returns to your site, they are authenticated automatically based
on the information stored in the remember me cookie. This allows the user
to access protected resources as if the user had actually authenticated upon
visiting the site.

In some cases, however, you may want to force the user to actually re-authenticate
before accessing certain resources. For example, you might allow "remember me"
users to change their password. You can do this by leveraing a few special "roles"::

    // src/Controller/AccountController.php
    // ...

    public function accountInfo()
    {
        // allow any authenticated user - we don't care if they just
        // logged in, or are logged in via a remember me cookie
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        // ...
    }

    public function resetPassword()
    {
        // require the user to log in during *this* session
        // if they were only logged in via a remember me cookie, they
        // will be redirected to the login page
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // ...
    }
