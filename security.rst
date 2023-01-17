.. index::
   single: Security

Security
========

Symfony provides many tools to secure your application. Some HTTP-related
security tools, like :doc:`secure session cookies </session>` and
:doc:`CSRF protection </security/csrf>` are provided by default. The
SecurityBundle, which you will learn about in this guide, provides all
authentication and authorization features needed to secure your
application.

.. _security-installation:

To get started, install the SecurityBundle:

.. code-block:: terminal

    $ composer require symfony/security-bundle

If you have :ref:`Symfony Flex <symfony-flex>` installed, this also
creates a ``security.yaml`` configuration file for you:

.. code-block:: yaml

    # config/packages/security.yaml
    security:
        enable_authenticator_manager: true
        # https://symfony.com/doc/current/security.html#registering-the-user-hashing-passwords
        password_hashers:
            Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface: 'auto'
        # https://symfony.com/doc/current/security.html#where-do-users-come-from-user-providers
        providers:
            users_in_memory: { memory: null }
        firewalls:
            dev:
                pattern: ^/(_(profiler|wdt)|css|images|js)/
                security: false
            main:
                lazy: true
                provider: users_in_memory

                # activate different ways to authenticate
                # https://symfony.com/doc/current/security.html#firewalls-authentication

                # https://symfony.com/doc/current/security/impersonating_user.html
                # switch_user: true

        # Easy way to control access for large sections of your site
        # Note: Only the *first* access control that matches will be used
        access_control:
            # - { path: ^/admin, roles: ROLE_ADMIN }
            # - { path: ^/profile, roles: ROLE_USER }

That's a lot of config! In the next sections, the three main elements are
discussed:

`The User`_ (``providers``)
    Any secured section of your application needs some concept of
    a user. The user provider loads users from any storage (e.g. the
    database) based on a "user identifier" (e.g. the user's email address);

`The Firewall`_ & `Authenticating Users`_ (``firewalls``)
    The firewall is the core of securing your application. Every request
    within the firewall is checked if it needs an authenticated user. The
    firewall also takes care of authenticating this user (e.g. using a
    login form);

`Access Control (Authorization)`_ (``access_control``)
    Using access control and the authorization checker, you control the
    required permissions to perform a specific action or visit a specific
    URL.

.. caution::

    Symfony Security has received major changes in 5.3. This article
    explains the *new authenticator-based* system (identified by the
    ``enable_authenticator_manager: true`` config option).

    Refer to the `5.2 version of this documentation`_ if you are still using
    the legacy security system.

.. _create-user-class:
.. _a-create-your-user-class:

The User
--------

Permissions in Symfony are always linked to a user object. If you need to
secure (parts of) your application, you need to create a user class. This
is a class that implements :class:`Symfony\\Component\\Security\\Core\\User\\UserInterface`.
This is often a Doctrine entity, but you can also use a dedicated
Security user class.

The easiest way to generate a user class is using the ``make:user`` command
from the `MakerBundle`_:

.. code-block:: terminal

    $ php bin/console make:user
     The name of the security user class (e.g. User) [User]:
     > User

     Do you want to store user data in the database (via Doctrine)? (yes/no) [yes]:
     > yes

     Enter a property name that will be the unique "display" name for the user (e.g. email, username, uuid) [email]:
     > email

     Will this app need to hash/check user passwords? Choose No if passwords are not needed or will be checked/hashed by some other system (e.g. a single sign-on server).

     Does this app need to hash/check user passwords? (yes/no) [yes]:
     > yes

     created: src/Entity/User.php
     created: src/Repository/UserRepository.php
     updated: src/Entity/User.php
     updated: config/packages/security.yaml

.. code-block:: php

    // src/Entity/User.php
    namespace App\Entity;

    use App\Repository\UserRepository;
    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
    use Symfony\Component\Security\Core\User\UserInterface;

    /**
     * @ORM\Entity(repositoryClass=UserRepository::class)
     */
    class User implements UserInterface, PasswordAuthenticatedUserInterface
    {
        /**
         * @ORM\Id
         * @ORM\GeneratedValue
         * @ORM\Column(type="integer")
         */
        private $id;

        /**
         * @ORM\Column(type="string", length=180, unique=true)
         */
        private $email;

        /**
         * @ORM\Column(type="json")
         */
        private $roles = [];

        /**
         * @var string The hashed password
         * @ORM\Column(type="string")
         */
        private $password;

        public function getId(): ?int
        {
            return $this->id;
        }

        public function getEmail(): ?string
        {
            return $this->email;
        }

        public function setEmail(string $email): self
        {
            $this->email = $email;

            return $this;
        }

        /**
         * The public representation of the user (e.g. a username, an email address, etc.)
         *
         * @see UserInterface
         */
        public function getUserIdentifier(): string
        {
            return (string) $this->email;
        }

        /**
         * @deprecated since Symfony 5.3
         */
        public function getUsername(): string
        {
            return (string) $this->email;
        }

        /**
         * @see UserInterface
         */
        public function getRoles(): array
        {
            $roles = $this->roles;
            // guarantee every user at least has ROLE_USER
            $roles[] = 'ROLE_USER';

            return array_unique($roles);
        }

        public function setRoles(array $roles): self
        {
            $this->roles = $roles;

            return $this;
        }

        /**
         * @see PasswordAuthenticatedUserInterface
         */
        public function getPassword(): string
        {
            return $this->password;
        }

        public function setPassword(string $password): self
        {
            $this->password = $password;

            return $this;
        }

        /**
         * Returning a salt is only needed if you are not using a modern
         * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
         *
         * @see UserInterface
         */
        public function getSalt(): ?string
        {
            return null;
        }

        /**
         * @see UserInterface
         */
        public function eraseCredentials()
        {
            // If you store any temporary, sensitive data on the user, clear it here
            // $this->plainPassword = null;
        }
    }

.. versionadded:: 5.3

    The :class:`Symfony\\Component\\Security\\Core\\User\\PasswordAuthenticatedUserInterface`
    interface and ``getUserIdentifier()`` method were introduced in Symfony 5.3.

If your user is a Doctrine entity, like in the example above, don't forget
to create the tables by :ref:`creating and running a migration <doctrine-creating-the-database-tables-schema>`:

.. code-block:: terminal

    $ php bin/console make:migration
    $ php bin/console doctrine:migrations:migrate

.. _where-do-users-come-from-user-providers:
.. _security-user-providers:

Loading the User: The User Provider
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Besides creating the entity, the ``make:user`` command also adds config
for a user provider in your security configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            providers:
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
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config>
                <!-- ... -->

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

            $security->provider('app_user_provider')
                ->entity()
                    ->class(User::class)
                    ->property('email')
            ;
        };

