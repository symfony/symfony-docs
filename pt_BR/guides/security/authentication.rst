.. index::
   single: Security; Authentication

Autenticação
============

A autenticação no Symfony2 é gerenciada por um sistema de Firewall. Ele é composto 
de listeners que reforçam a segurança e redirecionam o usuário se as suas credenciais 
não estiverem disponíveis, não forem suficientes, ou estiverem simplesmente erradas.

.. note::

    O Firewall é implementado através do evento ``core.security``, notificado logo 
    após o ``core.request``. Todas as características descritas neste documento são 
    implementadas como listeners para este evento.

.. index::
   single: Security; Firewall
  pair: Security; Configuration

O Mapa do Firewall
------------------

O Firewall pode ser configurado para proteger a sua aplicação como um todo, ou 
para utilizar diferentes estratégias de autenticação para diferentes partes da aplicação.

Normalmente, um site pode abrir a parte pública para todos, proteger o backend através 
de um formulário para autenticação, e proteger a API/Web Service pública através de uma 
autenticação básica HTTP:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            firewalls:
                backend:
                    pattern:    /admin/.*
                    form-login: true
                    logout:     true
                api:
                    pattern:    /api/.*
                    http_basic: true
                    stateless:  true
                public:
                    pattern:    /.*
                    security:   false

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall pattern="/admin/.*">
                <form-login />
                <logout />
            </firewall>
            <firewall pattern="/api/.*" stateless="true">
                <http-basic />
            </firewall>
            <firewall pattern="/.*" security="false" />
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'firewalls' => array(
                'backend' => array('pattern' => '/admin/.*', 'http_basic' => true, 'logout' => true),
                'api'     => array('pattern' => '/api/.*', 'http_basic' => true, 'stateless' => true),
                'public'  => array('pattern' => '/.*', 'security' => false),
            ),
        ));

Cada configuração do firewall é ativada quando o pedido de entrada corresponde
à expressão regular definida pela configuração ``pattern``. Esse padrão deve 
corresponder à informação do caminho do pedido (``preg_match('#^'.PATTERN_VALUE.'$#',
$request->getPathInfo())``.)

.. tip::

    A ordem de definição das configurações do firewall é importante pois o Symfony2 
    usará a primeira configuração para que o padrão coincida com o pedido (por isso 
    é necessário definir, em primeiro lugar, as configurações mais específicas).

.. index::
   pair: Security; Configuration

Mecanismos de Autenticação
--------------------------

Out of the box, o Symfony2 suporta os seguintes mecanismos de autenticação:

* HTTP Básico;
* HTTP Digest;
* Autenticação baseada em formulário;
* Certificados X.509;
* Autenticação Anônima.

Cada mecanismo de autenticação consiste de duas classes que fazem o trabalho: 
uma listener e uma de ponto de entrada. A *listener* tenta autenticar os 
pedidos de entrada. Quando o usuário não estiver autenticado, ou 
quando o listener detectar as credenciais erradas, o *ponto de entrada* cria 
uma resposta para enviar um feedback ao usuário e fornecer uma maneira para 
ele entrar com suas credenciais.

Você pode configurar um firewall para usar mais de um mecanismo de autenticação:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            firewalls:
                backend:
                    pattern:    /admin/.*
                    x509:       true
                    http_basic: true
                    form_login: true
                    logout:     true

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall pattern="/admin/.*">
                <x509 />
                <http-basic />
                <form-login />
                <logout />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'firewalls' => array(
                'backend' => array(
                    'pattern'    => '/admin/.*',
                    'x509'       => true,
                    'http_basic' => true,
                    'form_login' => true,
                    'logout'     => true,
                ),
            ),
        ));

A user accessing a resource under ``/admin/`` will be able to provide a valid
X.509 certificate, an Authorization HTTP header, or use a form to login.

Um usuário acessando um recurso em ``/admin/`` será capaz de fornecer um certificado 
X.509 válido, um header HTTP de Autorização, ou usar um formulário para login.

.. note::

    Quando o usuário não estiver autenticado e, se houver mais de um mecanismo 
    de autenticação, o Symfony2 define automaticamente um ponto de entrada padrão 
    (no exemplo acima, o formulário de login, mas se o usuário enviar um header 
    HTTP de Autorização com credenciais erradas, o Symfony2 usará o ponto de entrada
    HTTP básico.)

.. note::

    HTTP Basic authentication is interoperable, but not secure. HTTP Digest is
    more secure, but not really interoperable in practice.

    Autenticação HTTP básica é interoperável, mas não segura. HTTP Digest é mais 
    segura, mas não é realmente interoperável em prática.

.. index::
   single: Security; HTTP Basic

