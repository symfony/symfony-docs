.. index::
   single: Security

Security
========

.. admonition:: Screencast
    :class: screencast

    Do you prefer video tutorials? Check out the `Symfony Security screencast series`_.

Symfony's security system is powerful and comprehensive, and as such it can be challenging
to set up.

Don't worry!

Here you will learn how to set up your app's security system step-by-step:

#. :ref:`Installing security support <security-installation>`;

#. :ref:`Create your User Class <create-user-class>`;

#. :ref:`Authentication & Firewalls <security-yaml-firewalls>`;

#. :ref:`Denying access to your app (authorization) <security-authorization>`;

#. :ref:`Fetching the current User object <retrieving-the-user-object>`.

There are links to more advanced security configuration topics at the bottom of this page.

.. _security-installation:

1) Installation
---------------

In applications using :ref:`Symfony Flex <symfony-flex>`, run this command to
install the security feature:

.. code-block:: terminal

    $ composer require symfony/security-bundle


.. tip::

    Symfony 5.4 has a :doc:`new authenticator-based security system </security/authenticator_manager>`
    that will become the de facto security system in Symfony 6.0. This new system is almost fully backwards compatible with the
    current Symfony security system.

    Add the following to your security configuration to start using the new authenticator-based security system:

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/security.yaml
            security:
                enable_authenticator_manager: true
                # ...

        .. code-block:: xml

            <!-- config/packages/security.xml -->
            <?xml version="1.0" encoding="UTF-8"?>
            <srv:container xmlns="http://symfony.com/schema/dic/security"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:srv="http://symfony.com/schema/dic/services"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd
                    http://symfony.com/schema/dic/security
                    https://symfony.com/schema/dic/security/security-1.0.xsd">

                <config enable-authenticator-manager="true">
                    <!-- ... -->
                </config>
            </srv:container>

        .. code-block:: php

            // config/packages/security.php
            use Symfony\Config\SecurityConfig;

            return static function (SecurityConfig $security) {
                $security->enableAuthenticatorManager(true);
                // ...
            };

    Without this configuration, your app will continue to use the deprecated security system that
    you know from Symfony 4.4, until such time that the deprecated system is removed in Symfony 6.0.

.. _initial-security-yml-setup-authentication:
.. _initial-security-yaml-setup-authentication:
.. _create-user-class:

2a) Create your User Class
--------------------------

No matter *how* you will authenticate (e.g. login form or API tokens) or *where*
your user data will be stored (database, single sign-on), the next step is always the same, namely:

Create a "User" class.

The easiest way is to use the `MakerBundle`_.

Let's assume that you want to store your user data in the database with Doctrine:

.. code-block:: terminal

    $ php bin/console make:user

    The name of the security user class (e.g. User) [User]:
    > User

    Do you want to store user data in the database (via Doctrine)? (yes/no) [yes]:
    > yes

    Enter a property name that will be the unique "display" name for the user (e.g.
    email, username, uuid [email]
    > email

    Does this app need to hash/check user passwords? (yes/no) [yes]:
    > yes

    created: src/Entity/User.php
    created: src/Repository/UserRepository.php
    updated: src/Entity/User.php
    updated: config/packages/security.yaml

That's it! The command asks several questions so that it can generate exactly what
you need.

The most important is the ``User.php`` file itself. The *only* rule about
your ``User`` class is that it *must* implement :class:`Symfony\\Component\\Security\\Core\\User\\UserInterface`.
The `MakerBundle`_ will do that automatically.

Feel free to add *any* other fields or logic you need.

If your ``User`` class is
an entity (see the example), you can use the :ref:`make:entity command <doctrine-add-more-fields>`
to add more fields. Also, make sure to make and run a migration for the new entity:

.. code-block:: terminal

    $ php bin/console make:migration
    $ php bin/console doctrine:migrations:migrate

.. _security-user-providers:
.. _where-do-users-come-from-user-providers:

2b) The "User Provider"
-----------------------

In addition to your ``User`` class, you also need a "user provider". This class is responsible for reloading the User data from the session and some other
optional features, such as :doc:`remember me </security/remember_me>` and
:doc:`impersonation </security/impersonating_user>`.

The ``make:user`` command will have automatically configured one in your
``security.yaml`` file under the ``providers`` key:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            providers:
                # used to reload user from session & other features (e.g. switch_user)
                app_user_provider:
                    entity:
                        class: App\Entity\User
                        property: email

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- used to reload user from session & other features (e.g. switch-user) -->
                <provider name="app_user_provider">
                    <entity class="App\Entity\User" property="email"/>
                </provider>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use App\Entity\User;
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            // ...

            // used to reload user from session & other features (e.g. switch_user)
            $security->provider('app_user_provider')
                ->entity()
                    ->class(User::class)
                    ->property('email');
        };