This user provider knows how to (re)load users from a storage (e.g. a database)
based on a "user identifier" (e.g. the user's email address or username).
The configuration above uses Doctrine to load the ``User`` entity using the
``email`` property as "user identifier".

User providers are used in a couple places during the security lifecycle:

**Load the User based on an identifier**
    During login (or any other authenticator), the provider loads the user
    based on the user identifier. Some other features, like
    :doc:`user impersonation </security/impersonating_user>` and
    :doc:`Remember Me </security/remember_me>` also use this.

**Reload the User from the session**
    At the beginning of each request, the user is loaded from the
    session (unless your firewall is ``stateless``). The provider
    "refreshes" the user (e.g. the database is queried again for fresh
    data) to make sure all user information is up to date (and if
    necessary, the user is de-authenticated/logged out if something
    changed). See :ref:`user_session_refresh` for more information about
    this process.

Symfony comes with several built-in user providers:

:ref:`Entity User Provider <security-entity-user-provider>`
    Loads users from a database using :doc:`Doctrine </doctrine>`;
:ref:`LDAP User Provider <security-ldap-user-provider>`
    Loads users from a LDAP server;
:ref:`Memory User Provider <security-memory-user-provider>`
    Loads users from a configuration file;
:ref:`Chain User Provider <security-chain-user-provider>`
    Merges two or more user providers into a new user provider.
    Since each firewall has exactly *one* user provider, you can use this
    to chain multiple providers together.

The built-in user providers cover the most common needs for applications, but you
can also create your own :ref:`custom user provider <security-custom-user-provider>`.

.. note::

    Sometimes, you need to inject the user provider in another class (e.g.
    in your custom authenticator). All user providers follow this pattern
    for their service ID: ``security.user.provider.concrete.<your-provider-name>``
    (where ``<your-provider-name>`` is the configuration key, e.g.
    ``app_user_provider``). If you only have one user provider, you can autowire
    it using the :class:`Symfony\\Component\\Security\\Core\\User\\UserProviderInterface`
    type-hint.

.. _security-encoding-user-password:

Registering the User: Hashing Passwords
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Many applications require a user to log in with a password. For these
applications, the SecurityBundle provides password hashing and verification
functionality.

First, make sure your User class implements the
:class:`Symfony\\Component\\Security\\Core\\User\\PasswordAuthenticatedUserInterface`::

    // src/Entity/User.php

    // ...
    use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

    class User implements UserInterface, PasswordAuthenticatedUserInterface
    {
        // ...

        /**
         * @return string the hashed password for this user
         */
        public function getPassword(): string
        {
            return $this->password;
        }
    }

Then, configure which password hasher should be used for this class. If your
``security.yaml`` file was not already pre-configured, then ``make:user`` should
have done this for you:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...
            password_hashers:
                # Use native password hasher, which auto-selects and migrates the best
                # possible hashing algorithm (starting from Symfony 5.3 this is "bcrypt")
                Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface: 'auto'

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
                <!-- Use native password hasher, which auto-selects and migrates the best
                     possible hashing algorithm (starting from Symfony 5.3 this is "bcrypt") -->
                <password-hasher class="Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface" algorithm="auto"/>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use App\Entity\User;
        use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

        return static function (SecurityConfig $security) {
            // ...

            // Use native password hasher, which auto-selects and migrates the best
            // possible hashing algorithm (starting from Symfony 5.3 this is "bcrypt")
            $security->passwordHasher(PasswordAuthenticatedUserInterface::class)
                ->algorithm('auto')
            ;
        };

.. versionadded:: 5.3

    The ``password_hashers`` option was introduced in Symfony 5.3. In previous
    versions it was called ``encoders``.

Now that Symfony knows *how* you want to hash the passwords, you can use the
``UserPasswordHasherInterface`` service to do this before saving your users to
the database::

    // src/Controller/RegistrationController.php
    namespace App\Controller;

    // ...
    use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

    class RegistrationController extends AbstractController
    {
        public function index(UserPasswordHasherInterface $passwordHasher)
        {
            // ... e.g. get the user data from a registration form
            $user = new User(...);
            $plaintextPassword = ...;

            // hash the password (based on the security.yaml config for the $user class)
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $plaintextPassword
            );
            $user->setPassword($hashedPassword);

            // ...
        }
    }

.. tip::

    The ``make:registration-form`` maker command can help you set-up the
    registration controller and add features like email address
    verification using the `SymfonyCastsVerifyEmailBundle`_.

    .. code-block:: terminal

        $ composer require symfonycasts/verify-email-bundle
        $ php bin/console make:registration-form

You can also manually hash a password by running:

.. code-block:: terminal

    $ php bin/console security:hash-password

Read more about all available hashers and password migration in
:doc:`security/passwords`.

.. _firewalls-authentication:
.. _a-authentication-firewalls:

The Firewall
------------

The ``firewalls`` section of ``config/packages/security.yaml`` is the *most*
important section. A "firewall" is your authentication system: the firewall
defines which parts of your application are secured and *how* your users
will be able to authenticate (e.g. login form, API token, etc).

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...
            firewalls:
                dev:
                    pattern: ^/(_(profiler|wdt)|css|images|js)/
                    security: false
                main:
                    lazy: true
                    provider: users_in_memory

                    # activate different ways to authenticate
                    # https://symfony.com/doc/current/security.html#firewalls-authentication

                    # https://symfony.com/doc/current/security/impersonating_user.html
                    # switch_user: true

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
                <firewall name="dev"
                    pattern="^/(_(profiler|wdt)|css|images|js)/"
                    security="false"/>

                <firewall name="main"
                    lazy="true"/>

                <!-- activate different ways to authenticate
                     https://symfony.com/doc/current/security.html#firewalls-authentication -->

                <!-- https://symfony.com/doc/current/security/impersonating_user.html -->
                <!-- <switch-user/> -->
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            // ...
            $security->firewall('dev')
                ->pattern('^/(_(profiler|wdt)|css|images|js)/')
                ->security(false)
            ;

            $security->firewall('main')
                ->lazy(true)

                // activate different ways to authenticate
                // https://symfony.com/doc/current/security.html#firewalls-authentication

                // https://symfony.com/doc/current/security/impersonating_user.html
                // ->switchUser(true)
            ;
        };

Only one firewall is active on each request: Symfony uses the ``pattern`` key
to find the first match (you can also
:doc:`match by host or other things </security/firewall_restriction>`).

The ``dev`` firewall is really a fake firewall: it makes sure that you
don't accidentally block Symfony's dev tools - which live under URLs like
``/_profiler`` and ``/_wdt``.

