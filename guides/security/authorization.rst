.. index::
   single: Security; Authorization

Authorization
=============

When the user is authenticated, you can restrict access to your application
resources via access control rules. Authorization in Symfony2 covers this need
but it also provides a standard and powerful way to decide if a user can
access any resource (a URL, a model object, a method call, ...) thanks to a
flexible access decision manager.

.. index::
   single: Security; Access Control

Defining Access Control Rules for HTTP resources
------------------------------------------------

Authorization is enforced for each request, based on access control rules
defined in your configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            access_control:
                - { path: /admin/.*, role: ROLE_ADMIN }
                - { path: /.*, role: IS_AUTHENTICATED_ANONYMOUSLY }

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <access-control>
                <rule path="/admin/.*" role="ROLE_ADMIN" />
                <rule path="/.*" role="IS_AUTHENTICATED_ANONYMOUSLY" />
            </access-control>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'access_control' => array(
                array('path' => '/admin/.*', 'role' => 'ROLE_ADMIN'),
                array('path' => '/.*', 'role' => 'IS_AUTHENTICATED_ANONYMOUSLY'),
            ),
        ));

For each incoming request, Symfony2 tries to find a matching access control
rule (the first one wins) and throws an
:class:`Symfony\\Component\Security\\Exception\\AccessDeniedException` if the
user has not the needed roles or an
:class:`Symfony\\Component\Security\\Exception\\AuthenticationCredentialsNotFoundException`
if he is not authenticated yet.

In the example above, we match requests based on their path info, but there
are many other ways as you will learn in the next section.

.. tip::

    Symfony2 automatically adds a special role based on the anonymous flag:
    ``IS_AUTHENTICATED_ANONYMOUSLY`` for anonymous users and
    ``IS_AUTHENTICATED_FULLY`` for all others.

Matching a Request
------------------

Access control rules can match a request in many different ways:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            access_control:
                # match the path info
                - { path: /admin/.*, role: ROLE_ADMIN }

                # match the controller class name
                - { controller: .*\\.*Bundle\\Admin\\.*, role: ROLE_ADMIN }

                # match any request attribute
                -
                    attributes:
                        - { key: _controller, pattern: .*\\.*Bundle\\Admin\\.* }
                    role: ROLE_ADMIN

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <access-control>
                <!-- match the path info -->
                <rule path="/admin/.*" role="ROLE_ADMIN" />

                <!-- match the controller class name -->
                <rule controller=".*\\.*Bundle\\Admin\\.*" role="ROLE_ADMIN" />

                <!-- match any request attribute -->
                <rule role="ROLE_ADMIN">
                    <attribute key="_controller" pattern=".*\\.*Bundle\\Admin\\.*" />
                </rule>
            </access-control>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'access_control' => array(
                // match the path info
                array('path' => '/admin/.*', 'role' => 'ROLE_ADMIN'),

                // match the controller class name
                array('controller' => '.*\\.*Bundle\\Admin\\.*', 'role' => 'ROLE_ADMIN'),

                // match any request attribute
                array(
                    'attributes' => array(
                        array('key' => '_controller', 'pattern' => '.*\\.*Bundle\\Admin\\.*'),
                    ),
                    'role' => 'ROLE_ADMIN',
                ),
            ),
        ));

.. index::
   single: Security; HTTPS

Enforcing HTTP or HTTPS
-----------------------

Besides roles, you can also force parts of your website to use either HTTP or
HTTPS:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            access_control:
                - { path: /admin/.*, role: ROLE_ADMIN, requires_channel: https }
                - { path: /.*, requires_channel: http }

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <access-control>
                <rule path="/admin/.*" role="ROLE_ADMIN" requires-channel="https" />
                <rule path="/.*" requires-channel="http" />
            </access-control>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'access_control' => array(
                array('path' => '/admin/.*', 'role' => 'ROLE_ADMIN', 'requires_channel' => 'https'),
                array('path' => '/.*', 'requires_channel' => 'http'),
            ),
        ));

If no ``requires-channel`` is defined, Symfony2 will accept both HTTP and
HTTPS. But as soon as you set the setting to either HTTP or HTTPS, Symfony2
will redirect users if needed.

Access Control in Templates
---------------------------

If you want to check a user role in a template, you can use the dedicated
syntax:

.. configuration-block::

    .. code-block:: php

        <?php if ($view['security']->vote('ROLE_ADMIN')): ?>
            <a href="...">Delete</a>
        <?php endif ?>

    .. code-block:: jinja

        {% ifrole "ROLE_ADMIN" %}
            <a href="...">Delete</a>
        {% endifrole %}

.. note::

    If you need access to the user from a template, you need to pass it
    explicitly.