If your ``User`` class is an entity, you don't need to do anything else. But if
your class is *not* an entity, then ``make:user`` will also have generated a
``UserProvider`` class that you need to flush out and finalize. Learn more about user providers
here: :doc:`User Providers </security/user_provider>`.

.. _security-encoding-user-password:
.. _encoding-the-user-s-password:
.. _2c-encoding-passwords:

2c) Hashing Passwords
---------------------

Not all applications have "users" that need passwords. *If* your users have passwords,
you can control how those passwords are hashed in ``security.yaml``. The ``make:user``
command will pre-configure this for you:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            password_hashers:
                # use your user class name here
                App\Entity\User:
                    # Use native password hasher, which auto-selects the best
                    # possible hashing algorithm (starting from Symfony 5.3 this is "bcrypt")
                    algorithm: auto

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config>
                <!-- ... -->

                <security:password-hasher class="App\Entity\User"
                    algorithm="auto"
                    cost="12"/>

                <!-- ... -->
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use App\Entity\User;
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            // ...

            $security->passwordHasher(User::class)
                ->algorithm('auto')
                ->cost(12);

            // ...
        };

Now that Symfony knows *how* you want to hash the passwords, you can use the
``UserPasswordHasherInterface`` service to do this before saving your users to
the database.

.. _user-data-fixture:

For example, by using :ref:`DoctrineFixturesBundle <doctrine-fixtures>`, you can
create dummy database users:

.. code-block:: terminal

    $ php bin/console make:fixtures

    The class name of the fixtures to create (e.g. AppFixtures):
    > UserFixtures

Use this service to hash the passwords:

.. code-block:: diff

      // src/DataFixtures/UserFixtures.php

    + use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
      // ...

      class UserFixtures extends Fixture
      {
    +     private $passwordHasher;

    +     public function __construct(UserPasswordHasherInterface $passwordHasher)
    +     {
    +         $this->passwordHasher = $passwordHasher;
    +     }

          public function load(ObjectManager $manager)
          {
              $user = new User();
              // ...

    +         $user->setPassword($this->passwordHasher->hashPassword(
    +             $user,
    +             'the_new_password'
    +         ));

              // ...
          }
      }

As another example, in the ``RegistrationController`` class of your app, where you create and persist a new User entity, you will also use the ``UserPasswordHasherInterface`` service to hash the user's password.

.. code-block:: php

    class RegistrationController extends AbstractController
    {
        #[Route('/register', name: 'app_register')]
        public function register(
            Request $request,
            UserPasswordHasherInterface $passwordHasher,
            EntityManagerInterface $entityManager
        ): Response
        {
            $user = new User();
            $form = $this->createForm(RegistrationFormType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );

                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('app_profile');
            }

            return $this->render('registration/register.html.twig', [
                'registrationForm' => $form->createView(),
            ]);
        }
    }

If you need to manually hash a password, run the following command in your terminal:

.. code-block:: terminal

    $ php bin/console security:hash-password

.. _security-yaml-firewalls:
.. _security-firewalls:
.. _firewalls-authentication:

3a) Authentication & Firewalls
------------------------------

.. versionadded:: 5.1

    The ``lazy: true`` option was introduced in Symfony 5.1. Prior to version 5.1,
    it was enabled using ``anonymous: lazy``

The security system is configured in ``config/packages/security.yaml``. The *most*
important section is ``firewalls``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                dev:
                    pattern: ^/(_(profiler|wdt)|css|images|js)/
                    security: false
                main:
                    lazy: true

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config>
                <firewall name="dev"
                    pattern="^/(_(profiler|wdt)|css|images|js)/"
                    security="false"/>

                <firewall name="main"
                    lazy="true"/>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            $security->firewall('dev')
                ->pattern('^/(_(profiler|wdt)|css|images|js)/')
                ->security(false);

            $security->firewall('main')
                ->lazy(true);
        };

A "firewall" is your primary authentication system. Its configuration defines
*how* your users will be able to authenticate (e.g. login form, API token, etc).

Only one firewall is active on each request. Symfony uses the ``pattern`` key
to find the first match (you can also :doc:`match by host or other things </security/firewall_restriction>`).

The ``dev`` firewall is really a fake firewall. It ensures that you can access Symfony's dev tools - which live under URLs such as ``/_profiler``
and ``/_wdt``.

All *real* URLs are handled by the ``main`` firewall (no ``pattern`` key means
it matches *all* URLs).

A firewall can have many modes of authentication. In other words, it can employ many ways to ask the question "Who are you?".

Often, the user is unknown (i.e. not logged in) when they first visit your website.

If you go to the home page of your dev app, you *will* have access and you'll
see in the debug toolbar that you're "authenticated" as ``n/a``. The firewall verified that you have access to that resource, and that it
does not know your identity.

.. note::

    If you do not see the debug toolbar at the bottom of your dev app, install the :doc:`profiler </profiler>` with:

    .. code-block:: terminal

        $ composer require --dev symfony/profiler-pack

This means a request can have unauthenticated access to some resources,
while some actions (i.e. some pages or buttons) can still require specific
privileges.