All *real* URLs are handled by the ``main`` firewall (no ``pattern`` key means
it matches *all* URLs). A firewall can have many modes of authentication,
in other words, it enables many ways to ask the question "Who are you?".

Often, the user is unknown (i.e. not logged in) when they first visit your
website. If you visit your homepage right now, you *will* have access and
you will see that you are visiting a page behind the firewall in the toolbar:

.. image:: /_images/security/anonymous_wdt.png
   :align: center

Visiting a URL under a firewall doesn't necessarily require you to be authenticated
(e.g. the login form has to be accessible or some parts of your application
are public). You will learn how to restrict access to URLs, controllers or
anything else within your firewall in the :ref:`access control
<security-access-control>` section.

.. tip::

    The ``lazy`` anonymous mode prevents the session from being started if
    there is no need for authorization (i.e. explicit check for a user
    privilege). This is important to keep requests cacheable (see
    :doc:`/http_cache`).

.. note::

    If you do not see the toolbar, install the :doc:`profiler </profiler>`
    with:

    .. code-block:: terminal

        $ composer require --dev symfony/profiler-pack

Now that we understand our firewall, the next step is to create a way for your
users to authenticate!

.. _security-authenticators:

Authenticating Users
--------------------

During authentication, the system tries to find a matching user for the
visitor of the webpage. Traditionally, this was done using a login form or
a HTTP basic dialog in the browser. However, the SecurityBundle comes with
many other authenticators:

* `Form Login`_
* `JSON Login`_
* `HTTP Basic`_
* `Login Link`_
* `X.509 Client Certificates`_
* `Remote users`_
* :doc:`Custom Authenticators </security/custom_authenticator>`

.. tip::

    If your application logs users in via a third-party service such as
    Google, Facebook or Twitter (social login), check out the `HWIOAuthBundle`_
    community bundle.

.. _security-form-login:

Form Login
~~~~~~~~~~

Most websites have a login form where users authenticate using an
identifier (e.g. email address or username) and a password. This
functionality is provided by the *form login authenticator*.

First, create a controller for the login form:

.. code-block:: terminal

    $ php bin/console make:controller Login

     created: src/Controller/LoginController.php
     created: templates/login/index.html.twig

.. code-block:: php

    // src/Controller/LoginController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;

    class LoginController extends AbstractController
    {
        #[Route('/login', name: 'app_login')]
        public function index(): Response
        {
            return $this->render('login/index.html.twig', [
                'controller_name' => 'LoginController',
            ]);
        }
    }

Then, enable the form login authenticator using the ``form_login`` setting:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    form_login:
                        # "app_login" is the name of the route created previously
                        login_path: app_login
                        check_path: app_login

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
                    <!-- "app_login" is the name of the route created previously -->
                    <form-login login-path="app_login" check-path="app_login"/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            // ...

            $mainFirewall = $security->firewall('main');

            // "app_login" is the name of the route created previously
            $mainFirewall->formLogin()
                ->loginPath('app_login')
                ->checkPath('app_login')
            ;
        };

.. note::

    The ``login_path`` and ``check_path`` support URLs and route names (but
    cannot have mandatory wildcards - e.g. ``/login/{foo}`` where ``foo``
    has no default value).

Once enabled, the security system redirects unauthenticated visitors to the
``login_path`` when they try to access a secured place (this behavior can
be customized using :ref:`authentication entry points <security-entry-point>`).

Edit the login controller to render the login form:

.. code-block:: diff

      // ...
    + use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

      class LoginController extends AbstractController
      {
          #[Route('/login', name: 'app_login')]
    -     public function index(): Response
    +     public function index(AuthenticationUtils $authenticationUtils): Response
          {
    +         // get the login error if there is one
    +         $error = $authenticationUtils->getLastAuthenticationError();
    +
    +         // last username entered by the user
    +         $lastUsername = $authenticationUtils->getLastUsername();
    +
              return $this->render('login/index.html.twig', [
    -             'controller_name' => 'LoginController',
    +             'last_username' => $lastUsername,
    +             'error'         => $error,
              ]);
          }
      }

Don't let this controller confuse you. Its job is only to *render* the form:
the ``form_login`` authenticator will handle the form *submission* automatically.
If the user submits an invalid email or password, that authenticator will store
the error and redirect back to this controller, where we read the error (using
``AuthenticationUtils``) so that it can be displayed back to the user.

Finally, create or update the template:

