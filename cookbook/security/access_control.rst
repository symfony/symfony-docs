How does the Security access_control Work?
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
                <access-control>
                    <rule path="^/admin" role="ROLE_USER_IP" ip="127.0.0.1" />
                    <rule path="^/admin" role="ROLE_USER_HOST" host="symfony\.com$" />
                    <rule path="^/admin" role="ROLE_USER_METHOD" method="POST, PUT" />
                    <rule path="^/admin" role="ROLE_USER" />
                </access-control>
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
                    'method' => 'POST, PUT',
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
it then *enforces* access restrictions based on the ``roles`` and ``requires_channel``
options:

* ``role`` If the user does not have the given role(s), then access is denied
  (internally, an :class:`Symfony\\Component\\Security\\Core\\Exception\\AccessDeniedException`
  is thrown);

* ``requires_channel`` If the incoming request's channel (e.g. ``http``)
  does not match this value (e.g. ``https``), the user will be redirected
  (e.g. redirected from ``http`` to ``https``, or vice versa).

.. tip::

    If access is denied, the system will try to authenticate the user if not
    already (e.g. redirect the user to the login page). If the user is already
    logged in, the 403 "access denied" error page will be shown. See
    :doc:`/cookbook/controller/error_pages` for more information.

.. _book-security-securing-ip:

Securing by IP
--------------

Certain situations may arise when you may need to restrict access to a given
path based on IP. This is particularly relevant in the case of
:ref:`Edge Side Includes <edge-side-includes>` (ESI), for example. When ESI is
enabled, it's recommended to secure access to ESI URLs. Indeed, some ESI may
contain some private content like the current logged in user's information. To
prevent any direct access to these resources from a web browser (by guessing the
ESI URL pattern), the ESI route **must** be secured to be only visible from
the trusted reverse proxy cache.

.. versionadded:: 2.3
    Version 2.3 allows multiple IP addresses in a single rule with the ``ips: [a, b]``
    construct.  Prior to 2.3, users should create one rule per IP address to match and
    use the ``ip`` key instead of ``ips``.

.. caution::

    As you'll read in the explanation below the example, the ``ip`` option
    does not restrict to a specific IP address. Instead, using the ``ip``
    key means that the ``access_control`` entry will only match this IP address,
    and users accessing it from a different IP address will continue down
    the ``access_control`` list.

Here is an example of how you might secure all ESI routes that start with a
given prefix, ``/esi``, from outside access:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...
            access_control:
                - { path: ^/esi, roles: IS_AUTHENTICATED_ANONYMOUSLY, ips: [127.0.0.1, ::1] }
                - { path: ^/esi, roles: ROLE_NO_ACCESS }

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
                <access-control>
                    <rule path="^/esi" role="IS_AUTHENTICATED_ANONYMOUSLY"
                        ips="127.0.0.1, ::1" />
                    <rule path="^/esi" role="ROLE_NO_ACCESS" />
                </access-control>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            // ...
            'access_control' => array(
                array(
                    'path' => '^/esi',
                    'role' => 'IS_AUTHENTICATED_ANONYMOUSLY',
                    'ips' => '127.0.0.1, ::1'
                ),
                array(
                    'path' => '^/esi',
                    'role' => 'ROLE_NO_ACCESS'
                ),
            ),
        ));

Here is how it works when the path is ``/esi/something`` coming from the
``10.0.0.1`` IP:

* The first access control rule is ignored as the ``path`` matches but the
  ``ip`` does not match either of the IPs listed;

* The second access control rule is enabled (the only restriction being the
  ``path`` and it matches): as the user cannot have the ``ROLE_NO_ACCESS``
  role as it's not defined, access is denied (the ``ROLE_NO_ACCESS`` role can
  be anything that does not match an existing role, it just serves as a trick
  to always deny access).

Now, if the same request comes from ``127.0.0.1`` or ``::1`` (the IPv6 loopback
address):

* Now, the first access control rule is enabled as both the ``path`` and the
  ``ip`` match: access is allowed as the user always has the
  ``IS_AUTHENTICATED_ANONYMOUSLY`` role.

* The second access rule is not examined as the first rule matched.

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

            <access-control>
                <rule path="^/cart/checkout"
                    role="IS_AUTHENTICATED_ANONYMOUSLY"
                    requires-channel="https" />
            </access-control>
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