The user can then access the home page, login form or registration form without being authenticated
as a unique user.

You'll learn later how to deny access to certain URLs, controllers, or part of
templates.

.. tip::

    The ``lazy`` anonymous mode prevents the session from being started if
    there is no need for authorization (i.e. explicit check for a user
    privilege). This is important to keep requests cacheable (see
    :doc:`/http_cache`).

Now that we understand our firewall, the next step is to create a way for your
users to authenticate!

.. _security-form-login:

3b) Authenticating Your Users
-----------------------------

Authentication in Symfony can feel a bit "magic" at first. That's because, instead
of building a route & controller to handle login, you'll activate an
*authentication provider*. This is code that runs automatically *before* your controller
is called.

Symfony has several :doc:`built-in authentication providers </security/auth_providers>`.
If your use-case matches one of these *exactly*, great! But, in most cases - including
a login form - *we recommend using the new Authentication Manager*, which is a class that allows
you to control *every* part of the authentication process (see the next section).

.. tip::

    If your application logs users in via a third-party service such as Google,
    Facebook or Twitter (social login), check out the `HWIOAuthBundle`_ community
    bundle.

Authenticator Managers
~~~~~~~~~~~~~~~~~~~~~~

The authenticator manager is a class that gives you *complete* control over your
authentication process. There are many different ways to build an authenticator;
here are a few common use-cases:

* :doc:`/security/form_login_setup`
* :doc:`/security/authenticator_manager` – see this for the most detailed
  description of authenticator managers and how they work

Guard Authenticators
~~~~~~~~~~~~~~~~~~~~

.. deprecated:: 5.3

    Guard authenticators are deprecated since Symfony 5.3 in favor of the
    :doc:`new authenticator manager system </security/authenticator_manager>`
    referenced in the previous section.

If you still need to use the deprecated Guard Authenticators, then please refer to the
separate documentation page:

* :doc:`/security/guard_authentication`

Limiting Login Attempts
~~~~~~~~~~~~~~~~~~~~~~~

Symfony provides basic protection against `brute force login attacks`_ if
you're using the :doc:`authenticator-based authenticators </security/authenticator_manager>`.

You must enable this using the ``login_throttling`` setting:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            enable_authenticator_manager: true

            firewalls:
                # ...

                main:
                    # ...

                    # by default, the feature allows 5 login attempts per minute
                    login_throttling: null

                    # configure the maximum login attempts (per minute)
                    login_throttling:
                        max_attempts: 3

                    # configure the maximum login attempts in a custom period of time
                    login_throttling:
                        max_attempts: 3
                        interval: '15 minutes'

                    # use a custom rate limiter via its service ID
                    login_throttling:
                        limiter: app.my_login_rate_limiter

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config enable-authenticator-manager="true">
                <!-- ... -->

                <firewall name="main">
                    <!-- by default, the feature allows 5 login attempts per minute -->
                    <login-throttling/>

                    <!-- configure the maximum login attempts (per minute) -->
                    <login-throttling max-attempts="3"/>

                    <!-- configure the maximum login attempts in a custom period of time -->
                    <login-throttling max-attempts="3" interval="15 minutes"/>

                    <!-- use a custom rate limiter via its service ID -->
                    <login-throttling limiter="app.my_login_rate_limiter"/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            $security->enableAuthenticatorManager(true);

            // ...
            $mainFirewall = $security->firewall('main');

            // by default, the feature allows 5 login attempts per minute
            $mainFirewall
                ->loginThrottling();

            // configure the maximum login attempts (per minute)
            $mainFirewall
                ->loginThrottling()
                    ->maxAttempts(3)
                    ->interval('15 minutes');

            // configure the maximum login attempts in a custom period of time
            $mainFirewall
                ->loginThrottling()
                    ->maxAttempts(3);
        };

By default, login attempts are limited on ``max_attempts`` (default: 5)
failed requests for ``IP address + username`` and ``5 * max_attempts``
failed requests for ``IP address``. The second limit protects against an
attacker using multiple usernames from bypassing the first limit, without
disrupting normal users on big networks (such as offices).

.. tip::

    Limiting the failed login attempts is only one basic protection against
    brute force attacks. The `OWASP Brute Force Attacks`_ guidelines mention
    several other protections that you should consider depending on the
    level of protection required.