.. code-block:: html+twig

    {# templates/login/index.html.twig #}
    {% extends 'base.html.twig' %}

    {# ... #}

    {% block body %}
        {% if error %}
            <div>{{ error.messageKey|trans(error.messageData, 'security') }}</div>
        {% endif %}

        <form action="{{ path('app_login') }}" method="post">
            <label for="username">Email:</label>
            <input type="text" id="username" name="_username" value="{{ last_username }}"/>

            <label for="password">Password:</label>
            <input type="password" id="password" name="_password"/>

            {# If you want to control the URL the user is redirected to on success
            <input type="hidden" name="_target_path" value="/account"/> #}

            <button type="submit">login</button>
        </form>
    {% endblock %}

.. caution::

    The ``error`` variable passed into the template is an instance of
    :class:`Symfony\\Component\\Security\\Core\\Exception\\AuthenticationException`.
    It may contain sensitive information about the authentication failure.
    *Never* use ``error.message``: use the ``messageKey`` property instead,
    as shown in the example. This message is always safe to display.

The form can look like anything, but it usually follows some conventions:

* The ``<form>`` element sends a ``POST`` request to the ``app_login`` route, since
  that's what you configured as the ``check_path`` under the ``form_login`` key in
  ``security.yaml``;
* The username (or whatever your user's "identifier" is, like an email) field has
  the name ``_username`` and the password field has the name ``_password``.

.. tip::

    Actually, all of this can be configured under the ``form_login`` key. See
    :ref:`reference-security-firewall-form-login` for more details.

.. caution::

    This login form is currently not protected against CSRF attacks. Read
    :ref:`form_login-csrf` on how to protect your login form.

And that's it! When you submit the form, the security system automatically
reads the ``_username`` and ``_password`` POST parameter, loads the user via
the user provider, checks the user's credentials and either authenticates the
user or sends them back to the login form where the error can be displayed.

To review the whole process:

#. The user tries to access a resource that is protected (e.g. ``/admin``);
#. The firewall initiates the authentication process by redirecting the
   user to the login form (``/login``);
#. The ``/login`` page renders login form via the route and controller created
   in this example;
#. The user submits the login form to ``/login``;
#. The security system (i.e. the ``form_login`` authenticator) intercepts the
   request, checks the user's submitted credentials, authenticates the user if
   they are correct, and sends the user back to the login form if they are not.

.. seealso::

    You can customize the responses on a successful or failed login
    attempt. See :doc:`/security/form_login`.

.. _form_login-csrf:

CSRF Protection in Login Forms
..............................

`Login CSRF attacks`_ can be prevented using the same technique of adding hidden
CSRF tokens into the login forms. The Security component already provides CSRF
protection, but you need to configure some options before using it.

First, you need to enable CSRF on the form login:

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
                        enable_csrf: true

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
                    <form-login enable-csrf="true"/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            // ...

            $mainFirewall = $security->firewall('main');
            $mainFirewall->formLogin()
                // ...
                ->enableCsrf(true)
            ;
        };

.. _csrf-login-template:

Then, use the ``csrf_token()`` function in the Twig template to generate a CSRF
token and store it as a hidden field of the form. By default, the HTML field
must be called ``_csrf_token`` and the string used to generate the value must
be ``authenticate``:

.. code-block:: html+twig

    {# templates/login/index.html.twig #}

    {# ... #}
    <form action="{{ path('app_login') }}" method="post">
        {# ... the login fields #}

        <input type="hidden" name="_csrf_token" value="{{ csrf_token('authenticate') }}">

        <button type="submit">login</button>
    </form>

After this, you have protected your login form against CSRF attacks.

.. tip::

    You can change the name of the field by setting ``csrf_parameter`` and change
    the token ID by setting  ``csrf_token_id`` in your configuration. See
    :ref:`reference-security-firewall-form-login` for more details.

.. _security-json-login:

JSON Login
~~~~~~~~~~

Some applications provide an API that is secured using tokens. These
applications may use an endpoint that provides these tokens based on a
username (or email) and password. The JSON login authenticator helps you create
this functionality.

Enable the authenticator using the ``json_login`` setting:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    json_login:
                        # api_login is a route we will create below
                        check_path: api_login

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
                    <json-login check-path="api_login"/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            // ...

            $mainFirewall = $security->firewall('main');
            $mainFirewall->jsonLogin()
                ->checkPath('api_login')
            ;
        };

.. note::

    The ``check_path`` supports URLs and route names (but cannot have
    mandatory wildcards - e.g. ``/login/{foo}`` where ``foo`` has no
    default value).

The authenticator runs when a client request the ``check_path``. First,
create a controller for this path:

.. code-block:: terminal

    $ php bin/console make:controller --no-template ApiLogin

     created: src/Controller/ApiLoginController.php

.. code-block:: php

    // src/Controller/ApiLoginController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;

    class ApiLoginController extends AbstractController
    {
        #[Route('/api/login', name: 'api_login')]
        public function index(): Response
        {
            return $this->json([
                'message' => 'Welcome to your new controller!',
                'path' => 'src/Controller/ApiLoginController.php',
            ]);
        }
    }

This login controller will be called after the authenticator successfully
authenticates the user. You can get the authenticated user, generate a
token (or whatever you need to return) and return the JSON response:

.. code-block:: diff

      // ...
    + use App\Entity\User;
    + use Symfony\Component\Security\Http\Attribute\CurrentUser;

      class ApiLoginController extends AbstractController
      {
          #[Route('/api/login', name: 'api_login')]
    -     public function index(): Response
    +     public function index(#[CurrentUser] ?User $user): Response
          {
    +         if (null === $user) {
    +             return $this->json([
    +                 'message' => 'missing credentials',
    +             ], Response::HTTP_UNAUTHORIZED);
    +         }
    +
    +         $token = ...; // somehow create an API token for $user
    +
              return $this->json([
    -             'message' => 'Welcome to your new controller!',
    -             'path' => 'src/Controller/ApiLoginController.php',
    +             'user'  => $user->getUserIdentifier(),
    +             'token' => $token,
              ]);
          }
      }

.. note::

    The ``#[CurrentUser]`` can only be used in controller arguments to
    retrieve the authenticated user. In services, you would use
    :method:`Symfony\\Component\\Security\\Core\\Security::getUser`.

That's it! To summarize the process:

#. A client (e.g. the front-end) makes a *POST request* with the
   ``Content-Type: application/json`` header to ``/api/login`` with
   ``username`` (even if your identifier is actually an email) and
   ``password`` keys:

   .. code-block:: json

        {
            "username": "dunglas@example.com",
            "password": "MyPassword"
        }
#. The security system intercepts the request, checks the user's submitted
   credentials and authenticates the user. If the credentials is incorrect,
   an HTTP 401 Unauthorized JSON response is returned, otherwise your
   controller is run;
#. Your controller creates the correct response:

   .. code-block:: json

        {
            "user": "dunglas@example.com",
            "token": "45be42..."
        }

.. tip::

    The JSON request format can be configured under the ``json_login`` key.
    See :ref:`reference-security-firewall-json-login` for more details.

.. _security-http_basic:

HTTP Basic
~~~~~~~~~~

`HTTP Basic authentication`_ is a standardized HTTP authentication
framework. It asks credentials (username and password) using a dialog in
the browser and the HTTP basic authenticator of Symfony will verify these
credentials.

Add the ``http_basic`` key to your firewall to enable HTTP Basic
authentication:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    http_basic:
                        realm: Secured Area

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
                    <http-basic realm="Secured Area"/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            $mainFirewall = $security->firewall('main');
            $mainFirewall->httpBasic()
                ->realm('Secured Area')
            ;
        };

That's it! Whenever an unauthenticated user tries to visit a protected
page, Symfony will inform the browser that it needs to start HTTP basic
authentication (using the ``WWW-Authenticate`` response header). Then, the
authenticator verifies the credentials and authenticates the user.

.. note::

    You cannot use :ref:`log out <security-logging-out>` with the HTTP
    basic authenticator. Even if you log out from Symfony, your browser
    "remembers" your credentials and will send them on every request.

Login Link
~~~~~~~~~~

Login links are a passwordless authentication mechanism. The user will
receive a short-lived link (e.g. via email) which will authenticate them to the
website.

You can learn all about this authenticator in :doc:`/security/login_link`.

X.509 Client Certificates
~~~~~~~~~~~~~~~~~~~~~~~~~

When using client certificates, your web server does all the authentication
itself. The X.509 authenticator provided by Symfony extracts the email from
the "distinguished name" (DN) of the client certificate. Then, it uses this
email as user identifier in the user provider.

First, configure your web server to enable client certificate verification
and to expose the certificate's DN to the Symfony application:

.. configuration-block::

    .. code-block:: nginx

        server {
            # ...

            ssl_client_certificate /path/to/my-custom-CA.pem;

            # enable client certificate verification
            ssl_verify_client optional;
            ssl_verify_depth 1;

            location / {
                # pass the DN as "SSL_CLIENT_S_DN" to the application
                fastcgi_param SSL_CLIENT_S_DN $ssl_client_s_dn;

                # ...
            }
        }

    .. code-block:: apache

        # ...
        SSLCACertificateFile "/path/to/my-custom-CA.pem"
        SSLVerifyClient optional
        SSLVerifyDepth 1

        # pass the DN to the application
        SSLOptions +StdEnvVars

Then, enable the X.509 authenticator using ``x509`` on your firewall:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    x509:
                        provider: your_user_provider

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
                    <x509 provider="your_user_provider"/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            $mainFirewall = $security->firewall('main');
            $mainFirewall->x509()
                ->provider('your_user_provider')
            ;
        };

By default, Symfony extracts the email address from the DN in two different
ways:

#. First, it tries the ``SSL_CLIENT_S_DN_Email`` server parameter, which is
   exposed by Apache;
#. If it is not set (e.g. when using Nginx), it uses ``SSL_CLIENT_S_DN`` and
   matches the value following ``emailAddress=``.

You can customize the name of both parameters under the ``x509`` key. See
:ref:`the configuration reference <reference-security-firewall-x509>` for
more details.

Remote Users
~~~~~~~~~~~~

Besides client certificate authentication, there are more web server
modules that pre-authenticate a user (e.g. kerberos). The remote user
authenticator provides a basic integration for these services.

These modules often expose the authenticated user in the ``REMOTE_USER``
environment variable. The remote user authenticator uses this value as the
user identifier to load the corresponding user.

Enable remote user authentication using the ``remote_user`` key:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    # ...
                    remote_user:
                        provider: your_user_provider

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
                <firewall name="main">
                    <remote-user provider="your_user_provider"/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            $mainFirewall = $security->firewall('main');
            $mainFirewall->remoteUser()
                ->provider('your_user_provider')
            ;
        };

.. tip::

    You can customize the name of this server variable under the
    ``remote_user`` key. See
    :ref:`the configuration reference <reference-security-firewall-remote-user>`
    for more details.

.. _security-login-throttling:

Limiting Login Attempts
~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 5.2

    Login throttling was introduced in Symfony 5.2.

Symfony provides basic protection against `brute force login attacks`_.
You must enable this using the ``login_throttling`` setting:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # you must use the authenticator manager
            enable_authenticator_manager: true

            firewalls:
                # ...

                main:
                    # ...

                    # by default, the feature allows 5 login attempts per minute
                    login_throttling: null

                    # configure the maximum login attempts
                    login_throttling:
                        max_attempts: 3          # per minute ...
                        # interval: '15 minutes' # ... or in a custom period

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

            <!-- you must use the authenticator manager -->
            <config enable-authenticator-manager="true">
                <!-- ... -->

                <firewall name="main">
                    <!-- by default, the feature allows 5 login attempts per minute
                         max-attempts: (optional) You can configure the maximum attempts ...
                         interval:     (optional) ... and the period of time. -->
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

            $mainFirewall = $security->firewall('main');

            // by default, the feature allows 5 login attempts per minute
            $mainFirewall->loginThrottling()
                // ->maxAttempts(3)         // Optional: You can configure the maximum attempts ...
                // ->interval('15 minutes') // ... and the period of time.
            ;
        };

.. note::

    The value of the ``interval`` option must be a number followed by any of the
    units accepted by the `PHP date relative formats`_ (e.g. ``3 seconds``,
    ``10 hours``, ``1 day``, etc.)

.. versionadded:: 5.3

    The ``login_throttling.interval`` option was introduced in Symfony 5.3.

Internally, Symfony uses the :doc:`Rate Limiter component </rate_limiter>`
which by default uses Symfony's cache to store the previous login attempts.
However, you can implement a :ref:`custom storage <rate-limiter-storage>`.

Login attempts are limited on ``max_attempts`` (default: 5)
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

        return static function (ContainerBuilder $containerBuilder, FrameworkConfig $framework, SecurityConfig $security) {
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

            $containerBuilder->register('app.login_rate_limiter', DefaultLoginRateLimiter::class)
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
                        path: app_logout

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

                <firewall name="main">
                    <!-- ... -->
                    <logout path="app_logout"/>

                    <!-- use "target" to configure where to redirect after logout
                    <logout path="app_logout" target="app_any_route"/>
                    -->
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            // ...

            $mainFirewall = $security->firewall('main');
            // ...
            $mainFirewall->logout()
                // the argument can be either a route name or a path
                ->path('app_logout')

                // where to redirect after logout
                // ->target('app_any_route')
            ;
        };

Next, you need to create a route for this URL (but not a controller):

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
                // controller can be blank: it will never be called!
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
                // controller can be blank: it will never be called!
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

    .. code-block:: php

        // config/routes.php
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes) {
            $routes->add('app_logout', '/logout')
                ->methods(['GET'])
            ;
        };

That's it! By sending a user to the ``app_logout`` route (i.e. to ``/logout``)
Symfony will un-authenticate the current user and redirect them.

Customizing Logout
~~~~~~~~~~~~~~~~~~

.. versionadded:: 5.1

    The ``LogoutEvent`` was introduced in Symfony 5.1. Prior to this
    version, you had to use a
    :ref:`logout success handler <reference-security-logout-success-handler>`
    to customize the logout.

In some cases you need to run extra logic upon logout (e.g. invalidate
some tokens) or want to customize what happens after a logout. During
logout, a :class:`Symfony\\Component\\Security\\Http\\Event\\LogoutEvent`
is dispatched. Register an :doc:`event listener or subscriber </event_dispatcher>`
to execute custom logic::

    // src/EventListener/LogoutSubscriber.php
    namespace App\EventListener;

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\HttpFoundation\RedirectResponse;
    use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
    use Symfony\Component\Security\Http\Event\LogoutEvent;

    class LogoutSubscriber implements EventSubscriberInterface
    {
        public function __construct(
            private UrlGeneratorInterface $urlGenerator
        ) {
        }

        public static function getSubscribedEvents(): array
        {
            return [LogoutEvent::class => 'onLogout'];
        }

        public function onLogout(LogoutEvent $event): void
        {
            // get the security token of the session that is about to be logged out
            $token = $event->getToken();

            // get the current request
            $request = $event->getRequest();

            // get the current response, if it is already set by another listener
            $response = $event->getResponse();

            // configure a custom logout response to the homepage
            $response = new RedirectResponse(
                $this->urlGenerator->generate('homepage'),
                RedirectResponse::HTTP_SEE_OTHER
            );
            $event->setResponse($response);
        }
    }

.. _retrieving-the-user-object:

Fetching the User Object
------------------------

After authentication, the ``User`` object of the current user can be
accessed via the ``getUser()`` shortcut in the
:ref:`base controller <the-base-controller-class-services>`::

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

    class ProfileController extends AbstractController
    {
        public function index(): Response
        {
            // usually you will want to make sure the user is authenticated first,
            // see "Authorization" below
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

            // returns your User object, or null if the user is not authenticated
            // use inline documentation to tell your editor your exact User class
            /** @var \App\Entity\User $user */
            $user = $this->getUser();

            // Call whatever methods you have added to your User class
            // For example, if you added a getFirstName() method, you can use that.
            return new Response('Well hi there '.$user->getFirstName());
        }
    }

Fetching the User from a Service
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

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
            // Avoid calling getUser() in the constructor: auth may not
            // be complete yet. Instead, store the entire Security object.
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

.. _denying-access-roles-and-other-authorization:
.. _security-access-control:

Access Control (Authorization)
------------------------------

Users can now log in to your app using your login form. Great! Now, you need to learn
how to deny access and work with the User object. This is called **authorization**,
and its job is to decide if a user can access some resource (a URL, a model object,
a method call, ...).

The process of authorization has two different sides:

#. The user receives a specific role when logging in (e.g. ``ROLE_ADMIN``).
#. You add code so that a resource (e.g. URL, controller) requires a specific
   "attribute" (e.g. a role like ``ROLE_ADMIN``) in order to be accessed.

Roles
~~~~~

When a user logs in, Symfony calls the ``getRoles()`` method on your ``User``
object to determine which roles this user has. In the ``User`` class that
was generated earlier, the roles are an array that's stored in the
database and every user is *always* given at least one role: ``ROLE_USER``::

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
a user should have. The only rule is that every role **must start with** the
``ROLE_`` prefix - otherwise, things will not work as expected. Other than that,
a role is just a string and you can invent whatever you need (e.g. ``ROLE_PRODUCT_ADMIN``).

You will use these roles next to grant access to specific sections of your site.

.. _security-role-hierarchy:

Hierarchical Roles
..................

Instead of giving many roles to each user, you can define role inheritance
rules by creating a role hierarchy:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            role_hierarchy:
                ROLE_ADMIN:       ROLE_USER
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

                <role id="ROLE_ADMIN">ROLE_USER</role>
                <role id="ROLE_SUPER_ADMIN">ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH</role>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            // ...

            $security->roleHierarchy('ROLE_ADMIN', ['ROLE_USER']);
            $security->roleHierarchy('ROLE_SUPER_ADMIN', ['ROLE_ADMIN', 'ROLE_ALLOWED_TO_SWITCH']);
        };

