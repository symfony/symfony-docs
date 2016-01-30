.. index::
    single: Security; Configuration reference

SecurityBundle Configuration ("security")
=========================================

The security system is one of the most powerful parts of Symfony and can
largely be controlled via its configuration.

Full Default Configuration
--------------------------

The following is the full default configuration for the security system.
Each part will be explained in the next section.

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            access_denied_url:    ~ # Example: /foo/error403

            # strategy can be: none, migrate, invalidate
            session_fixation_strategy:  migrate
            hide_user_not_found:  true
            always_authenticate_before_granting:  false
            erase_credentials:    true
            access_decision_manager:
                strategy:             affirmative
                allow_if_all_abstain:  false
                allow_if_equal_granted_denied:  true
            acl:

                # any name configured in doctrine.dbal section
                connection:           ~
                cache:
                    id:                   ~
                    prefix:               sf2_acl_
                provider:             ~
                tables:
                    class:                acl_classes
                    entry:                acl_entries
                    object_identity:      acl_object_identities
                    object_identity_ancestors:  acl_object_identity_ancestors
                    security_identity:    acl_security_identities
                voter:
                    allow_if_object_identity_unavailable:  true

            encoders:
                # Examples:
                Acme\DemoBundle\Entity\User1: sha512
                Acme\DemoBundle\Entity\User2:
                    algorithm:           sha512
                    encode_as_base64:    true
                    iterations:          5000

                # PBKDF2 encoder
                # see the note about PBKDF2 below for details on security and speed
                Acme\Your\Class\Name:
                    algorithm:            pbkdf2
                    hash_algorithm:       sha512
                    encode_as_base64:     true
                    iterations:           1000
                    key_length:           40

                # Example options/values for what a custom encoder might look like
                Acme\DemoBundle\Entity\User3:
                    id:                   my.encoder.id

                # BCrypt encoder
                # see the note about bcrypt below for details on specific dependencies
                Acme\DemoBundle\Entity\User4:
                    algorithm:            bcrypt
                    cost:                 13

                # Plaintext encoder
                # it does not do any encoding
                Acme\DemoBundle\Entity\User5:
                    algorithm:            plaintext
                    ignore_case:          false

            providers:            # Required
                # Examples:
                my_in_memory_provider:
                    memory:
                        users:
                            foo:
                                password:           foo
                                roles:              ROLE_USER
                            bar:
                                password:           bar
                                roles:              [ROLE_USER, ROLE_ADMIN]

                my_entity_provider:
                    entity:
                        class:              SecurityBundle:User
                        property:           username
                        # name of a non-default entity manager
                        manager_name:       ~

                # Example custom provider
                my_some_custom_provider:
                    id:                   ~

                # Chain some providers
                my_chain_provider:
                    chain:
                        providers:          [ my_in_memory_provider, my_entity_provider ]

            firewalls:            # Required
                # Examples:
                somename:
                    pattern: .*
                    # restrict the firewall to a specific host
                    host: admin\.example\.com
                     # restrict the firewall to specific http methods
                    methods: [GET, POST]
                    request_matcher: some.service.id
                    access_denied_url: /foo/error403
                    access_denied_handler: some.service.id
                    entry_point: some.service.id
                    provider: some_key_from_above
                    # manages where each firewall stores session information
                    # See "Firewall Context" below for more details
                    context: context_key
                    stateless: false
                    x509:
                        provider: some_key_from_above
                    remote_user:
                        provider: some_key_from_above
                    http_basic:
                        provider: some_key_from_above
                    http_digest:
                        provider: some_key_from_above
                    form_login:
                        # submit the login form here
                        check_path: /login_check

                        # the user is redirected here when they need to log in
                        login_path: /login

                        # if true, forward the user to the login form instead of redirecting
                        use_forward: false

                        # login success redirecting options (read further below)
                        always_use_default_target_path: false
                        default_target_path:            /
                        target_path_parameter:          _target_path
                        use_referer:                    false

                        # login failure redirecting options (read further below)
                        failure_path:    /foo
                        failure_forward: false
                        failure_path_parameter: _failure_path
                        failure_handler: some.service.id
                        success_handler: some.service.id

                        # field names for the username and password fields
                        username_parameter: _username
                        password_parameter: _password

                        # csrf token options
                        csrf_parameter:       _csrf_token
                        csrf_token_id:        authenticate
                        csrf_token_generator: my.csrf_token_generator.id

                        # by default, the login form *must* be a POST, not a GET
                        post_only:      true
                        remember_me:    false

                        # by default, a session must exist before submitting an authentication request
                        # if false, then Request::hasPreviousSession is not called during authentication
                        # new in Symfony 2.3
                        require_previous_session: true

                    remember_me:
                        token_provider: name
                        secret: "%secret%"
                        name: NameOfTheCookie
                        lifetime: 3600 # in seconds
                        path: /foo
                        domain: somedomain.foo
                        secure: false
                        httponly: true
                        always_remember_me: false
                        remember_me_parameter: _remember_me
                    logout:
                        path:   /logout
                        target: /
                        invalidate_session: false
                        delete_cookies:
                            a: { path: null, domain: null }
                            b: { path: null, domain: null }
                        handlers: [some.service.id, another.service.id]
                        success_handler: some.service.id
                    anonymous: ~

                # Default values and options for any firewall
                some_firewall_listener:
                    pattern:              ~
                    security:             true
                    request_matcher:      ~
                    access_denied_url:    ~
                    access_denied_handler:  ~
                    entry_point:          ~
                    provider:             ~
                    stateless:            false
                    context:              ~
                    logout:
                        csrf_parameter:       _csrf_token
                        csrf_token_generator: ~
                        csrf_token_id:        logout
                        path:                 /logout
                        target:               /
                        success_handler:      ~
                        invalidate_session:   true
                        delete_cookies:

                            # Prototype
                            name:
                                path:                 ~
                                domain:               ~
                        handlers:             []
                    anonymous:
                        secret:               "%secret%"
                    switch_user:
                        provider:             ~
                        parameter:            _switch_user
                        role:                 ROLE_ALLOWED_TO_SWITCH

            access_control:
                requires_channel:     ~

                # use the urldecoded format
                path:                 ~ # Example: ^/path to resource/
                host:                 ~
                ips:                  []
                methods:              []
                roles:                []
            role_hierarchy:
                ROLE_ADMIN:      [ROLE_ORGANIZER, ROLE_USER]
                ROLE_SUPERADMIN: [ROLE_ADMIN]