If you need a more complex limiting algorithm, create a class that implements
:class:`Symfony\\Component\\HttpFoundation\\RateLimiter\\RequestRateLimiterInterface`
(or use
:class:`Symfony\\Component\\Security\\Http\\RateLimiter\\DefaultLoginRateLimiter`)
and set the ``limiter`` option to its service ID:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        framework:
            rate_limiter:
                # define 2 rate limiters (one for username+IP, the other for IP)
                username_ip_login:
                    policy: token_bucket
                    limit: 5
                    rate: { interval: '5 minutes' }

                ip_login:
                    policy: sliding_window
                    limit: 50
                    interval: '15 minutes'

        services:
            # our custom login rate limiter
            app.login_rate_limiter:
                class: Symfony\Component\Security\Http\RateLimiter\DefaultLoginRateLimiter
                arguments:
                    # globalFactory is the limiter for IP
                    $globalFactory: '@limiter.ip_login'
                    # localFactory is the limiter for username+IP
                    $localFactory: '@limiter.username_ip_login'

        security:
            firewalls:
                main:
                    # use a custom rate limiter via its service ID
                    login_throttling:
                        limiter: app.login_rate_limiter

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <framework:config>
                <framework:rate-limiter>
                    <!-- define 2 rate limiters (one for username+IP, the other for IP) -->
                    <framework:limiter name="username_ip_login"
                        policy="token_bucket"
                        limit="5"
                    >
                        <framework:rate interval="5 minutes"/>
                    </framework:limiter>

                    <framework:limiter name="ip_login"
                        policy="sliding_window"
                        limit="50"
                        interval="15 minutes"
                    />
                </framework:rate-limiter>
            </framework:config>

            <srv:services>
                <!-- our custom login rate limiter -->
                <srv:service id="app.login_rate_limiter"
                    class="Symfony\Component\Security\Http\RateLimiter\DefaultLoginRateLimiter"
                >
                    <!-- 1st argument is the limiter for IP -->
                    <srv:argument type="service" id="limiter.ip_login"/>
                    <!-- 2nd argument is the limiter for username+IP -->
                    <srv:argument type="service" id="limiter.username_ip_login"/>
                </srv:service>
            </srv:services>

            <config>
                <firewall name="main">
                    <!-- use a custom rate limiter via its service ID -->
                    <login-throttling limiter="app.login_rate_limiter"/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Component\DependencyInjection\ContainerBuilder;
        use Symfony\Component\DependencyInjection\Reference;
        use Symfony\Component\Security\Http\RateLimiter\DefaultLoginRateLimiter;
        use Symfony\Config\FrameworkConfig;
        use Symfony\Config\SecurityConfig;

        return static function (ContainerBuilder $container, FrameworkConfig $framework, SecurityConfig $security) {
            $framework->rateLimiter()
                ->limiter('username_ip_login')
                    ->policy('token_bucket')
                    ->limit(5)
                    ->rate()
                        ->interval('5 minutes')
            ;

            $framework->rateLimiter()
                ->limiter('ip_login')
                    ->policy('sliding_window')
                    ->limit(50)
                    ->interval('15 minutes')
            ;

            $container->register('app.login_rate_limiter', DefaultLoginRateLimiter::class)
                ->setArguments([
                    // 1st argument is the limiter for IP
                    new Reference('limiter.ip_login'),
                    // 2nd argument is the limiter for username+IP
                    new Reference('limiter.username_ip_login'),
                ]);

            $security->firewall('main')
                ->loginThrottling()
                    ->limiter('app.login_rate_limiter')
            ;
        };

.. _`security-authorization`:
.. _denying-access-roles-and-other-authorization:

4) Denying Access, Roles and Other Authorization
------------------------------------------------

Users can now log in to your app using your login form. Great! Now, you need to learn
how to deny access and work with the User object. This is called **authorization**,
and its job is to decide if a user can access a specific resource (a URL, a model object,
a method call, etc.).

The process of authorization has two different sides:

#. The user receives a specific set of roles when logging in (e.g. ``ROLE_ADMIN``).
#. You add code so that a resource (e.g. URL, controller) requires a specific
   "attribute" (most commonly a role like ``ROLE_ADMIN``) for access to be granted.

Roles
~~~~~

When a user logs in, Symfony calls the ``getRoles()`` method on your ``User``
object to determine the roles of the user. In the ``User`` class that we
generated earlier, the roles are an array that's stored in the database, and
every user is *always* given at least one role: ``ROLE_USER``::

    // src/Entity/User.php

    // ...
    class User
    {
        /**
         * @ORM\Column(type="json")
         */
        private $roles = [];

        // ...
        public function getRoles(): array
        {
            $roles = $this->roles;
            // guarantee every user at least has ROLE_USER
            $roles[] = 'ROLE_USER';

            return array_unique($roles);
        }
    }

This is a nice default, but you can do *whatever* you want to determine which roles
a user should have. Here are a few guidelines:

* Every role **must start with** ``ROLE_`` (otherwise, things won't work as expected)

* Other than the above rule, a role is just a string and you can invent whatever you
  need (e.g. ``ROLE_PRODUCT_ADMIN``).

You'll use these roles next to grant access to specific sections of your site.
You can also use a :ref:`role hierarchy <security-role-hierarchy>` where having
some roles automatically give you other roles.

.. _security-role-authorization:

Add Code to Deny Access
~~~~~~~~~~~~~~~~~~~~~~~

There are **two** ways to deny access to something:

#. The :ref:`access_control section in security.yaml <security-authorization-access-control>`
   allows you to protect URL patterns (e.g. ``/admin/*``). Simpler, but less flexible; or

#. Define it in your :ref:`controller (or other code) <security-securing-controller>`.

.. _security-authorization-access-control:

Securing URL patterns (access_control)
......................................

The most basic way to secure part of your app is to secure an entire URL pattern
in ``security.yaml``. For example, to require ``ROLE_ADMIN`` for all URLs that
start with ``/admin``, you can do the following:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                # ...
                main:
                    # ...

            access_control:
                # require ROLE_ADMIN for /admin*
                - { path: '^/admin', roles: ROLE_ADMIN }

                # or require ROLE_ADMIN or IS_AUTHENTICATED_FULLY for /admin*
                - { path: '^/admin', roles: [IS_AUTHENTICATED_FULLY, ROLE_ADMIN] }

                # the 'path' value can be any valid regular expression
                # (this one will match URLs like /api/post/7298 and /api/comment/528491)
                - { path: ^/api/(post|comment)/\d+$, roles: ROLE_USER }

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config>
                <!-- ... -->

                <firewall name="main">
                    <!-- ... -->
                </firewall>

                <!-- require ROLE_ADMIN for /admin* -->
                <rule path="^/admin" role="ROLE_ADMIN"/>

                <!-- require ROLE_ADMIN or IS_AUTHENTICATED_FULLY for /admin* -->
                <rule path="^/admin">
                    <role>ROLE_ADMIN</role>
                    <role>IS_AUTHENTICATED_FULLY</role>
                </rule>

                <!-- the 'path' value can be any valid regular expression
                     (this one will match URLs like /api/post/7298 and /api/comment/528491) -->
                <rule path="^/api/(post|comment)/\d+$" role="ROLE_USER"/>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            $security->enableAuthenticatorManager(true);

            // ...
            $security->firewall('main')
            // ...
            ;

            // require ROLE_ADMIN for /admin*
            $security->accessControl()
                ->path('^/admin')
                ->roles(['ROLE_ADMIN']);

            // require ROLE_ADMIN or IS_AUTHENTICATED_FULLY for /admin*
            $security->accessControl()
                ->path('^/admin')
                ->roles(['ROLE_ADMIN', 'IS_AUTHENTICATED_FULLY']);

            // the 'path' value can be any valid regular expression
            // (this one will match URLs like /api/post/7298 and /api/comment/528491)
            $security->accessControl()
                ->path('^/api/(post|comment)/\d+$')
                ->roles(['ROLE_USER']);
        };

You can define as many URL patterns as you need -- each is a regular expression.
**BUT**, only **one** will be matched per request. Symfony starts at the top of
the list and stops when it finds the first match.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            access_control:
                # matches /admin/users/*
                - { path: '^/admin/users', roles: ROLE_SUPER_ADMIN }

                # matches /admin/* except for anything matching the above rule
                - { path: '^/admin', roles: ROLE_ADMIN }

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config>
                <!-- ... -->

                <rule path="^/admin/users" role="ROLE_SUPER_ADMIN"/>
                <rule path="^/admin" role="ROLE_ADMIN"/>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            // ...

            $security->accessControl()
                ->path('^/admin/users')
                ->roles(['ROLE_SUPER_ADMIN']);

            $security->accessControl()
                ->path('^/admin')
                ->roles(['ROLE_ADMIN']);
        };

Prepending the path with ``^`` means that only URLs *beginning* with the
pattern are matched. For example, a path of ``/admin`` (without the ``^``)
would match ``/admin/foo`` and it would also match URLs like ``/foo/admin``.

A path with ``^/admin`` would match ``/admin/foo``, but would not match ``/foo/admin``.

Each ``access_control`` can also match on IP address, hostname and HTTP methods.
It can also be used to redirect a user to the ``https`` version of a URL pattern.
For more details, please see :doc:`/security/access_control`.

.. _security-securing-controller:

Securing Controllers and Other Code
...................................

You can deny access from inside a controller::

    // src/Controller/AdminController.php
    // ...

    public function adminDashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // or add an optional message - seen by developers
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'User tried to access a page without having ROLE_ADMIN');
    }

That's it! If access is not granted, a special
:class:`Symfony\\Component\\Security\\Core\\Exception\\AccessDeniedException`
is thrown and no more code in your controller is called. Then, one of two things
will happen:

1) If the user isn't logged in yet, they will be asked to log in (e.g. redirected
   to the login page); or

2) If the user *is* logged in, but does *not* have the ``ROLE_ADMIN`` role, they'll
   be shown the 403 access denied page (which you can
   :ref:`customize <controller-error-pages-by-status-code>`).

.. _security-securing-controller-annotations:

Thanks to the SensioFrameworkExtraBundle, you can also secure your controller
using annotations or attributes:

.. code-block:: php

    .. code-block:: php-annotations

        // src/Controller/SecurityController.php
        namespace App\Controller;

        use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

        /**
        * Require ROLE_ADMIN for *every* controller method in this class.
        *
        * @IsGranted("ROLE_ADMIN")
        */
        class AdminController extends AbstractController
        {
            /**
            * Require ROLE_ADMIN for only this controller method.
            *
            * @IsGranted("ROLE_ADMIN")
            */
            public function adminDashboard(): Response
            {
                // ...
            }
        }

    .. code-block:: php-attributes

        // src/Controller/SecurityController.php
        namespace App\Controller;

        use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

        /**
        * Require ROLE_ADMIN for *every* controller method in this class.
        */
        #[IsGranted('ROLE_ADMIN')]
        class AdminController extends AbstractController
        {
            /**
            * Require ROLE_ADMIN for only this controller method.
            */
            #[IsGranted('ROLE_ADMIN')]
            public function adminDashboard(): Response
            {
                // ...
            }
        }

For more information, see the `FrameworkExtraBundle documentation`_.

.. _security-template:

Access Control in Twig Templates
................................

If you want to check if the current user has a certain role, you can use
the built-in ``is_granted()`` helper function in any Twig template:

.. code-block:: html+twig

    {% if is_granted('ROLE_ADMIN') %}
        <a href="...">Delete</a>
    {% endif %}

Securing other Services
.......................

See :doc:`/security/securing_services`.

Setting Individual User Permissions
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Most applications require more specific access rules.

For instance, a user should be able to only edit their *own* comments on a blog.

Voters allow you to write *whatever* business logic you need to determine access. Using
these voters is similar to the role-based access checks implemented in the
previous chapters.

Please read :doc:`/security/voters` to learn how to implement
your own voter.

Checking to see if a User is Logged In (IS_AUTHENTICATED_FULLY)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you *only* want to check if a user is logged in (you don't care about roles),
you have two options.

If you've given *every* user ``ROLE_USER``, you can
check for that role.

Alternatively, you can use a special "attribute" in place of a
role::

    // ...

    public function adminDashboard(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // ...
    }

You can use ``IS_AUTHENTICATED_FULLY`` anywhere roles are used: like
``access_control`` or in Twig.

Technically, ``IS_AUTHENTICATED_FULLY`` isn't an actual role. Yet, it acts like a role, and every
logged-in user will have that specific attribute.

There are several special attributes that you can use:

* ``IS_AUTHENTICATED_REMEMBERED``: *All* logged in users have this, even
  if they are logged in because of a "remember me cookie". You can use this to check
  if the user is logged in regardless of whether you're using
  the :doc:`remember me functionality </security/remember_me>` or not,
  .

* ``IS_AUTHENTICATED_FULLY``: This is similar to ``IS_AUTHENTICATED_REMEMBERED``,
  but stronger. Users who are logged in only because of a "remember me cookie"
  will have ``IS_AUTHENTICATED_REMEMBERED`` but will not have ``IS_AUTHENTICATED_FULLY``.

* ``IS_REMEMBERED``: *Only* users authenticated using the
  :doc:`remember me functionality </security/remember_me>` (i.e. a
  remember-me cookie), have this attribute.

* ``IS_IMPERSONATOR``: When the current user is
  :doc:`impersonating another user </security/impersonating_user>` in this
  session, this attribute will match.

* ``IS_AUTHENTICATED``: *All* authenticated users have this attribute, regardless of the method
  of their authentication.

* ``IS_AUTHENTICATED_ANONYMOUSLY``: *All* users (even unauthenticated ones) have
  this. Note that this attribute is deprecated since Symfony 5.4. Use ``PUBLIC_ACCESS`` instead.

* ``IS_ANONYMOUS``: This attribute is deprecated since Symfony 5.4 and should not be used. We are moving away
  from the concept of an anonymous user.

* ``PUBLIC_ACCESS``: All users, including unauthenticated users, will have this attribute.

If you really need to know if a request was made by an unauthenticated user, then check for **not** ``IS_AUTHENTICATED``.

.. _retrieving-the-user-object:

5a) Fetching the User Object
----------------------------

After authentication, the ``User`` object of the current user can be accessed
via the ``getUser()`` shortcut::

    public function index(): Response
    {
        // usually you'll want to make sure the user is authenticated first
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // returns your User object, or null if the user is not authenticated
        // use inline documentation to tell your editor your exact User class
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // Call whatever methods you've added to your User class
        // For example, if you added a getFirstName() method, you can use that.
        return new Response('Well hi there '.$user->getFirstName());
    }

5b) Fetching the User from a Service
------------------------------------

If you need to get the logged in user from a service, use the
:class:`Symfony\\Component\\Security\\Core\\Security` service::

    // src/Service/ExampleService.php
    // ...

    use Symfony\Component\Security\Core\Security;

    class ExampleService
    {
        private $security;

        public function __construct(Security $security)
        {
            // Avoid calling getUser() in the constructor: the authentication
            //  process might not be complete yet during class instantiation.
            // Instead, store the entire Security object.
            $this->security = $security;
        }

        public function someMethod()
        {
            // returns User object or null if not authenticated
            $user = $this->security->getUser();

            // ...
        }
    }

Fetch the User in a Template
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In a Twig Template the user object is available via the ``app.user`` variable
thanks to the :ref:`Twig global app variable <twig-app-variable>`:

.. code-block:: html+twig

    {% if is_granted('IS_AUTHENTICATED_FULLY') %}
        <p>Email: {{ app.user.email }}</p>
    {% endif %}

.. _security-logging-out:

Logging Out
-----------

To enable logging out, activate the  ``logout`` config parameter under your firewall:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    logout:
                        path:   app_logout

                        # where to redirect after logout
                        # target: app_any_route

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config>
                <!-- ... -->

                <firewall name="secured_area">
                    <!-- ... -->
                    <logout path="app_logout"/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            // ...

            $security->firewall('secured_area')
                // ...
                ->logout()
                    ->path('app_logout');
        };

