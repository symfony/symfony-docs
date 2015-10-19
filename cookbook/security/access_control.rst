How Does the Security access_control Work?
==========================================

For each incoming request, Symfony checks each ``access_control`` entry
to find *one* that matches the current request. As soon as it finds a matching
``access_control`` entry, it stops - only the **first** matching ``access_control``
is used to enforce access.

Each ``access_control`` has several options that configure two different
things:

#. :ref:`should the incoming request match this access control entry <security-book-access-control-matching-options>`
#. :ref:`once it matches, should some sort of access restriction be enforced <security-book-access-control-enforcement-options>`:

.. _security-book-access-control-matching-options:

1. Matching Options
-------------------

Symfony creates an instance of :class:`Symfony\\Component\\HttpFoundation\\RequestMatcher`
for each ``access_control`` entry, which determines whether or not a given
access control should be used on this request. The following ``access_control``
options are used for matching:

* ``path``
* ``ip`` or ``ips``
* ``host``
* ``methods``

Take the following ``access_control`` entries as an example:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...
            access_control:
                - { path: ^/admin, roles: ROLE_USER_IP, ip: 127.0.0.1 }
                - { path: ^/admin, roles: ROLE_USER_HOST, host: symfony\.com$ }
                - { path: ^/admin, roles: ROLE_USER_METHOD, methods: [POST, PUT] }
                - { path: ^/admin, roles: ROLE_USER }

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->
                <rule path="^/admin" role="ROLE_USER_IP" ip="127.0.0.1" />
                <rule path="^/admin" role="ROLE_USER_HOST" host="symfony\.com$" />
                <rule path="^/admin" role="ROLE_USER_METHOD" methods="POST, PUT" />
                <rule path="^/admin" role="ROLE_USER" />
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            // ...
            'access_control' => array(
                array(
                    'path' => '^/admin',
                    'role' => 'ROLE_USER_IP',
                    'ip' => '127.0.0.1',
                ),
                array(
                    'path' => '^/admin',
                    'role' => 'ROLE_USER_HOST',
                    'host' => 'symfony\.com$',
                ),
                array(
                    'path' => '^/admin',
                    'role' => 'ROLE_USER_METHOD',
                    'methods' => 'POST, PUT',
                ),
                array(
                    'path' => '^/admin',
                    'role' => 'ROLE_USER',
                ),
            ),
        ));

For each incoming request, Symfony will decide which ``access_control``
to use based on the URI, the client's IP address, the incoming host name,
and the request method. Remember, the first rule that matches is used, and
if ``ip``, ``host`` or ``method`` are not specified for an entry, that ``access_control``
will match any ``ip``, ``host`` or ``method``:

+-----------------+-------------+-------------+------------+--------------------------------+-------------------------------------------------------------+
| URI             | IP          | HOST        | METHOD     | ``access_control``             | Why?                                                        |
+=================+=============+=============+============+================================+=============================================================+
| ``/admin/user`` | 127.0.0.1   | example.com | GET        | rule #1 (``ROLE_USER_IP``)     | The URI matches ``path`` and the IP matches ``ip``.         |
+-----------------+-------------+-------------+------------+--------------------------------+-------------------------------------------------------------+
| ``/admin/user`` | 127.0.0.1   | symfony.com | GET        | rule #1 (``ROLE_USER_IP``)     | The ``path`` and ``ip`` still match. This would also match  |
|                 |             |             |            |                                | the ``ROLE_USER_HOST`` entry, but *only* the **first**      |
|                 |             |             |            |                                | ``access_control`` match is used.                           |
+-----------------+-------------+-------------+------------+--------------------------------+-------------------------------------------------------------+
| ``/admin/user`` | 168.0.0.1   | symfony.com | GET        | rule #2 (``ROLE_USER_HOST``)   | The ``ip`` doesn't match the first rule, so the second      |
|                 |             |             |            |                                | rule (which matches) is used.                               |
+-----------------+-------------+-------------+------------+--------------------------------+-------------------------------------------------------------+
| ``/admin/user`` | 168.0.0.1   | symfony.com | POST       | rule #2 (``ROLE_USER_HOST``)   | The second rule still matches. This would also match the    |
|                 |             |             |            |                                | third rule (``ROLE_USER_METHOD``), but only the **first**   |
|                 |             |             |            |                                | matched ``access_control`` is used.                         |
+-----------------+-------------+-------------+------------+--------------------------------+-------------------------------------------------------------+
| ``/admin/user`` | 168.0.0.1   | example.com | POST       | rule #3 (``ROLE_USER_METHOD``) | The ``ip`` and ``host`` don't match the first two entries,  |
|                 |             |             |            |                                | but the third - ``ROLE_USER_METHOD`` - matches and is used. |
+-----------------+-------------+-------------+------------+--------------------------------+-------------------------------------------------------------+
| ``/admin/user`` | 168.0.0.1   | example.com | GET        | rule #4 (``ROLE_USER``)        | The ``ip``, ``host`` and ``method`` prevent the first       |
|                 |             |             |            |                                | three entries from matching. But since the URI matches the  |
|                 |             |             |            |                                | ``path`` pattern of the ``ROLE_USER`` entry, it is used.    |
+-----------------+-------------+-------------+------------+--------------------------------+-------------------------------------------------------------+
| ``/foo``        | 127.0.0.1   | symfony.com | POST       | matches no entries             | This doesn't match any ``access_control`` rules, since its  |
|                 |             |             |            |                                | URI doesn't match any of the ``path`` values.               |
+-----------------+-------------+-------------+------------+--------------------------------+-------------------------------------------------------------+

