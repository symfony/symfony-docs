.. index::
   pair: Tests; Configuration

Configurando os Testes
======================

.. index::
   pair: PHPUnit; Configuration

Configuração do PHPUnit
-----------------------

Cada aplicação tem sua própria configuração do PHPUnit, gravada no arquivo
``phpunit.xml.dist``. Você pode alterar esse arquivo para mudar as configurações
padrão ou criar um arquivo ``phpunit.xml`` para configurá-lo para sua máquina
local.

.. tip::
   Grave o arquivo ``phpunit.xml.dist`` no seu repositório de código, e ignore o 
   arquivo ``phpunit.xml``.

Por padrão, apenas os testes armazenados no namespace ``Application`` são executados
pelo comando ``phpunit``. Mas você pode facilmente adicionar mais namespaces. Por
exemplo, a seguinte configuração adiciona os testes de um pacote instalado de terceiros:

.. code-block:: xml

    <!-- hello/phpunit.xml.dist -->
    <testsuites>
        <testsuite name="Project Test Suite">
            <directory>../src/Application/*/Tests</directory>
            <directory>../src/Bundle/*/Tests</directory>
        </testsuite>
    </testsuites>

Para adicionar outros namespaces no code coverage, altera também a seção ``<filter>``:

.. code-block:: xml

    <filter>
        <whitelist>
            <directory>../src/Application</directory>
            <directory>../src/Bundle</directory>
            <exclude>
                <directory>../src/Application/*/Resources</directory>
                <directory>../src/Application/*/Tests</directory>
                <directory>../src/Bundle/*/Resources</directory>
                <directory>../src/Bundle/*/Tests</directory>
            </exclude>
        </whitelist>
    </filter>

Configuração do Cliente
-----------------------

O Cliente usado pelos testes funcionais cria um Kernel que é executado em um
ambiente especial de testes (``test``), para que você possa alterá-lo como quiser:

.. code-block:: yaml

    # config_test.yml
    imports:
        - { resource: config_dev.yml }

    web.config:
        toolbar: false

    zend.logger:
        priority: debug

    kernel.test: ~

Você também pode alterar o ambiente padrão (``test``) e sobrescrever o modo de depuração
 (``true``) passando eles como opções do método createClient()::

    $client = $this->createClient(array(
        'environment' => 'my_test_env',
        'debug'       => false,
    ));

Se a sua aplicação se comporta de acordo com algum cabeçalho HTTP, passe eles como o segundo
argumento de ``createClient()``::

    $client = $this->createClient(array(), array(
        'HTTP_HOST'       => 'en.example.com',
        'HTTP_USER_AGENT' => 'MySuperBrowser/1.0',
    ));

Você também pode sobrescrever os cabeçalhos HTTP baseado em uma requisição::

    $client->request('GET', '/', array(), array(
        'HTTP_HOST'       => 'en.example.com',
        'HTTP_USER_AGENT' => 'MySuperBrowser/1.0',
    ));

.. tip::
   Para fornecer seu próprio Cliente, sobrescreva o parametro ``test.client.class`` ou 
   defina um serviço ``test.client``.