Next, you'll need to create a route for this URL:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/SecurityController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\Routing\Annotation\Route;

        class SecurityController extends AbstractController
        {
            /**
             * @Route("/logout", name="app_logout", methods={"GET"})
             */
            public function logout(): void
            {
                // method can be blank: it will never be executed!
                throw new \Exception('Don\'t forget to activate logout in security.yaml');
            }
        }

    .. code-block:: php-attributes

        // src/Controller/SecurityController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\Routing\Annotation\Route;

        class SecurityController extends AbstractController
        {
            #[Route('/logout', name: 'app_logout', methods: ['GET'])]
            public function logout()
            {
                // method can be blank: it will never be executed!
                throw new \Exception('Don\'t forget to activate logout in security.yaml');
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        app_logout:
            path: /logout
            methods: GET

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="app_logout" path="/logout" methods="GET"/>
        </routes>

    ..  code-block:: php

        // config/routes.php
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes) {
            $routes->add('app_logout', '/logout')
                ->methods(['GET'])
            ;
        };

And that's it! By sending a user to the ``app_logout`` route (i.e. to ``/logout``)
Symfony will remove authentication from the user session and redirect the user to
the home page of your app, if you did not configure a specific target page for the logout
process (see below).

Customizing Logout
~~~~~~~~~~~~~~~~~~

You might need to execute extra logic upon logout (e.g., invalidate
tokens) or you might want to customize what happens after a logout.

During logout, a :class:`Symfony\\Component\\Security\\Http\\Event\\LogoutEvent`
is dispatched.

Register an :doc:`event listener or subscriber </event_dispatcher>`
to execute custom logic. The following information is available in the
event class:

``getToken()``
    Returns the security token of the session that is about to be logged
    out.
``getRequest()``
    Returns the current request.
``getResponse()``
    Returns a response, if it is already set by a custom listener. Use
    ``setResponse()`` to configure a custom logout response.

To redirect a user to a specific location after logout, you can either set the location
in ``security.yaml`` using the ``target`` key as described in :doc:`/reference/configuration/security`,
or you can use ``setResponse()`` to set a redirect response in the ``LogoutEvent`` subscriber,
as described above. This latter option is ideal if the post-logout action can vary based upon
certain conditions or circumstances.


.. tip::

    Every Security firewall has its own event dispatcher
    (``security.event_dispatcher.FIREWALLNAME``). The logout event is
    dispatched on both the global and firewall dispatcher. You can register
    on the firewall dispatcher if you want your listener to only be
    executed for a specific firewall. For instance, if you have an ``api``
    and ``main`` firewall, use this configuration to register only on the
    logout event in the ``main`` firewall:

    .. configuration-block::

        .. code-block:: yaml

            # config/services.yaml
            services:
                # ...

                App\EventListener\CustomLogoutSubscriber:
                    tags:
                        - name: kernel.event_subscriber
                          dispatcher: security.event_dispatcher.main

        .. code-block:: xml

            <!-- config/services.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd">

                <services>
                    <!-- ... -->

                    <service id="App\EventListener\CustomLogoutSubscriber">
                        <tag name="kernel.event_subscriber"
                             dispacher="security.event_dispatcher.main"
                         />
                    </service>
                </services>
            </container>

        .. code-block:: php

            // config/services.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            use App\EventListener\CustomLogoutListener;
            use App\EventListener\CustomLogoutSubscriber;
            use Symfony\Component\Security\Http\Event\LogoutEvent;

            return function(ContainerConfigurator $configurator) {
                $services = $configurator->services();

                $services->set(CustomLogoutSubscriber::class)
                    ->tag('kernel.event_subscriber', [
                        'dispatcher' => 'security.event_dispatcher.main',
                    ]);
            };

.. _security-role-hierarchy:

Hierarchical Roles
------------------

Instead of giving many roles to each user, you can define role inheritance
rules by creating a role hierarchy:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            role_hierarchy:
                ROLE_ADMIN:       ROLE_APPROVER
                ROLE_SUPER_ADMIN: [ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH]

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config>
                <!-- ... -->

                <role id="ROLE_ADMIN">ROLE_APPROVER</role>
                <role id="ROLE_SUPER_ADMIN">ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH</role>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            // ...

            $security->roleHierarchy('ROLE_ADMIN', ['ROLE_APPROVER']);
            $security->roleHierarchy('ROLE_SUPER_ADMIN', ['ROLE_ADMIN', 'ROLE_ALLOWED_TO_SWITCH']);
        };

