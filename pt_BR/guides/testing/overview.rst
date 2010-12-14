.. index::
   single: Testes

Testes
=======

Sempre que você escreve uma nova linha de código, você pode estar adicionando
novas falhas. Utilizar testes automatizados é uma forma de cobrir isto e 
este tópico lhe mostrará como escrever testes unitários e também testes
funcionais para sua aplicação Symfony2.


Framework de testes
-----------------

Os testes do Symfony2 confiam no PHPUnit, suas melhores práticas e algumas
convenções. Aqui não documentaremos o PHPUnit em si, mas, se você ainda não
conhece, você poderá ler sua excelente `documentação`_.

.. Nota::
   Symfony2 trabalha com PHPUnit 3.5 ou superior.

A configuração PHPUnit padrão procura por testes em subdiretórios ``Tests/``:

.. code-block:: xml

    <!-- app/phpunit.xml.dist -->

    <phpunit ... bootstrap="../src/autoload.php">
        <testsuites>
            <testsuite name="Projeto Conjunto de Testes">
                <directory>../src/Application/*/Tests</directory>
            </testsuite>
        </testsuites>

        ...
    </phpunit>

Executar um conjunto de teste para uma determinada aplicação é simples:

.. code-block:: bash

    # Especifica o diretório de configuração na linha de comando
    $ phpunit -c app/

    # ou então você pode rodar o phpunit a partir do diretórios da aplicação
    $ cd app/
    $ phpunit

.. tip::
   A cobertura de código pode ser gerada com a opção ``--coverage-html``.

.. index::
   single: Testes; Testes Unitários

Testes Unitários
----------

Escrever testes unitários Symfony2 não é diferente do que escrever testes
unitários PHPUnit. Por convenção, é recomendado replicar a estrutura de 
diretórios do bundle em seu subdiretório ``Tests/``.Então, escreva testes
para a classe ``Application\HelloBundle\Model\Article`` no arquivo
``Application/HelloBundle/Tests/Model/ArticleTest.php``.

Em um teste unitário, autoloading é ativado automaticamente através do arquivo
``src/autoload.php`` (como configurado por padrão no arquivo ``phpunit.xml.dist``).

A execução de testes de um determinado arquivo ou diretório também é muito fácil:

.. code-block:: bash

    # Executar todos os testes do Model
    $ phpunit -c app Application/HelloBundle/Tests/Model/

    # Executar todos os testes da classe Article
    $ phpunit -c app Application/HelloBundle/Tests/Model/ArticleTest.php

.. index::
   single: Testes; Testes Funcionais

Testes Funcionais
----------------

Testes funcionais verificam a integração das diferentes camadas de uma aplicação
(a partir do roteamento até a camada view). Eles não são diferentes dos testes unitários,
até onde o PHPUnit se preocupa, mas eles têm um trabalho muito específico:


* Fazer uma requisição;
* Testar a resposta;
* Clicar em um link ou enviar um formulário;;
* Testar a resposta;
* Limpar e repetir.

Requests, clicks, and submissions are done by a client that knows how to talk
to the application. To access such a client, your tests need to extend the
Symfony2 ``WebTestCase`` class. The sandbox provides a simple functional test
for ``HelloController`` that reads as follows::


As requisições, cliques e submissões são feitas por um cliente que sabe como
se comunicar com a aplicação. Para acessar como um cliente, os testes precisam estender
a classe Symfony2 ``WebTestCase`. A sandbox possui um teste funcional simples para
``HelloController`` que diz o seguinte::

    // src/Application/HelloBundle/Tests/Controller/HelloControllerTest.php
    namespace Application\HelloBundle\Tests\Controller;

    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

    class HelloControllerTest extends WebTestCase
    {
        public function testIndex()
        {
            $client = $this->createClient();
            $crawler = $client->request('GET', '/hello/Fabien');

            $this->assertEquals(1, count($crawler->filter('html:contains("Olá Fabien")')));
        }
    }

O método ``createClient()`` retorna um cliente vinculado ao aplicativo atual:

    $crawler = $client->request('GET', 'hello/Fabien');

O método ``request()`` retorna um objeto ``Crawler`` que pode ser usado para
selecionar elementos da Resposta, clicar em links e submeter formulários.

.. Dica::

    O Crawler somente pode ser usado  e o conteúdo da resposta é um documento XML ou um documento HTML.

Primeiro encontre o link com o Crawler, utilizando uma expressão XPath ou um seletor CSS,
e em seguida use o Cliente para clicar nele::

    $link = $crawler->filter('a:contains("Saudacao")')->eq(1)->link();

    $crawler = $client->click($link);

Submeter um formulário é bem parecido; Selecione o botão de submit e, opcionalmente, altere valores
do formulário, e então envie-o::

    $form = $crawler->selectButton('submit');

    // defina alguns valores
    $form['name'] = 'Lucas';

    // envie o formulário
    $crawler = $client->submit($form);

Cada campo do ``Formulário`` tem métodos especializados, dependendo de seu tipo::

    //  preenchendo um campo texto
    $form['name'] = 'Lucas';

    // selecionar uma opção ou um radio
    $form['country']->select('França');

    // marcando um checkbox
    $form['like_symfony']->tick();

    // enviando  um arquivo
    $form['photo']->upload('/caminho/para/lucas.jpg');

Ao invés de alterar um campo de cada vez, você também pode passar uma matriz
de valores para o método ``submit()``::

    $crawler = $client->submit($form, array(
        'name'         => 'Lucas',
        'country'      => 'França',
        'like_symfony' => true,
        'photo'        => '/caminho/para/lucas.jpg',
    ));

Agora que você pode navegar facilmente através de uma aplicação, usar declarações para testar
se ela realmente faz o que você esperava. Use o Crawler  para fazer declarações sobre o DOM::

    // Declara que a resposta deve corresponder com um seletor CSS especificado.
    $this->assertTrue(count($crawler->filter('h1')) > 0);

Ou, compare o conteúdo da resposta se você quiser apenas confirmar que o conteúdo contém
algum texto, ou se a resposta não é um documento XML/HTML::

    $this->assertRegExp('/Olá Fabien/', $client->getResponse()->getContent());

.. _documentação: http://www.phpunit.de/manual/3.5/en/
