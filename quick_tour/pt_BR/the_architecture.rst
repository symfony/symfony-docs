A Arquitetura
=============

Você é meu heroi! Quem teria pensado que você ainda estaria aqui após as
três primeiras partes? Seu esforço será bem recompensado em breve. As três 
primeiras partes não olharam profundamente a arquitetura do framework. 
Como isto faz o Symfony um framework distante da multidão, vamos mergulhar agora.

.. index::
   single: Directory Structure

A estrutura de Diretorios
-------------------------

A estrutura de diretorios de uma aplicação do Symfony é bastante flexisivel
mas a estrutura de uma sandbox reflete uma tipica e recomendada estrutura 
de uma aplicação do Symfony:

* ``hello/``: Este diretorio, nomeado após sua aplicação, contém os
  arquivos de configuração;

* ``src/``: Todo o código PHP está neste diretorio;

* ``web/``: Este deve ser o diretorio raiz da web.

O Diretorio Web
~~~~~~~~~~~~~~~

O diretorio web é a casa de todos os arquivos publicos e estaticos como imagens,
folha de estilo, e arquivos JavaScript. É onde os front controllers vivem:

.. code-block:: html+php

    # web/index.php
    <?php

    require_once __DIR__.'/../hello/HelloKernel.php';

    $kernel = new HelloKernel('prod', false);
    $kernel->handle()->send();

Como qualquer front controllers, ``index.php`` usa a ``HelloKernel``, que é uma classe Kernel 
para inicializar a aplicação.

.. index::
   single: Kernel

O Diretorio Aplicação
~~~~~~~~~~~~~~~~~~~~~

A classe ``HelloKernel`` é o ponto de entrada principal de configuraçãp 
da aplicação e como tal, é armazenada no diretorio ``hello/``.

Esta classe deve implementar cinco metodos:

* ``registerRootDir()``: Retorna o diretorio raiz de configurações;

* ``registerBundles()``: Retorna um array de todos os pacotes necessários para executar o
  aplicação (observe a referencia a ``Application\HelloBundle\HelloBundle``);

* ``registerBundleDirs()``: Retorna uma matriz que associa namespaces e seus
  diretórios;

* ``registerContainerConfiguration()``: Retorna o objeto principal de configuração
  (Mais sobre isso depois);

* ``registerRoutes()``: Retorna a configuração de roteamento.

De uma olhada na implememtação padrão destes metodos para entender melhor a 
flexibilidade do framework. No começo deste tutprial, você abriu o arquivo 
``hello/config/routing.yml``. O caminho é configurado em ``registerRoutes()``::

    public function registerRoutes()
    {
        $loader = new RoutingLoader($this->getBundleDirs());

        return $loader->load(__DIR__.'/config/routing.yml');
    }

Aqui é também onde você pode alternar entre usar arquivos de configuração YAML para XML
ou código PHP normal, no que você se encaixa melhor.

Para fazer as coisas trabalharem juntas, o kernel requer um arquivo do diretorio ``scr/``::

    // hello/HelloKernel.php
    require_once __DIR__.'/../src/autoload.php';

O Diretorio Source
~~~~~~~~~~~~~~~~~~

O arquivo ``src/autoload.php`` é responsavél por autocarregar todos os arquivos internos 
do diretorio ``scr/``::

    // src/autoload.php
    require_once __DIR__.'/vendor/symfony/src/Symfony/Foundation/UniversalClassLoader.php';

    use Symfony\Foundation\UniversalClassLoader;

    $loader = new UniversalClassLoader();
    $loader->registerNamespaces(array(
        'Symfony'                    => __DIR__.'/vendor/symfony/src',
        'Application'                => __DIR__,
        'Bundle'                     => __DIR__,
        'Doctrine\\Common'           => __DIR__.'/vendor/doctrine/lib/vendor/doctrine-common/lib',
        'Doctrine\\DBAL\\Migrations' => __DIR__.'/vendor/doctrine-migrations/lib',
        'Doctrine\\DBAL'             => __DIR__.'/vendor/doctrine/lib/vendor/doctrine-dbal/lib',
        'Doctrine'                   => __DIR__.'/vendor/doctrine/lib',
        'Zend'                       => __DIR__.'/vendor/zend/library',
    ));
    $loader->registerPrefixes(array(
        'Swift_' => __DIR__.'/vendor/swiftmailer/lib/classes',
        'Twig_'  => __DIR__.'/vendor/twig/lib',
    ));
    $loader->register();

O ``UniversalClassLoader`` do Symfony é usado para carregar automaticamente os 
arquivos que respeita nem a técnica de interoperabilidade `standards`_ para PHP 5.3 namespaces 
ou o PEAR nomeação de `convention`_ classes. Como você pode ver aqui, todas as dependencias 
são armazenadas no diretorio ``vendor/``, mas isto é somente uma convenção. 
Você pode armazenar em qualquer lugar que você quiser, globalmente
em seu servidor ou localmente em seu projeto.

.. index::
   single: Bundles


O Sistema de empacotamento (Bundle)
-----------------------------------

Esta seção começa a arranhar a superfície de um dos maiores e mais poderosos 
recursos do Symfony, o sistema de empacotamento.

Um pacote é como um plugin em outros softwares. Mas por que é chamado
pacote e não plugin então? Porque tudo é um pacote no Symfony, das features do
core do framework até seus códigos escritos para a sua aplicação. Pacotes são 
cidadãos de primeira classe em Symfony. Isso lhe dá a flexibilidade para usar os
recursos pré-construído e embalados em pacotes de terceiros ou para distribuir 
seus próprios pacotes. Isso torna muito fácil escolher quais as funcionalidades 
que permitam a sua aplicação e otimizá-los da maneira que quiser.