HTTP Básica
~~~~~~~~~~~

Configurar a autenticação HTTP básica é bem simples:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            firewalls:
                main:
                    http_basic: true

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
                'main' => array('http_basic' => true),
            ),
        ));

.. index::
   single: Security; HTTP Digest

HTTP Digest
~~~~~~~~~~~

Configurar a autenticação HTTP Digest é bem simples:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            firewalls:
                main:
                    http_digest: true

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall>
                <http-digest />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'firewalls' => array(
                'main' => array('http_digest' => true),
            ),
        ));

.. caution::

    Para usar o HTTP Digest, você deve armazenar as senhas do usuário de forma clara.

.. index::
   single: Security; Form based

Autenticação baseada em formulário
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

A autenticação baseada em formulário é o mecanismo de autenticação mais utilizado 
na web hoje em dia:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            firewalls:
                main:
                    form_login: true

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall>
                <form-login />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'firewalls' => array(
                'main' => array('form_login' => true),
            ),
        ));

Quando o usuário não estiver autenticado, ele é redirecionado para a URL ``login_path``
(``/login`` por padrão).

Esse listener depende de um formulário para interagir com o usuário. Ele lida com a
submissão do formulário automaticamente, mas não com a sua exibição, assim, você mesmo 
deve implementar esta parte::

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\Security\SecurityContext;

    class SecurityController extends Controller
    {
        public function loginAction()
        {
            // get the error if any (works with forward and redirect -- see below)
            if ($this->get('request')->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
                $error = $this->get('request')->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
            } else {
                $error = $this->get('request')->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
            }

            return $this->render('SecurityBundle:Security:login.php', array(
                // last username entered by the user
                'last_username' => $this->get('request')->getSession()->get(SecurityContext::LAST_USERNAME),
                'error'         => $error,
            ));
        }
    }

E o template correspondente:

.. configuration-block::

    .. code-block:: html+php

        <?php if ($error): ?>
            <div><?php echo $error ?></div>
        <?php endif; ?>

        <form action="<?php echo $view['router']->generate('_security_check') ?>" method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="_username" value="<?php echo $last_username ?>" />

            <label for="password">Password:</label>
            <input type="password" id="password" name="_password" />

            <input type="submit" name="login" />
        </form>

    .. code-block:: jinja

        {% if error %}
            <div>{{ error }}</div>
        {% endif %}

        <form action="{% path "_security_check" %}" method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="_username" value="{{ last_username }}" />

            <label for="password">Password:</label>
            <input type="password" id="password" name="_password" />

            <input type="submit" name="login" />
        </form>