The above configuration has the following meaning:

Users with the ``ROLE_ADMIN`` role also have the ``ROLE_APPROVER`` role.

Users with ``ROLE_SUPER_ADMIN`` also have the ``ROLE_ADMIN`` and ``ROLE_ALLOWED_TO_SWITCH`` roles.
In addition, they also have ``ROLE_APPROVER``, which is inherited from ``ROLE_ADMIN``.

.. tip::

    If you automatically assign a lowest-level role, such as ``ROLE_USER`` (or any other role), to all
    users in the ``setRoles()`` method of your ``User`` class, then you do not need to include that ``ROLE_USER``
    in the role hierarchy.


For role hierarchy to work, do not try to call ``$user->getRoles()`` manually.
For example, in a controller extending from the :ref:`base controller <the-base-controller-class-services>`::

    // NO! - $user->getRoles() will not know about the role hierarchy
    $hasAccess = in_array('ROLE_ADMIN', $user->getRoles());

    // YES! - use of the normal security methods
    $hasAccess = $this->isGranted('ROLE_ADMIN');
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

.. note::

    The ``role_hierarchy`` values are static - you can't, for example, store the
    role hierarchy in a database. If you need that, create a custom
    :doc:`security voter </security/voters>` that looks for the user roles
    in the database.

Frequently Asked Questions
--------------------------

