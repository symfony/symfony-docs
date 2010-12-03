.. index::
   single: Testes

Testes
=======

Sempre que você escreve uma nova linha de código, você pode estar adicionando
novas falhas. Utilizar testes automatizados é uma forma de previnir isto e 
este tópico lhe mostrará como escrever testes unitários e também testes
funcionais para sua aplicação Symfony2.


Framework de testes
-----------------

Os testes do Symfony2 confiam no PHPUnit, suas melhores práticas e algumas
convenções. Aqui não documentaremos o PHPUnit em si, mas, se você ainda não
conhece, você poderá ler sua excelente `documentação`_.

.. note::
   Symfony2 é compatível com PHPUnit 3.5 ou superior.

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

Functional tests check the integration of the different layers of an
application (from the routing to the views). They are no different from unit
tests as far as PHPUnit is concerned, but they have a very specific workflow:


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


As requisições, cliques e observações são feitas por um cliente que sabe como
falar com a aplicação. Para acessar como um cliente, os testes precisam estender
a classe Symfony2 ``WebTestCase`. A sandbox possui um teste funcional simples para
``HelloController`` que diz o seguinte: 



    // src/Application/HelloBundle/Tests/Controller/HelloControllerTest.php
    namespace Application\HelloBundle\Tests\Controller;

    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

    class HelloControllerTest extends WebTestCase
    {
        public function testIndex()
        {
            $client = $this->createClient();
            $crawler = $client->request('GET', '/hello/Fabien');

            $this->assertEquals(1, count($crawler->filter('html:contains("Hello Fabien")')));
        }
    }

The ``createClient()`` method returns a client tied to the current application::

    $crawler = $client->request('GET', 'hello/Fabien');

The ``request()`` method returns a ``Crawler`` object which can be used to
select elements in the Response, to click on links, and to submit forms.

.. tip::

    The Crawler can only be used if the Response content is an XML or an HTML
    document.

Click on a link by first selecting it with the Crawler using either a XPath
expression or a CSS selector, then use the Client to click on it::

    $link = $crawler->filter('a:contains("Greet")')->eq(1)->link();

    $crawler = $client->click($link);

Submitting a form is very similar; select a form button, optionally override
some form values, and submit the corresponding form::

    $form = $crawler->selectButton('submit');

    // set some values
    $form['name'] = 'Lucas';

    // submit the form
    $crawler = $client->submit($form);

Each ``Form`` field has specialized methods depending on its type::

    // fill an input field
    $form['name'] = 'Lucas';

    // select an option or a radio
    $form['country']->select('France');

    // tick a checkbox
    $form['like_symfony']->tick();

    // upload a file
    $form['photo']->upload('/path/to/lucas.jpg');

Instead of changing one field at a time, you can also pass an array of values
to the ``submit()`` method::

    $crawler = $client->submit($form, array(
        'name'         => 'Lucas',
        'country'      => 'France',
        'like_symfony' => true,
        'photo'        => '/path/to/lucas.jpg',
    ));

Now that you can easily navigate through an application, use assertions to test
that it actually does what you expect it to. Use the Crawler to make assertions
on the DOM::

    // Assert that the response matches a given CSS selector.
    $this->assertTrue(count($crawler->filter('h1')) > 0);

Or, test against the Response content directly if you just want to assert that
the content contains some text, or if the Response is not an XML/HTML
document::

    $this->assertRegExp('/Hello Fabien/', $client->getResponse()->getContent());

.. _documentation: http://www.phpunit.de/manual/3.5/en/
