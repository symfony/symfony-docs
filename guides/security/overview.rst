.. index::
   single: Security

Segurança
=========

O Symfony2 vem com uma camada de segurança embutida. Ela protege a sua aplicação, 
fornecendo autenticação e autorização.

A *autenticação* garante que o usuário é quem ele diz ser. A *autorização* se 
refere ao processo de decidir se um usuário tem permissão para executar uma ação 
ou não (a autorização vem após a autenticação).

Este documento é uma visão geral rápida desses conceitos principais, mas o poder real é 
explicado em três outros documentos: :doc:`Usuários </guides/security/users>`,
:doc:`Authenticação </guides/security/authentication>`, e
:doc:`Autorização </guides/security/authorization>`.

.. index::
   pair: Security; Configuration

Configuração
------------

Para os casos de uso mais comuns, a segurança no Symfony2 pode ser facilmente configurada 
a partir do arquivo de configuração principal, aqui está uma configuração típica:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        security.config:
            providers:
                main:
                    password_encoder: sha1
                    users:
                        foo: { password: 0beec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33, roles: ROLE_USER }

            firewalls:
                main:
                    pattern:    /.*
                    http-basic: true
                    logout:     true

            access_control:
                - { path: /.*, role: ROLE_USER }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <security:config>
            <security:provider>
                <security:password-encoder hash="sha1" />
                <security:user name="foo" password="0beec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33" roles="ROLE_USER" />
            </security:provider>

            <security:firewall pattern="/.*">
                <security:http-basic />
                <security:logout />
            </security:firewall>

            <security:access-control>
                <security:rule path="/.*" role="ROLE_USER" />
            </security:access-control>
        </security:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('security', 'config', array(
            'provider' => array(
                'main' => array('password_encoder' => 'sha1', 'users' => array(
                    'foo' => array('password' => '0beec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33', 'roles' => 'ROLE_USER'),
                )),
            ),
            'firewalls' => array(
                'main' => array('pattern' => '/.*', 'http-basic' => true, 'logout' => true),
            ),
            'access_control' => array(
                array('path' => '/.*', 'role' => 'ROLE_USER'),
            ),
        ));

Na maioria das vezes, é mais conveniente utilizar todas as configurações relacionadas 
à segurança em um arquivo externo. Se você usar XML, o arquivo externo pode usar o namespace 
security como padrão para torná-lo mais legível:

.. code-block:: xml

        <srv:container xmlns="http://www.symfony-project.org/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://www.symfony-project.org/schema/dic/services"
            xsi:schemaLocation="http://www.symfony-project.org/schema/dic/services http://www.symfony-project.org/schema/dic/services/services-1.0.xsd">

            <config>
                <provider>
                    <password-encoder hash="sha1" />
                    <user name="foo" password="0beec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33" roles="ROLE_USER" />
                </provider>

                <firewall pattern="/.*">
                    <http-basic />
                    <logout />
                </firewall>

                <access-control>
                    <rule path="/.*" role="ROLE_USER" />
                </access-control>
            </config>
        </srv:container>

.. note::

    Todos os exemplos na documentação assumem que você está usando um arquivo 
    externo com o namespace security padrão, como indicado acima.

Como você pode ver, a configuração tem três seções:

* *provider*: Um provedor sabe como criar usuários;

* *firewall*: Um firewall define os mecanismos de autenticação para toda a aplicação ou 
  apenas para uma parte dela;

* *access-control*: As regras de controle de acesso protegem partes da sua aplicação 
  com roles.

Para resumir o workflow, o firewall autentica o cliente com base nas credenciais 
submetidas e no usuário criado pelo provedor, e o controle de acesso autoriza 
o acesso ao recurso.

Autenticação
------------

O Symfony2 suporta muitos mecanismos de autenticação diferentes, e mais, eles podem
ser facilmente adicionados, se necessário; os principais são:

* HTTP Básico;
* HTTP Digest;
* Autenticação baseada em formulário;
* Certificados X.509.

Segue um exemplo de como você pode proteger a sua aplicação com autenticação básica HTTP:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            firewalls:
                main:
                    http-basic: true

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall>
                <http-basic />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'firewalls' => array(
                'main' => array('http-basic' => true),
            ),
        ));

Vários firewalls também podem ser definidos se você precisa de mecanismos de autenticação
diferentes para diferentes partes da aplicação:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            firewalls:
                backend:
                    pattern: /admin/.*
                    http-basic: true
                public:
                    pattern:  /.*
                    security: false

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall pattern="/admin/.*">
                <http-basic />
            </firewall>

            <firewall pattern="/.*" security="false" />
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'firewalls' => array(
                'backend' => array('pattern' => '/admin/.*', 'http-basic' => true),
                'public'  => array('pattern' => '/.*', 'security' => false),
            ),
        ));

.. tip::

    A utilização da autenticação HTTP básica é a mais fácil, mas leia o 
    documento de :doc:`Autenticação </guides/security/authentication>`
    para aprender como configurar outros mecanismos de autenticação, como 
    configurar uma autenticação stateless, como você pode passar por outro 
    usuário, como impor https, e muito mais.

Usuários
--------

Durante a autenticação, o Symfony2 pede ao provedor de usuário para criar o objeto
do usuário correspondente a solicitação do cliente (através de credenciais como 
um nome de usuário e uma senha). Para começar rápido, você pode definir um provedor 
in-memory diretamente na sua configuração:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            providers:
                main:
                    users:
                        foo: { password: foo }

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <provider>
                <user name="foo" password="foo" />
            </provider>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'provider' => array(
                'main' => array('users' => array(
                    'foo' => array('password' => 'foo'),
                )),
            ),
        ));

A configuração acima define um usuário 'foo' com a senha 'foo'. Após a autenticação, 
você pode acessar o usuário autenticado através do contexto de segurança (o usuário 
é uma instância de :class:`Symfony\\Component\\Security\\User\\User`)::

    $user = $container->get('security.context')->getUser();

.. tip::

    Usar o provedor in-memory é uma ótima forma de proteger facilmente o backend
    de seu site pessoal, criar um protótipo, ou fornecer fixtures para 
    os testes. Leia o documento :doc:`Users </guides/security/users>` para 
    saber como evitar que a senha esteja de forma clara, como usar uma Entidade do Doctrine 
    como um provedor de usuário, como definir diversos provedores, e muito mais.

Autorização
-----------

A autorização é opcional, mas lhe fornece uma poderosa forma de restringir o acesso aos 
recursos de sua aplicação com base em roles de usuário:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            providers:
                main:
                    users:
                        foo: { password: foo, roles: ['ROLE_USER', 'ROLE_ADMIN'] }
            access_control:
                - { path: /.*, role: ROLE_USER }

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <provider>
                <user name="foo" password="foo" roles="ROLE_USER,ROLE_ADMIN" />
            </provider>

            <access-control>
                <rule path="/.*" role="ROLE_USER" />
            </access-control>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'provider' => array(
                'main' => array('users' => array(
                    'foo' => array('password' => 'foo', 'roles' => array('ROLE_USER', 'ROLE_ADMIN')),
                )),
            ),

            'access_control' => array(
                array('path' => '/.*', 'role' => 'ROLE_USER'),
            ),
        ));

A configuração acima define um usuário 'foo' com os roles 'ROLE_USER' e 'ROLE_ADMIN' 
e restringe o acesso a toda aplicação para os usuários com o role 'ROLE_USER'.

.. tip::

    Leia o documento :doc:`Authorization </guides/security/authorization>` 
    para aprender como definir uma hierarquia de roles, como personalizar seu 
    template com base em roles, como definir regras de controle de acesso com 
    base em atributos do pedido (request), e muito mais.