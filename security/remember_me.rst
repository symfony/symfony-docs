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
                        secret:   '%kernel.secret%' # required
                        lifetime: 604800 # 1 week in seconds
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
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config>
                <!-- ... -->

                <firewall name="main">
                    <!-- ... -->

                    <!-- secret: required
                         lifetime: 604800 is 1 week in seconds -->
                    <remember-me
                        secret="%kernel.secret%"
                        lifetime="604800"
                    />
                    <!-- by default, the feature is enabled by checking a checkbox
                         in the login form (see below), add always-remember-me="true"
                         to always enable it. -->
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security): void {
            // ...
            $security->firewall('main')
                // ...
                ->rememberMe()
                    ->secret('%kernel.secret%') // required
                    ->lifetime(604800) // 1 week in seconds

                    // by default, the feature is enabled by checking a
                    // checkbox in the login form (see below), uncomment
                    // the following line to always enable it.
                    // ->alwaysRememberMe(true)
            ;
        };

The ``secret`` option is the only required option and it is used to sign
the remember me cookie. It's common to use the ``kernel.secret`` parameter,
which is defined using the ``APP_SECRET`` environment variable.

After enabling the ``remember_me`` system in the configuration, there are a
couple more things to do before remember me works correctly:

#. :ref:`Add an opt-in checkbox to activate remember me <security-remember-me-activate>`;
#. :ref:`Use an authenticator that supports remember me <security-remember-me-authenticator>`;
#. Optionally, :ref:`configure how remember me cookies are stored and validated <security-remember-me-storage>`.

After this, the remember me cookie will be created upon successful
authentication. For some pages/actions, you can
:ref:`force a user to fully authenticate <security-remember-me-authorization>`
(i.e. not through a remember me cookie) for better security.

.. note::

    The ``remember_me`` setting contains many settings to configure the
    cookie created by this feature. See `Customizing the Remember Me Cookie`_
    for a full description of these settings.

.. _security-remember-me-activate:

Activating the Remember Me System
---------------------------------

Using the remember me cookie is not always appropriate (e.g. you should not
use it on a shared PC). This is why by default, Symfony requires your users
to opt-in to the remember me system via a request parameter.

Remember Me for Form Login
~~~~~~~~~~~~~~~~~~~~~~~~~~

This request parameter is often set via a checkbox in the login form. This
checkbox must have a name of ``_remember_me``:

.. code-block:: html+twig

    {# templates/security/login.html.twig #}
    <form method="post">
        {# ... your form fields #}

        <label>
            <input type="checkbox" name="_remember_me" checked>
            Keep me logged in
        </label>

        {# ... #}
    </form>

.. note::

    Optionally, you can configure a custom name for this checkbox using the
    ``name`` setting under the ``remember_me`` section.

Remember Me for JSON Login
~~~~~~~~~~~~~~~~~~~~~~~~~~

If you implement the login via an API that uses :ref:`JSON Login <json-login>`
you can add a ``_remember_me`` key to the body of your POST request.

.. code-block:: json

    {
        "username": "dunglas@example.com",
        "password": "MyPassword",
        "_remember_me": true
    }

.. note::

    Optionally, you can configure a custom name for this key using the
    ``name`` setting under the ``remember_me`` section of your firewall.

.. versionadded:: 6.3

    The JSON login ``_remember_me`` option was introduced in Symfony 6.3.

Always activating Remember Me
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Sometimes, you may wish to always activate the remember me system and not
allow users to opt-out. In these cases, you can use the
``always_remember_me`` setting:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    remember_me:
                        secret: '%kernel.secret%'
                        # ...
                        always_remember_me: true

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config>
                <!-- ... -->

                <firewall name="main">
                    <!-- ... -->

                    <remember-me
                        secret="%kernel.secret%"
                        always-remember-me="true"
                    />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security): void {
            // ...
            $security->firewall('main')
                // ...
                ->rememberMe()
                    ->secret('%kernel.secret%')
                    // ...
                    ->alwaysRememberMe(true)
            ;
        };

Now, no request parameter is checked and each successful authentication
will produce a remember me cookie.

.. _security-remember-me-authenticator:

Add Remember Me Support to the Authenticator
--------------------------------------------

Not all authentication methods support remember me (e.g. HTTP Basic
authentication doesn't have support). An authenticator indicates support
using a ``RememberMeBadge`` on the :ref:`security passport <security-passport>`.

After logging in, you can use the security profiler to see if this badge is
present:

.. image:: /_images/security/profiler-badges.png
    :alt: The Security page of the Symfony profiler, with the "Authenticators" tab showing the remember me badge in the passport object.

Without this badge, remember me will not be activated (regardless of all
other settings).

Add Remember Me Support to Custom Authenticators
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When you use a custom authenticator, you must add a ``RememberMeBadge``
manually::

    // src/Service/LoginAuthenticator.php
    namespace App\Service;

    // ...
    use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
    use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
    use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

    class LoginAuthenticator extends AbstractAuthenticator
    {
        public function authenticate(Request $request): Passport
        {
            // ...

            return new Passport(
                new UserBadge(...),
                new PasswordCredentials(...),
                [
                    new RememberMeBadge(),
                ]
            );
        }
    }

.. _security-remember-me-storage:

Customize how Remember Me Tokens are Stored
-------------------------------------------

Remember me cookies contain a token that is used to verify the user's
identity. As these tokens are long-lived, it is important to take
precautions to allow invalidating any generated tokens.

Symfony provides two ways to validate remember me tokens:

Signature based tokens
    By default, the remember me cookie contains a signature based on
    properties of the user. If the properties change, the signature changes
    and already generated tokens are no longer considered valid. See
    :ref:`how to use them <security-remember-me-signature>` for more
    information.

Persistent tokens
    Persistent tokens store any generated token (e.g. in a database). This
    allows you to invalidate tokens by changing the rows in the database.
    See :ref:`how to store tokens <security-remember-me-persistent>` for more
    information.

.. note::

    You can also write your own custom remember me handler by creating a
    class that extends
    :class:`Symfony\\Component\\Security\\Http\\RememberMe\\AbstractRememberMeHandler`
    (or implements :class:`Symfony\\Component\\Security\\Http\\RememberMe\\RememberMeHandlerInterface`).
    You can then configure this custom handler by configuring the service
    ID in the ``service`` option under ``remember_me``.

.. _security-remember-me-signature:

Using Signed Remember Me Tokens
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By default, remember me cookies contain a *hash* that is used to validate
the cookie. This hash is computed based on configured
signature properties.

These properties are always included in the hash:

* The user identifier (returned by
  :method:`Symfony\\Component\\Security\\Core\\User\\UserInterface::getUserIdentifier`);
* The expiration timestamp.

On top of these, you can configure custom properties using the
``signature_properties`` setting (defaults to ``password``). The properties
are fetched from the user object using the
:doc:`PropertyAccess component </components/property_access>` (e.g. using
``getUpdatedAt()`` or a public ``$updatedAt`` property when using
``updatedAt``).

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    remember_me:
                        secret: '%kernel.secret%'
                        # ...
                        signature_properties: ['password', 'updatedAt']

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config>
                <!-- ... -->

                <firewall name="main">
                    <!-- ... -->

                    <remember-me secret="%kernel.secret%">
                        <signature-property>password</signature-property>
                        <signature-property>updatedAt</signature-property>
                    </remember-me>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security): void {
            // ...
            $security->firewall('main')
                // ...
                ->rememberMe()
                    ->secret('%kernel.secret%')
                    // ...
                    ->signatureProperties(['password', 'updatedAt'])
            ;
        };

In this example, the remember me cookie will no longer be considered valid
if the ``updatedAt``, password or user identifier for this user changes.

.. tip::

    Signature properties allow for some advanced usages without having to
    set-up storage for all remember me tokens. For instance, you can add a
    ``forceReloginAt`` field to your user and to the signature properties.
    This way, you can invalidate all remember me tokens from a user by
    changing this timestamp.

.. _security-remember-me-persistent:

Storing Remember Me Tokens in the Database
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

As remember me tokens are often long-lived, you might prefer to save them in
a database to have full control over them. Symfony comes with support for
persistent remember me tokens.

This implementation uses a *remember me token provider* for storing and
retrieving the tokens from the database. The DoctrineBridge provides a
token provider using Doctrine.

You can enable the doctrine token provider using the ``doctrine`` setting:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    remember_me:
                        secret: '%kernel.secret%'
                        # ...
                        token_provider:
                            doctrine: true

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config>
                <!-- ... -->

                <firewall name="main">
                    <!-- ... -->

                    <remember-me secret="%kernel.secret%">
                        <token-provider doctrine="true"/>
                    </remember-me>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security): void {
            // ...
            $security->firewall('main')
                // ...
                ->rememberMe()
                    ->secret('%kernel.secret%')
                    // ...
                    ->tokenProvider([
                        'doctrine' => true,
                    ])
            ;
        };

This also instructs Doctrine to create a table for the remember me tokens.
If you use the DoctrineMigrationsBundle, you can create a new migration for
this:

.. code-block:: terminal

    $ php bin/console doctrine:migrations:diff

    # and optionally run the migrations locally
    $ php bin/console doctrine:migrations:migrate

Otherwise, you can use the ``doctrine:schema:update`` command:

.. code-block:: terminal

    # get the required SQL code
    $ php bin/console doctrine:schema:update --dump-sql

    # run the SQL in your DB client, or let the command run it for you
    $ php bin/console doctrine:schema:update --force

Implementing a Custom Token Provider
....................................

You can also create a custom token provider by creating a class that
implements :class:`Symfony\\Component\\Security\\Core\\Authentication\\RememberMe\\TokenProviderInterface`.

Then, configure the service ID of your custom token provider as ``service``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    remember_me:
                        # ...
                        token_provider:
                            service: App\Security\RememberMe\CustomTokenProvider

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config>
                <!-- ... -->

                <firewall name="main">
                    <!-- ... -->

                    <remember-me>
                        <token-provider service="App\Security\RememberMe\CustomTokenProvider"/>
                    </remember-me>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use App\Security\RememberMe\CustomTokenProvider;
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security): void {
            // ...
            $security->firewall('main')
                // ...
                ->rememberMe()
                    // ...
                    ->tokenProvider([
                        'service' => CustomTokenProvider::class,
                    ])
            ;
        };