Users with the ``ROLE_ADMIN`` role will also have the ``ROLE_USER`` role.
Users with ``ROLE_SUPER_ADMIN``, will automatically have ``ROLE_ADMIN``,
``ROLE_ALLOWED_TO_SWITCH`` and ``ROLE_USER`` (inherited from
``ROLE_ADMIN``).

.. caution::

    For role hierarchy to work, do not use ``$user->getRoles()`` manually.
    For example, in a controller extending from the :ref:`base controller <the-base-controller-class-services>`::

        // BAD - $user->getRoles() will not know about the role hierarchy
        $hasAccess = in_array('ROLE_ADMIN', $user->getRoles());

        // GOOD - use of the normal security methods
        $hasAccess = $this->isGranted('ROLE_ADMIN');
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

.. note::

    The ``role_hierarchy`` values are static - you cannot, for example, store the
    role hierarchy in a database. If you need that, create a custom
    :doc:`security voter </security/voters>` that looks for the user roles
    in the database.

.. _security-role-authorization:

Add Code to Deny Access
~~~~~~~~~~~~~~~~~~~~~~~

There are **two** ways to deny access to something:

#. :ref:`access_control in security.yaml <security-authorization-access-control>`
   allows you to protect URL patterns (e.g. ``/admin/*``). Simpler, but less flexible;

#. :ref:`in your controller (or other code) <security-securing-controller>`.

.. _security-authorization-access-control:

Securing URL patterns (access_control)
......................................

The most basic way to secure part of your app is to secure an entire URL pattern
in ``security.yaml``. For example, to require ``ROLE_ADMIN`` for all URLs that
start with ``/admin``, you can:

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

You can define as many URL patterns as you need - each is a regular expression.
**BUT**, only **one** will be matched per request: Symfony starts at the top of
the list and stops when it finds the first match:

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
would match ``/admin/foo`` but would also match URLs like ``/foo/admin``.

Each ``access_control`` can also match on IP address, hostname and HTTP methods.
It can also be used to redirect a user to the ``https`` version of a URL pattern.
See :doc:`/security/access_control`.

.. _security-securing-controller:

Securing Controllers and other Code
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

1) If the user is not logged in yet, they will be asked to log in (e.g. redirected
   to the login page).

2) If the user *is* logged in, but does *not* have the ``ROLE_ADMIN`` role, they will
   be shown the 403 access denied page (which you can
   :ref:`customize <controller-error-pages-by-status-code>`).

.. _security-securing-controller-annotations:

Thanks to the SensioFrameworkExtraBundle, you can also secure your controller
using annotations:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/AdminController.php
        // ...

        use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

        /**
         * Require ROLE_ADMIN for all the actions of this controller
         *
         * @IsGranted("ROLE_ADMIN")
         */
        class AdminController extends AbstractController
        {
            /**
             * Require ROLE_SUPER_ADMIN only for this action
             *
             * @IsGranted("ROLE_SUPER_ADMIN")
             */
            public function adminDashboard(): Response
            {
                // ...
            }
        }

    .. code-block:: php-attributes

        // src/Controller/AdminController.php
        // ...

        use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

        /**
         * Require ROLE_ADMIN for all the actions of this controller
         */
        #[IsGranted('ROLE_ADMIN')]
        class AdminController extends AbstractController
        {
            /**
             * Require ROLE_SUPER_ADMIN only for this action
             */
            #[IsGranted('ROLE_SUPER_ADMIN')]
            public function adminDashboard(): Response
            {
                // ...
            }
        }

For more information, see the `FrameworkExtraBundle documentation`_.

.. _security-template:

Access Control in Templates
...........................

If you want to check if the current user has a certain role, you can use
the built-in ``is_granted()`` helper function in any Twig template:

.. code-block:: html+twig

    {% if is_granted('ROLE_ADMIN') %}
        <a href="...">Delete</a>
    {% endif %}

.. _security-isgranted:

Securing other Services
.......................

You can check access *anywhere* in your code by injecting the ``Security``
service. For example, suppose you have a ``SalesReportManager`` service and you
want to include extra details only for users that have a ``ROLE_SALES_ADMIN`` role:

.. code-block:: diff

      // src/SalesReport/SalesReportManager.php

      // ...
      use Symfony\Component\Security\Core\Exception\AccessDeniedException;
    + use Symfony\Component\Security\Core\Security;

      class SalesReportManager
      {
    +     private $security;

    +     public function __construct(Security $security)
    +     {
    +         $this->security = $security;
    +     }

          public function generateReport()
          {
              $salesData = [];

    +         if ($this->security->isGranted('ROLE_SALES_ADMIN')) {
    +             $salesData['top_secret_numbers'] = rand();
    +         }

              // ...
          }

          // ...
      }

If you are using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
Symfony will automatically pass the ``security.helper`` to your service
thanks to autowiring and the ``Security`` type-hint.

You can also use a lower-level
:class:`Symfony\\Component\\Security\\Core\\Authorization\\AuthorizationCheckerInterface`
service. It does the same thing as ``Security``, but allows you to type-hint a
more-specific interface.

Allowing Unsecured Access (i.e. Anonymous Users)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When a visitor is not yet logged in to your website, they are treated as
"unauthenticated" and don't have any roles. This will block them from
visiting your pages if you defined an ``access_control`` rule.

In the ``access_control`` configuration, you can use the ``PUBLIC_ACCESS``
security attribute to exclude some routes for unauthenticated access (e.g.
the login page):

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            enable_authenticator_manager: true

            # ...
            access_control:
                # allow unauthenticated users to access the login form
                - { path: ^/admin/login, roles: PUBLIC_ACCESS }

                # but require authentication for all other admin routes
                - { path: ^/admin, roles: ROLE_ADMIN }

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

                <access-control>
                    <!-- allow unauthenticated users to access the login form -->
                    <rule path="^/admin/login" role="PUBLIC_ACCESS"/>

                    <!-- but require authentication for all other admin routes -->
                    <rule path="^/admin" role="ROLE_ADMIN"/>
                </access-control>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            $security->enableAuthenticatorManager(true);
            // ....

            // allow unauthenticated users to access the login form
            $security->accessControl()
                ->path('^/admin/login')
                ->roles([AuthenticatedVoter::PUBLIC_ACCESS])
            ;

            // but require authentication for all other admin routes
            $security->accessControl()
                ->path('^/admin')
                ->roles(['ROLE_ADMIN'])
            ;
        };

