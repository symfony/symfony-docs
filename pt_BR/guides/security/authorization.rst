.. index::
   single: Security; Authorization

Autorização
===========

Quando o usuários estiver autenticado, você pode restringir o acesso aos recursos
da sua aplicação usando regras de controle de acesso. A Autorização no Symfony2 
cobre essa necessidade mas também provê uma maneira padrão e poderosa de decidir
se o usuário pode acessar algum recurso (uma URL, um model, uma chamada de método, ...)
graças a um flexível gerenciador de decisão de acesso.

.. index::
   single: Security; Access Control

Definindo Regras de Controle de Acesso para Recursos HTTP
---------------------------------------------------------

A Autorização é executada para cara requisição, baseada nas regras de controle
de acesso definidas na sua configuração:

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

Para cara requisição que chega, o Symfony2 tenta encontrar uma regra de controle de acesso
que case (a primeira a casar é escolhida) e dispara uma 
:class:`Symfony\\Component\Security\\Exception\\AccessDeniedException` se o usuário não tiver 
as permissões necessárias ou uma 
:class:`Symfony\\Component\Security\\Exception\\AuthenticationCredentialsNotFoundException`
se o usuário não estiver autenticado ainda.

No exemplo acima, nós comparamos as requisições basiadas nas informações do path, mas
tem várias outras maneiras que você vai aprender nessa seção.

..tip::

    O Symfony2 automaticamente adiciona uma role especial baseada na flag anônima:
    ``IS_AUTHENTICATED_ANONYMOUSLY`` para usuários anônimos e
    ``IS_AUTHENTICATED_FULLY`` para todos os outros.

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

Executando HTTP ou HTTPS
------------------------

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

        <?php if ($view['user']->hasRole('ROLE_ADMIN')): ?>
            <a href="...">Delete</a>
        <?php endif ?>

    .. code-block:: jinja

        {% ifrole "ROLE_ADMIN" %}
            <a href="...">Delete</a>
        {% endifrole %}

.. note::

    If you need access to the user from a template, you need to pass it
    explicitly.