O template deve ter os campos ``_username`` e ``_password`, e a URL de submissão do 
formulário deve ser o valor da configuração ``check_path`` (``/login_check`` por padrão).

Finalmente, adicione as rotas para as URLs ``/login`` (valor do ``login_path``)
e ``/login_check`` (valor do ``login_check``):

.. code-block:: xml

    <route id="_security_login" pattern="/login">
        <default key="_controller">SecurityBundle:Security:login</default>
    </route>

    <route id="_security_check" pattern="/login_check" />


Após uma falha de autenticação, o usuário é redirecionado para a página de login. 
Você pode usar forward em vez de definir o ``failure_forward`` como ``true``. Você
também pode redirecionar ou avançar (forward) para outra página, se você definir a 
configuração ``failure_path``.

Após uma autenticação bem-sucedida, o usuário é redirecionado com base no 
seguinte algoritmo:

* se ``always_use_default_target_path`` for ``true`` (``false`` por padrão),
  redireciona o usuário para o ``default_target_path`` (``/`` por padrão);

* se o pedido contém um parâmetro denominado ``_target_path`` (configurável através 
  do ``target_path_parameter``), redireciona o usuário para o valor deste parâmetro;

* se houver uma URL de destino armazenado na sessão (o que é feito automaticamente quando 
  um usuário é redirecionado para a página de login), redireciona o usuário para esta URL;

* se ``use_referer`` está definida como ``true`` (``false`` é o padrão), redireciona 
  o usuário para a URL Referrer;

* Redireciona o usuário para a URL ``default_target_path`` (``/`` por padrão).

.. note::

    Todas as URLs devem ser valores do path info ou URLs absolutas.

Os valores padrão para todas as configurações são os mais sensatos, mas aqui está 
um exemplo de configuração que mostra como substituir todos eles:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            firewalls:
                main:
                    form_login:
                        check_path:                     /login_check
                        login_path:                     /login
                        failure_path:                   null
                        always_use_default_target_path: false
                        default_target_path:            /
                        target_path_parameter:          _target_path
                        use_referer:                    false

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall>
                <form-login
                    check_path="/login_check"
                    login_path="/login"
                    failure_path="null"
                    always_use_default_target_path="false"
                    default_target_path="/"
                    target_path_parameter="_target_path"
                    use_referer="false"
                />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'firewalls' => array(
                'main' => array('form_login' => array(
                    'check_path'                     => '/login_check',
                    'login_path'                     => '/login',
                    'failure_path'                   => null,
                    'always_use_default_target_path' => false,
                    'default_target_path'            => '/',
                    'target_path_parameter'          => _target_path,
                    'use_referer'                    => false,
                )),
            ),
        ));

.. index::
   single: Security; X.509 certificates

Certificados X.509 
~~~~~~~~~~~~~~~~~~

Os certificados X.509 são uma ótima maneira de autenticar os usuários se você conhece todos eles:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            firewalls:
                main:
                    x509: true

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall>
                <x509 />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'firewalls' => array(
                'main' => array('x509' => true),
            ),
        ));

Como o Symfony2 não valida o certificado em si, e, porque, obviamente não é possível
padronizar a senha, primeiro você deve configurar o servidor web corretamente 
antes de ativar esse mecanismo de autenticação. Aqui está uma configuração simples, 
mas que funciona para o Apache:

.. code-block:: xml

    <VirtualHost *:443>
        ServerName intranet.example.com:443

        DocumentRoot "/some/path"
        DirectoryIndex app.php
        <Directory "/some/path">
            Allow from all
            Order allow,deny
            SSLOptions +StdEnvVars
        </Directory>

        SSLEngine on
        SSLCertificateFile "/path/to/server.crt"
        SSLCertificateKeyFile "/path/to/server.key"
        SSLCertificateChainFile "/path/to/ca.crt"
        SSLCACertificateFile "/path/to/ca.crt"
        SSLVerifyClient require
        SSLVerifyDepth 1
    </VirtualHost>

Por padrão, o usuário é o e-mail declarado no certificado (o valor da variável 
de ambiente ``SSL_CLIENT_S_DN_Email``).

.. tip::

    A autenticação de certificado só funciona quando o usuário acessar a aplicação 
    via HTTPS.

.. index::
   single: Security; Anonymous Users

Usuários Anônimos
~~~~~~~~~~~~~~~~~

Quando você desativar a segurança, nenhum usuário será anexado mais ao pedido. 
Se você ainda quiser um, você pode ativar usuários anônimos. Um usuário anônimo 
é autenticado, mas possui somente o role IS_AUTHENTICATED_ANONYMOUSLY. A autenticação 
"real" só ocorre quando o usuário acessa um recurso limitado por uma norma mais 
restritiva de controle de acesso:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            firewalls:
                main:
                    anonymous: true

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall>
                <anonymous />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'firewalls' => array(
                'main' => array('anonymous' => true),
            ),
        ));

Como os usuários anônimos são autenticados, o método ``isAuthenticated()`` retorna ``true``. 
Para verificar se o usuário é anônimo, verifique o role ``IS_AUTHENTICATED_ANONYMOUSLY`` 
(note que todos os usuários não-anônimos têm o role ``IS_AUTHENTICATED_FULLY``).

.. index::
   single: Security; Stateless Authentication

Autenticação Stateless
----------------------

Por padrão, o Symfony2 baseia-se em um cookie (a sessão) para manter o contexto 
de segurança do usuário. Mas, se você usar certificados ou autenticação HTTP,
por exemplo, a persistência não é necessária pois as credenciais estão disponíveis
em cada pedido (request). Neste caso, e, se você não precisa armazenar nada entre 
os pedidos, você pode ativar a autenticação stateless (o que significa que nenhum 
cookie será criado pelo Symfony2):

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            firewalls:
                main:
                    http_basic: true
                    stateless:  true

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall stateless="true">
                <http-basic />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'firewalls' => array(
                'main' => array('http_basic' => true, 'stateless' => true),
            ),
        ));

.. note::

    Se você usar um formulário de login, o Symfony2 criará um cookie mesmo 
    que você defina ``stateless`` para ``true``.


.. index::
   single: Security; Impersonating

Representar um usuário
----------------------

Às vezes, é útil poder alternar de um usuário para outro sem ter que sair e 
logar novamente (por exemplo, quando você está depurando ou tentando entender 
um bug que um usuário vê e você não consegue reproduzí-lo.) Isso pode ser feito 
facilmente, ativando o listener ``switch-user``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            firewalls:
                main:
                    http_basic:  true
                    switch_user: true

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall>
                <http-basic />
                <switch-user />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'firewalls' => array(
                'main'=> array('http_basic' => true, 'switch_user' => true),
            ),
        ));


Para alternar para outro usuário, basta adicionar uma query string com o 
parâmetro ``_switch_user`` tendo como valor o nome do usuário à URL atual:

    http://example.com/somewhere?_switch_user=thomas

Para voltar ao usuário original, use o nome de usuário especial ``_exit``:

    http://example.com/somewhere?_switch_user=_exit

Naturalmente, esse recurso deve ser disponibilizado a um grupo pequeno de 
usuários. Por padrão, o acesso é restrito aos usuários com o role 
'ROLE_ALLOWED_TO_SWITCH'. Mude o role padrão com a configuração ``role`` e, 
para segurança extra, também altere o nome do parâmetro através da configuração
``parameter``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            firewalls:
                main:
                    http_basic:  true
                    switch_user: { role: ROLE_ADMIN, parameter: _want_to_be_this_user }

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall>
                <http-basic />
                <switch-user role="ROLE_ADMIN" parameter="_want_to_be_this_user" />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'firewalls' => array(
                'main'=> array(
                    'http_basic'  => true,
                    'switch_user' => array('role' => 'ROLE_ADMIN', 'parameter' => '_want_to_be_this_user'),
                ),
            ),
        ));

.. index::
   single: Security; Logout

Logout de Usuários
------------------

Se você quiser fornecer uma maneira dos seus usuários efetuarem logout, 
ative o listener logout:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            firewalls:
                main:
                    http_basic: true
                    logout:     true

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall>
                <http-basic />
                <logout />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'firewalls' => array(
                'main'=> array('http_basic' => true, 'logout' => true),
            ),
        ));

Por padrão, é realizado o logout dos usuários quando eles acessam o caminho 
``/logout`` e eles são redirecionados para ``/``. Isso pode ser facilmente 
alterado através das configurações ``path`` e ``target``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            firewalls:
                main:
                    http_basic: true
                    logout:     { path: /signout, target: /signin }

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall>
                <http-basic />
                <logout path="/signout" target="/signin" />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'firewalls' => array(
                'main'=> array(
                    'http_basic' => true,
                    'logout' => array('path' => '/signout', 'target' => '/signin')),
            ),
        ));

Autenticação e Providers de Usuário
-----------------------------------

Por padrão, um firewall usa o primeiro provider de usuário declarado para 
autenticação. Mas, se você quiser usar providers de usuário diferentes para
partes diferentes do seu site, você pode explicitamente alterar o provider 
de usuário para um firewall, ou apenas para um mecanismo de autenticação:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            providers:
                default:
                    password_encoder: sha1
                    entity: { class: SecurityBundle:User, property: username }
                certificate:
                    users:
                        fabien@example.com: { roles: ROLE_USER }

            firewalls:
                backend:
                    pattern:    /admin/.*
                    x509:       { provider: certificate }
                    form-login: { provider: default }
                    logout:     true
                api:
                    provider:   default
                    pattern:    /api/.*
                    http_basic: true
                    stateless:  true

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <provider name="default">
                <password-encoder>sha1</password-encoder>
                <entity class="SecurityBundle:User" property="username" />
            </provider>

            <provider name="certificate">
                <user name="fabien@example.com" roles="ROLE_USER" />
            </provider>

            <firewall pattern="/admin/.*">
                <x509 provider="certificate" />
                <form-login provider="default" />
                <logout />
            </firewall>
            <firewall pattern="/api/.*" stateless="true" provider="default">
                <http-basic />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'providers' => array(
                'default' => array(
                    'password_encoder' => 'sha1',
                    'entity' => array('class' => 'SecurityBundle:User', 'property' => 'username'),
                ),
                'certificate' => array('users' => array(
                    'fabien@example.com' => array('roles' => 'ROLE_USER'),
                ),
            ),

            'firewalls' => array(
                'backend' => array(
                    'pattern' => '/admin/.*',
                    'x509' => array('provider' => 'certificate'),
                    'form-login' => array('provider' => 'default')
                    'logout' => true,
                ),
                'api' => array(
                    'provider' => 'default',
                    'pattern' => '/api/.*',
                    'http_basic' => true,
                    'stateless' => true,
                ),
            ),
        ));

No exemplo acima, as URLs ``/admin/.*`` aceitam usuários do provider de usuário 
``certificate`` quando utilizar a autenticação X.509, e o provider ``default``
quando o usuário inscreve-se (sign in) com um formulário. As URLs ``/api/.*`` 
usam o provider ``default`` para todos os mecanismos de autenticação.

.. note::

    Os listeners não usam os providers de usuário diretamente, mas providers de
    autenticação. Eles fazem a autenticação real, como checar a senha, e eles 
    podem usar um provider de usuário para isto (este não é o caso do provider 
    de autenticação anônima, por exemplo).