Granting Anonymous Users Access in a Custom Voter
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you are using a :doc:`custom voter </security/voters>`, you can allow
anonymous users access by checking if there is no user set on the token::

    // src/Security/PostVoter.php
    namespace App\Security;

    // ...
    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
    use Symfony\Component\Security\Core\Authentication\User\UserInterface;
    use Symfony\Component\Security\Core\Authorization\Voter\Voter;

    class PostVoter extends Voter
    {
        // ...

        protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
        {
            // ...

            if (!$token->getUser() instanceof UserInterface) {
                // the user is not authenticated, e.g. only allow them to
                // see public posts
                return $subject->isPublic();
            }
        }
    }

Setting Individual User Permissions
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Most applications require more specific access rules. For instance, a user
should be able to only edit their *own* comments on a blog. Voters allow you
to write *whatever* business logic you need to determine access. Using
these voters is similar to the role-based access checks implemented in the
previous chapters. Read :doc:`/security/voters` to learn how to implement
your own voter.

Checking to see if a User is Logged In (IS_AUTHENTICATED_FULLY)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you *only* want to check if a user is logged in (you don't care about roles),
you have the following two options.

Firstly, if you have given *every* user ``ROLE_USER``, you can check for that role.

Secondly, you can use a special "attribute" in place of a role::

    // ...

    public function adminDashboard(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // ...
    }

You can use ``IS_AUTHENTICATED_FULLY`` anywhere roles are used: like
``access_control`` or in Twig.

``IS_AUTHENTICATED_FULLY`` is not a role, but it kind of acts like one, and every
user that has logged in will have this. Actually, there are some special attributes
like this:

* ``IS_AUTHENTICATED_REMEMBERED``: *All* logged in users have this, even
  if they are logged in because of a "remember me cookie". Even if you don't
  use the :doc:`remember me functionality </security/remember_me>`,
  you can use this to check if the user is logged in.

* ``IS_AUTHENTICATED_FULLY``: This is similar to ``IS_AUTHENTICATED_REMEMBERED``,
  but stronger. Users who are logged in only because of a "remember me cookie"
  will have ``IS_AUTHENTICATED_REMEMBERED`` but will not have ``IS_AUTHENTICATED_FULLY``.

* ``IS_REMEMBERED``: *Only* users authenticated using the
  :doc:`remember me functionality </security/remember_me>`, (i.e. a
  remember-me cookie).

* ``IS_IMPERSONATOR``: When the current user is
  :doc:`impersonating </security/impersonating_user>` another user in this
  session, this attribute will match.

.. versionadded:: 5.1

    The ``IS_REMEMBERED`` and ``IS_IMPERSONATOR`` attributes were
    introduced in Symfony 5.1.

.. deprecated:: 5.3

   The ``IS_ANONYMOUS`` and ``IS_AUTHENTICATED_ANONYMOUSLY`` attributes are
   deprecated since Symfony 5.3.

.. _user_session_refresh:

Understanding how Users are Refreshed from the Session
------------------------------------------------------

At the end of every request (unless your firewall is ``stateless``), your
``User`` object is serialized to the session. At the beginning of the next
request, it is deserialized and then passed to your user provider to "refresh" it
(e.g. Doctrine queries for a fresh user).

Then, the two User objects (the original from the session and the refreshed User
object) are "compared" to see if they are "equal". By default, the core
``AbstractToken`` class compares the return values of the ``getPassword()``,
``getSalt()`` and ``getUserIdentifier()`` methods. If any of these are different,
your user will be logged out. This is a security measure to make sure that malicious
users can be de-authenticated if core user data changes.

However, in some cases, this process can cause unexpected authentication problems.
If you are having problems authenticating, it could be that you *are* authenticating
successfully, but you immediately lose authentication after the first redirect.

In that case, review the serialization logic (e.g. the ``__serialize()`` or
``serialize()`` methods) on you user class (if you have any) to make sure
that all the fields necessary are serialized and also exclude all the
fields not necessary to be serialized (e.g. Doctrine relations).

Comparing Users Manually with EquatableInterface
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Or, if you need more control over the "compare users" process, make your User class
implement :class:`Symfony\\Component\\Security\\Core\\User\\EquatableInterface`.
Then, your ``isEqualTo()`` method will be called when comparing users instead
of the core logic.

Security Events
---------------

During the authentication process, multiple events are dispatched that allow you
to hook into the process or customize the response sent back to the user. You
can do this by creating an :doc:`event listener or subscriber </event_dispatcher>`
for these events.

.. tip::

    Every Security firewall has its own event dispatcher
    (``security.event_dispatcher.FIREWALLNAME``). Events are dispatched on
    both the global and the firewall-specific dispatcher. You can register
    on the firewall dispatcher if you want your listener to only be
    called for a specific firewall. For instance, if you have an ``api``
    and ``main`` firewall, use this configuration to register only on the
    logout event in the ``main`` firewall:

    .. configuration-block::

        .. code-block:: yaml

            # config/services.yaml
            services:
                # ...

                App\EventListener\LogoutSubscriber:
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

                    <service id="App\EventListener\LogoutSubscriber">
                        <tag name="kernel.event_subscriber"
                             dispatcher="security.event_dispatcher.main"
                         />
                    </service>
                </services>
            </container>

        .. code-block:: php

            // config/services.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            use App\EventListener\LogoutSubscriber;

            return function(ContainerConfigurator $containerConfigurator) {
                $services = $containerConfigurator->services();

                $services->set(LogoutSubscriber::class)
                    ->tag('kernel.event_subscriber', [
                        'dispatcher' => 'security.event_dispatcher.main',
                    ]);
            };

Authentication Events
~~~~~~~~~~~~~~~~~~~~~

.. raw:: html

    <object data="./_images/security/security_events.svg" type="image/svg+xml"></object>

:class:`Symfony\\Component\\Security\\Http\\Event\\CheckPassportEvent`
    Dispatched after the authenticator created the :ref:`security passport <security-passport>`.
    Listeners of this event do the actual authentication checks (like
    checking the passport, validating the CSRF token, etc.)

:class:`Symfony\\Component\\Security\\Http\\Event\\AuthenticationTokenCreatedEvent`
    Dispatched after the passport was validated and the authenticator
    created the security token (and user). This can be used in advanced use-cases
    where you need to modify the created token (e.g. for multi factor
    authentication).

:class:`Symfony\\Component\\Security\\Core\\Event\\AuthenticationSuccessEvent`
    Dispatched when authentication is nearing success. This is the last
    event that can make an authentication fail by throwing an
    ``AuthenticationException``.

:class:`Symfony\\Component\\Security\\Http\\Event\\LoginSuccessEvent`
    Dispatched after authentication was fully successful. Listeners to this
    event can modify the response sent back to the user.

:class:`Symfony\\Component\\Security\\Http\\Event\\LoginFailureEvent`
    Dispatched after an ``AuthenticationException`` was thrown during
    authentication. Listeners to this event can modify the error response
    sent back to the user.

Other Events
~~~~~~~~~~~~

:class:`Symfony\\Component\\Security\\Http\\Event\\LogoutEvent`
    Dispatched just before a user logs out of your application. See
    :ref:`security-logging-out`.

:class:`Symfony\\Component\\Security\\Http\\Event\\TokenDeauthenticatedEvent`
    Dispatched when a user is deauthenticated, for instance because the
    password was changed. See :ref:`user_session_refresh`.

:class:`Symfony\\Component\\Security\\Http\\Event\\SwitchUserEvent`
    Dispatched after impersonation is completed. See
    :doc:`/security/impersonating_user`.

Frequently Asked Questions
--------------------------

**Can I have Multiple Firewalls?**
    Yes! But it is usually not necessary. Each firewall is like a separate security
    system, being authenticated in one firewall doesn't make you authenticated in
    another one. One firewall can have multiple diverse ways of allowing
    authentication (e.g. form login, API key authentication and LDAP).

**Can I Share Authentication Between Firewalls?**
    Yes, but only with some configuration. If you are using multiple firewalls and
    you authenticate against one firewall, you will *not* be authenticated against
    any other firewalls automatically. Different firewalls are like different security
    systems. To do this you have to explicitly specify the same
    :ref:`reference-security-firewall-context` for different firewalls. However,
    one main firewall is usually sufficient for the needs of most applications.

**Security doesn't seem to work on my Error Pages**
    As routing is done *before* security, 404 error pages are not covered by
    any firewall. This means you cannot check for security or even access the
    user object on these pages. See :doc:`/controller/error_pages`
    for more details.

**My Authentication Doesn't Seem to Work: No Errors, but I am Never Logged In**
    Sometimes authentication may be successful, but after redirecting, you are
    logged out immediately due to a problem loading the ``User`` from the session.
    To see if this is an issue, check your log file (``var/log/dev.log``) for
    the log message:

**Cannot refresh token because user has changed**
    If you see this, there are two possible causes. First, there may be a problem
    loading your User from the session. See :ref:`user_session_refresh`. Second,
    if certain user information was changed in the database since the last page
    refresh, Symfony will purposely log out the user for security reasons.

Learn More
----------

Authentication (Identifying/Logging in the User)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. toctree::
    :maxdepth: 1

    security/passwords
    security/ldap
    security/remember_me
    security/impersonating_user
    security/user_checkers
    security/firewall_restriction
    security/csrf
    security/form_login
    security/custom_authenticator
    security/entry_point

Authorization (Denying Access)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. toctree::
    :maxdepth: 1

    security/voters
    security/access_control
    security/expressions
    security/access_denied_handler
    security/force_https

.. _`5.2 version of this documentation`: https://symfony.com/doc/5.2/security.html
.. _`FrameworkExtraBundle documentation`: https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/index.html
.. _`HWIOAuthBundle`: https://github.com/hwi/HWIOAuthBundle
.. _`OWASP Brute Force Attacks`: https://owasp.org/www-community/controls/Blocking_Brute_Force_Attacks
.. _`brute force login attacks`: https://owasp.org/www-community/controls/Blocking_Brute_Force_Attacks
.. _`MakerBundle`: https://symfony.com/doc/current/bundles/SymfonyMakerBundle/index.html
.. _`SymfonyCastsVerifyEmailBundle`: https://github.com/symfonycasts/verify-email-bundle
.. _`HTTP Basic authentication`: https://en.wikipedia.org/wiki/Basic_access_authentication
.. _`Login CSRF attacks`: https://en.wikipedia.org/wiki/Cross-site_request_forgery#Forging_login_requests
.. _`PHP date relative formats`: https://www.php.net/datetime.formats.relative