.. versionadded:: 2.8
    The ``secret`` option of ``anonymous`` and ``remember_me`` was introduced
    in Symfony 2.8. Prior to 2.8, it was called ``key``.

.. _reference-security-firewall-form-login:

Form Login Configuration
------------------------

When using the ``form_login`` authentication listener beneath a firewall,
there are several common options for configuring the "form login" experience.

For even more details, see :doc:`/cookbook/security/form_login`.

The Login Form and Process
~~~~~~~~~~~~~~~~~~~~~~~~~~

login_path
..........

**type**: ``string`` **default**: ``/login``

This is the route or path that the user will be redirected to (unless ``use_forward``
is set to ``true``) when they try to access a protected resource but isn't
fully authenticated.

This path **must** be accessible by a normal, un-authenticated user, else
you may create a redirect loop. For details, see
":ref:`Avoid Common Pitfalls <book-security-common-pitfalls>`".

check_path
..........

**type**: ``string`` **default**: ``/login_check``

This is the route or path that your login form must submit to. The firewall
will intercept any requests (``POST`` requests only, by default) to this
URL and process the submitted login credentials.

Be sure that this URL is covered by your main firewall (i.e. don't create
a separate firewall just for ``check_path`` URL).

use_forward
...........

**type**: ``boolean`` **default**: ``false``

If you'd like the user to be forwarded to the login form instead of being
redirected, set this option to ``true``.

username_parameter
..................

**type**: ``string`` **default**: ``_username``

This is the field name that you should give to the username field of your
login form. When you submit the form to ``check_path``, the security system
will look for a POST parameter with this name.

password_parameter
..................

**type**: ``string`` **default**: ``_password``

This is the field name that you should give to the password field of your
login form. When you submit the form to ``check_path``, the security system
will look for a POST parameter with this name.

post_only
.........

**type**: ``boolean`` **default**: ``true``

By default, you must submit your login form to the ``check_path`` URL as
a POST request. By setting this option to ``false``, you can send a GET
request to the ``check_path`` URL.

Redirecting after Login
~~~~~~~~~~~~~~~~~~~~~~~