.. _security-remember-me-authorization:

Forcing the User to Re-Authenticate before Accessing certain Resources
----------------------------------------------------------------------

When the user returns to your site, they are authenticated automatically based
on the information stored in the remember me cookie. This allows the user
to access protected resources as if the user had actually authenticated upon
visiting the site.

In some cases, however, you may want to force the user to actually re-authenticate
before accessing certain resources. For example, you might not allow "remember me"
users to change their password. You can do this by leveraging a few special
"attributes"::

    // src/Controller/AccountController.php
    // ...

    public function accountInfo(): Response
    {
        // allow any authenticated user - we don't care if they just
        // logged in, or are logged in via a remember me cookie
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        // ...
    }

    public function resetPassword(): Response
    {
        // require the user to log in during *this* session
        // if they were only logged in via a remember me cookie, they
        // will be redirected to the login page
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // ...
    }

.. tip::

    There is also a ``IS_REMEMBERED`` attribute that grants access *only*
    when the user is authenticated via the remember me mechanism.

Customizing the Remember Me Cookie
----------------------------------

The ``remember_me`` configuration contains many options to customize the
cookie created by the system:

``name`` (default value: ``REMEMBERME``)
    The name of the cookie used to keep the user logged in. If you enable the
    ``remember_me`` feature in several firewalls of the same application, make sure
    to choose a different name for the cookie of each firewall. Otherwise, you'll
    face lots of security related problems.

``lifetime`` (default value: ``31536000`` i.e. 1 year in seconds)
    The number of seconds after which the cookie will be expired. This
    defines the maximum time between two visits for the user to remain
    authenticated.

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