Uma aplicação é composta por pacotes definidos no método ``registerBundles()`` 
da classe:: ``HelloKernel``

    // hello/HelloKernel.php

    use Symfony\Foundation\Bundle\KernelBundle;
    use Symfony\Framework\FoundationBundle\FoundationBundle;
    use Symfony\Framework\DoctrineBundle\DoctrineBundle;
    use Symfony\Framework\SwiftmailerBundle\SwiftmailerBundle;
    use Symfony\Framework\ZendBundle\ZendBundle;
    use Application\HelloBundle\HelloBundle;

    public function registerBundles()
    {
        return array(
            new KernelBundle(),
            new FoundationBundle(),
            new DoctrineBundle(),
            new SwiftmailerBundle(),
            new ZendBundle(),
            new HelloBundle(),
        );
    }

Juntamente com o ``HelloBundle`` que nos já comentamos, note que o kernel também
permite ``KernelBundle``, ``FoundationBundle``, ``DoctrineBundle``,
``SwiftmailerBundle``, e ``ZendBundle``. Todos fazem parte do núcleo do framework.

Cada pacote pode ser customizado via configuração, escrevendo arquivos YAML ou XML.
Veja as configurações padrão:

.. code-block:: yaml

    # hello/config/config.yml
    kernel.config: ~
    web.config: ~
    web.templating: ~

Cada entrada, como por exemplo ``kernel.config`` define a configuração do pacote.
Alguns pacotes podem ter varias entradas caso ofereçam muitas funcionalidades
``FoundationBundle``, que tem duas entradas: ``web.config`` e ``web.templating``.

Cada ambiente pode sobescrever a configuração padrão fornecendo um arquivo especifico
de configuração:

.. code-block:: yaml

    # hello/config/config_dev.yml
    imports:
        - { resource: config.yml }

    web.config:
        toolbar: true

    zend.logger:
        priority: info
        path:     %kernel.root_dir%/logs/%kernel.environment%.log

Como vimos na parte anterior, uma aplicação é feita de pacotes definidos no método 
``registerBundles()``, mas como o Symfony sabe onde procurar um pacote? Symfony é
muito flexível neste aspecto. O método ``registerBundleDirs()`` deve retornar um 
array associativo que mapeia namespaces para qualquer diretório válido (locais ou 
globais)::

    public function registerBundleDirs()
    {
        return array(
            'Application'        => __DIR__.'/../src/Application',
            'Bundle'             => __DIR__.'/../src/Bundle',
            'Symfony\\Framework' => __DIR__.'/../src/vendor/symfony/src/Symfony/Framework',
        );
    }

Então quando você referencia o ``HelloBundle`` em um controller name ou em um
template name, o Symfony procura dentro destes diretorios.

Agora você entende porque o Symfony é tão flexivél? Compartilhe seus pacotes 
entre aplicações, armazene localmente o globalmente, você escolhe.

.. index::
   single: Vendors

Vendors
-------

Provavelmente sua aplicação dependerá de biblioteca de terceiros. Estas devem ser
armazenadas no diretorio ``src/vendor``. Ele já contém as bibliotecas do symfony,
SwiftMailer, o ORM Doctrine, o ORM Prople, o sistema de templates Twig, e uma 
seleção de classes do Zend Framework.

.. index::
   single: Cache
   single: Logs

Cache e Logs
------------

Symfony é provavelmente um dos mais rápidos frameworks full-stack. Mas como pode
ser tão rapido se analisa dezenas de arquivos YAML e XML a cada solicitação? Isto
se deve em parte ao sistema de cache. A configuração da aplicação é analisada no
primeiro pedido, depois é compilada em codigo PHP e armazenada no diretorio
``cache/``. No ambiente de desenvolvimento, o Symfony é esperto o suficiente para
limpar o chache quando você altera um arquivo ou muda sua configuração.

Quando desenvolvemos uma aplicação, as coisas podem dar errado em muitos aspectos.
Os arquivos log do diretorio ``logs/`` dizem a você tudo sobre os pedidos e te 
ajudam a corrigir o problema rapidamente.

.. index::
   single: CLI
   single: Command Line

A interface de Linha de Comando
-------------------------------

Cada aplicação vem com uma ferramenta de linha de comando (``console``), ela te 
ajuda a manter sua aplicação. Ele fornece comandos que aumentar a sua
produtividade ao automatizar tarefas tediosas e repetitivas.

Chame-a sem argumentos para aprender mais sobre suas capacidades:

.. code-block:: bash

    $ php hello/console

A opção ``--help`` te ajuda a descobrir o uso de um comando:

.. code-block:: bash

    $ php hello/console router:debug --help

Considerações Finais
--------------------

Me chame de louco, mas após ler esta parte, você deve estar confortável com a 
coisas que o circulam e fazem o Symfony trabalhar por você. Tudo é feito no 
Symfony para estar fora do teu caminho. Então, sinta-se livre para renomear e 
mover os diretorios como achar necessario.

E isto é tudo para um tour rápido. Entre os teste de envio de e-mail, você ainda
necessidade de aprender muito para se tornar um mestre Symfony. Pronto para 
cavar esses temas agora? Não procure mais, vá para a `guides`_ pagina oficial e 
ecolha qualquer tópico que quiser.

.. _standards:  http://groups.google.com/group/php-standards/web/psr-0-final-proposal
.. _convention: http://pear.php.net/
.. _guides:     http://www.symfony-reloaded.org/learn
