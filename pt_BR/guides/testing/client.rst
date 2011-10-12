.. index::
   single: Tests; Client

O Cliente de Testes
===================

O Cliente de testes simula um cliente HTTP como um navegador.

.. note::
   O Cliente de teste é baseado nos componentes ``BrowserKit`` e ``Crawler``.

Fazendo Requisições
-------------------

O cliente sabe como fazer requisições para uma aplicação Symfony2::

    $crawler = $client->request('GET', '/hello/Fabien');

O método ``request()`` usa o método HTTP e a URL como argumantos e retorna uma
instancia de ``Crawler``.

Use o Crawler para encontrar elementos DOM na Resposta. Esses elementos podem
ser usados para clicas em links e submeter formulários::

    $link = $crawler->selectLink('Go elsewhere...')->link();
    $crawler = $client->click($link);

    $form = $crawler->selectButton('validate')->form();
    $crawler = $client->submit($form, array('name' => 'Fabien'));

Os métodos ``click()`` e ``submit()`` retornam ambos um objeto ``Crawler``. Esse 
método é a melhor maneira de navegar por uma aplicação, por ela esconder muitos
detalhes. Por exemplo, quando você submete um formulário, ele detecta automaticamente
o método HTTP e a URL do formulário, isso te dá uma ótima API para enviar arquivos,
e ele mescla os valores enviados com os valores padrões desse formulário, e ainda mais.

.. tip::
   O Crawler tem sua própria :doc:`documentação <crawler>`. Leia isso para aprender mais
   sobre os objetos ``Link`` e ``Form``.

Mas você também pode simular submissões de formulários e requisições complexas  com o 
a inclusão de argumentos no método ``request()``::

    // Form submission
    $client->request('POST', '/submit', array('name' => 'Fabien'));

    // Form submission with a file upload
    $client->request('POST', '/submit', array('name' => 'Fabien'), array('photo' => '/path/to/photo'));

    // Specify HTTP headers
    $client->request('DELETE', '/post/12', array(), array(), array('PHP_AUTH_USER' => 'username', 'PHP_AUTH_PW' => 'pa$$word'));

Quando uma requisição retorna um redirecionamento na resposta, o cliente automaticamente
segue ele. Esse comportamento pode ser alterado com o método ``followRedirects()``::

    $client->followRedirects(false);

Quando o cliente não segue os redirecionamentos, você pode forçar o redirecionamento
com o método ``followRedirect()``::

    $crawler = $client->followRedirect();

E por último, mas não menos importante, você pode forçar que cada requisição seja executada
como um processo PHP, para evitar qualquer efeito colateral quando estiver trabalhando com
vários clientes no mesmo scriptt::

    $client->insulate();

Navegando
---------

O Cliente suporta muitas operações que podem ser feitas no navegador real::

    $client->back();
    $client->forward();
    $client->reload();

    // Clears all cookies and the history
    $client->restart();

Acessando Objetos Internos
--------------------------

Se você usar o cliente para testar sua aplicação, você pode querer acessar os
objetos internos do cliente::

    $history = $client->getHistory();
    $cookieJar = $client->getCookieJar();

Você também pode acessar os objetos relacionados a última requisição::

    $request = $client->getRequest();
    $response = $client->getResponse();
    $crawler = $client->getCrawler();
    $profiler = $client->getProfiler();

Se as requisições não forem isoladas, você também pode acessar o ``Container`` e
o ``Kernel``::

    $container = $client->getContainer();
    $kernel = $client->getKernel();