.. _security-book-access-control-enforcement-options:

2. Access Enforcement
---------------------

Once Symfony has decided which ``access_control`` entry matches (if any),
it then *enforces* access restrictions based on the ``roles``, ``allow_if`` and ``requires_channel``
options:

* ``role`` If the user does not have the given role(s), then access is denied
  (internally, an :class:`Symfony\\Component\\Security\\Core\\Exception\\AccessDeniedException`
  is thrown);

* ``allow_if`` If the expression returns false, then access is denied;

* ``requires_channel`` If the incoming request's channel (e.g. ``http``)
  does not match this value (e.g. ``https``), the user will be redirected
  (e.g. redirected from ``http`` to ``https``, or vice versa).

.. tip::

    If access is denied, the system will try to authenticate the user if not
    already (e.g. redirect the user to the login page). If the user is already
    logged in, the 403 "access denied" error page will be shown. See
    :doc:`/cookbook/controller/error_pages` for more information.

.. _book-security-securing-ip:

Matching access_control By IP
-----------------------------

Certain situations may arise when you need to have an ``access_control``
entry that *only* matches requests coming from some IP address or range.
For example, this *could* be used to deny access to a URL pattern to all
requests *except* those from a trusted, internal server.

.. caution::

    As you'll read in the explanation below the example, the ``ips`` option
    does not restrict to a specific IP address. Instead, using the ``ips``
    key means that the ``access_control`` entry will only match this IP address,
    and users accessing it from a different IP address will continue down
    the ``access_control`` list.

Here is an example of how you configure some example ``/internal*`` URL
pattern so that it is only accessible by requests from the local server itself:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...
            access_control:
                #
                - { path: ^/internal, roles: IS_AUTHENTICATED_ANONYMOUSLY, ips: [127.0.0.1, fe80::1, ::1] }
                - { path: ^/internal, roles: ROLE_NO_ACCESS }

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->
                <rule path="^/internal"
                    role="IS_AUTHENTICATED_ANONYMOUSLY"
                    ips="127.0.0.1, fe80::1, ::1"
                />

                <rule path="^/internal" role="ROLE_NO_ACCESS" />
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            // ...
            'access_control' => array(
                array(
                    'path' => '^/internal',
                    'role' => 'IS_AUTHENTICATED_ANONYMOUSLY',
                    'ips' => '127.0.0.1, fe80::1, ::1'
                ),
                array(
                    'path' => '^/internal',
                    'role' => 'ROLE_NO_ACCESS'
                ),
            ),
        ));

Here is how it works when the path is ``/internal/something`` coming from
the external IP address ``10.0.0.1``:

* The first access control rule is ignored as the ``path`` matches but the
  IP address does not match either of the IPs listed;

* The second access control rule is enabled (the only restriction being the
  ``path``) and so it matches. If you make sure that no users ever have
  ``ROLE_NO_ACCESS``, then access is denied (``ROLE_NO_ACCESS`` can be anything
  that does not match an existing role, it just serves as a trick to always
  deny access).

But if the same request comes from ``127.0.0.1``, ``::1`` (the IPv6 loopback
address) or ``fe80::1`` (the IPv6 link-local address):

* Now, the first access control rule is enabled as both the ``path`` and the
  ``ip`` match: access is allowed as the user always has the
  ``IS_AUTHENTICATED_ANONYMOUSLY`` role.

* The second access rule is not examined as the first rule matched.

.. _book-security-allow-if:

Securing by an Expression
~~~~~~~~~~~~~~~~~~~~~~~~~

Once an ``access_control`` entry is matched, you can deny access via the
``roles`` key or use more complex logic with an expression in the ``allow_if``
key:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...
            access_control:
                -
                    path: ^/_internal/secure
                    allow_if: "'127.0.0.1' == request.getClientIp() or has_role('ROLE_ADMIN')"

    .. code-block:: xml

            <access-control>
                <rule path="^/_internal/secure"
                    allow-if="'127.0.0.1' == request.getClientIp() or has_role('ROLE_ADMIN')" />
            </access-control>

    .. code-block:: php

            'access_control' => array(
                array(
                    'path' => '^/_internal/secure',
                    'allow_if' => '"127.0.0.1" == request.getClientIp() or has_role("ROLE_ADMIN")',
                ),
            ),

In this case, when the user tries to access any URL starting with ``/_internal/secure``,
they will only be granted access if the IP address is ``127.0.0.1`` or if
the user has the ``ROLE_ADMIN`` role.

Inside the expression, you have access to a number of different variables
and functions including ``request``, which is the Symfony
:class:`Symfony\\Component\\HttpFoundation\\Request` object (see
:ref:`component-http-foundation-request`).

For a list of the other functions and variables, see
:ref:`functions and variables <book-security-expression-variables>`.

.. _book-security-securing-channel:

Forcing a Channel (http, https)
-------------------------------

You can also require a user to access a URL via SSL; just use the
``requires_channel`` argument in any ``access_control`` entries. If this
``access_control`` is matched and the request is using the ``http`` channel,
the user will be redirected to ``https``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...
            access_control:
                - { path: ^/cart/checkout, roles: IS_AUTHENTICATED_ANONYMOUSLY, requires_channel: https }

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <rule path="^/cart/checkout"
                role="IS_AUTHENTICATED_ANONYMOUSLY"
                requires-channel="https"
            />
        </srv:container>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'access_control' => array(
                array(
                    'path' => '^/cart/checkout',
                    'role' => 'IS_AUTHENTICATED_ANONYMOUSLY',
                    'requires_channel' => 'https',
                ),
            ),
        ));