**Can I have Multiple Firewalls?**
    Yes! But it's usually not necessary. Each firewall is like a separate security
    system. And so, unless you have *very* different authentication needs, one
    firewall usually works well. With the :doc:`built-in authentication providers </security/auth_providers>`
    and your own :doc:`custom authentication providers </security/custom_authentication_provider>`,
    you can create diverse ways of allowing authentication (e.g. form login,
    API key authentication and LDAP) all under the same firewall.

**Can I Share Authentication Between Firewalls?**
    Yes, but only with some configuration. If you're using multiple firewalls and
    you authenticate against one firewall, you will *not* be automatically authenticated against
    any other firewalls. Different firewalls are like different security
    systems. To do this you have to explicitly specify the same
    :ref:`reference-security-firewall-context` for different firewalls. But usually
    for most applications, having one main firewall is enough.

**Security doesn't seem to work on my Error Pages**
    As routing is done *before* security, 404 error pages are not covered by
    any firewall. This means you can't check for security or even access the
    user object on these pages. See :doc:`/controller/error_pages`
    for more details.

**My Authentication Doesn't Seem to Work: No Errors, but I'm Never Logged In**
    Sometimes authentication may be successful, but after redirecting, you're
    logged out immediately due to a problem loading the ``User`` from the session.
    To see if this is an issue, check your log file (``var/log/dev.log``) for
    the error message.

**Cannot refresh token because user has changed**
    If you see this, there are two possible causes. Firstly, there might be a problem
    loading your ``User`` from the session. See :ref:`user_session_refresh`. Secondly,
    if certain user information was changed in the database since the last page
    refresh, Symfony will purposely log out the user for security reasons.

Learn More
----------

Authentication (Identifying/Logging in the User)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. toctree::
    :maxdepth: 1

    security/authenticator_manager
    security/form_login_setup
    security/reset_password
    security/json_login_setup
    security/guard_authentication
    security/password_migration
    security/auth_providers
    security/user_provider
    security/ldap
    security/remember_me
    security/impersonating_user
    security/user_checkers
    security/named_hashers
    security/multiple_guard_authenticators
    security/firewall_restriction
    security/csrf
    security/custom_authentication_provider

Authorization (Denying Access)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. toctree::
    :maxdepth: 1

    security/voters
    security/securing_services
    security/access_control
    security/access_denied_handler
    security/acl
    security/force_https

.. _`FrameworkExtraBundle documentation`: https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/index.html
.. _`HWIOAuthBundle`: https://github.com/hwi/HWIOAuthBundle
.. _`OWASP Brute Force Attacks`: https://owasp.org/www-community/controls/Blocking_Brute_Force_Attacks
.. _`brute force login attacks`: https://owasp.org/www-community/controls/Blocking_Brute_Force_Attacks
.. _`Symfony Security screencast series`: https://symfonycasts.com/screencast/symfony-security
.. _`MakerBundle`: https://symfony.com/doc/current/bundles/SymfonyMakerBundle/index.html