* ``always_use_default_target_path`` (type: ``boolean``, default: ``false``)
* ``default_target_path`` (type: ``string``, default: ``/``)
* ``target_path_parameter`` (type: ``string``, default: ``_target_path``)
* ``use_referer`` (type: ``boolean``, default: ``false``)

.. _reference-security-pbkdf2:

Logout Configuration
--------------------

invalidate_session
~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

By default, when users log out from any firewall, their sessions are invalidated.
This means that logging out from one firewall automatically logs them out from
all the other firewalls.

The ``invalidate_session`` option allows to redefine this behavior. Set this
option to ``false`` in every firewall and the user will only be logged out from
the current firewall and not the other ones.

Using the PBKDF2 Encoder: Security and Speed
--------------------------------------------

The `PBKDF2`_ encoder provides a high level of Cryptographic security, as
recommended by the National Institute of Standards and Technology (NIST).

You can see an example of the ``pbkdf2`` encoder in the YAML block on this
page.

But using PBKDF2 also warrants a warning: using it (with a high number
of iterations) slows down the process. Thus, PBKDF2 should be used with
caution and care.

A good configuration lies around at least 1000 iterations and sha512
for the hash algorithm.

.. _reference-security-bcrypt:

Using the BCrypt Password Encoder
---------------------------------

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...

            encoders:
                Symfony\Component\Security\Core\User\User:
                    algorithm: bcrypt
                    cost:      15

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <!-- ... -->
            <encoder
                class="Symfony\Component\Security\Core\User\User"
                algorithm="bcrypt"
                cost="15"
            />
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            // ...
            'encoders' => array(
                'Symfony\Component\Security\Core\User\User' => array(
                    'algorithm' => 'bcrypt',
                    'cost'      => 15,
                ),
            ),
        ));

The ``cost`` can be in the range of ``4-31`` and determines how long a password
will be encoded. Each increment of ``cost`` *doubles* the time it takes
to encode a password.

If you don't provide the ``cost`` option, the default cost of ``13`` is
used.

.. note::

    You can change the cost at any time — even if you already have some
    passwords encoded using a different cost. New passwords will be encoded
    using the new cost, while the already encoded ones will be validated
    using a cost that was used back when they were encoded.

A salt for each new password is generated automatically and need not be
persisted. Since an encoded password contains the salt used to encode it,
persisting the encoded password alone is enough.

.. note::

    All the encoded passwords are ``60`` characters long, so make sure to
    allocate enough space for them to be persisted.

    .. _reference-security-firewall-context:

Firewall Context
----------------

Most applications will only need one :ref:`firewall <book-security-firewalls>`.
But if your application *does* use multiple firewalls, you'll notice that
if you're authenticated in one firewall, you're not automatically authenticated
in another. In other words, the systems don't share a common "context":
each firewall acts like a separate security system.

However, each firewall has an optional ``context`` key (which defaults to
the name of the firewall), which is used when storing and retrieving security
data to and from the session. If this key were set to the same value across
multiple firewalls, the "context" could actually be shared:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...

            firewalls:
                somename:
                    # ...
                    context: my_context
                othername:
                    # ...
                    context: my_context

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <security:config>
            <firewall name="somename" context="my_context">
                <! ... ->
            </firewall>
            <firewall name="othername" context="my_context">
                <! ... ->
            </firewall>
        </security:config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'somename' => array(
                    // ...
                    'context' => 'my_context'
                ),
                'othername' => array(
                    // ...
                    'context' => 'my_context'
                ),
            ),
        ));

HTTP-Digest Authentication
--------------------------

To use HTTP-Digest authentication you need to provide a realm and a secret:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            firewalls:
                somename:
                    http_digest:
                        secret: '%secret%'
                        realm: 'secure-api'

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <security:config>
            <firewall name="somename">
                <http-digest secret="%secret%" realm="secure-api" />
            </firewall>
        </security:config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'somename' => array(
                    'http_digest' => array(
                        'secret' => '%secret%',
                        'realm'  => 'secure-api',
                    ),
                ),
            ),
        ));

.. versionadded:: 2.8
    The ``secret`` option was introduced in Symfony 2.8. Prior to 2.8, it was
    called ``key``.

.. _`PBKDF2`: https://en.wikipedia.org/wiki/PBKDF2
.. _`ircmaxell/password-compat`: https://packagist.org/packages/ircmaxell/password-compat
