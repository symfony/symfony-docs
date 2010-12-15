.. index::
   single: Bundles; Configuration

Configuração de Bundle
======================

Para prover mais flexíbilidade, um bundle pode disponibilizar definições de
configurações usando os mecanismos embutidos no Symfony2.

Configuração Simples
--------------------

Para definir uma configuração simples, depende dos ``parametros`` de configuração 
do Symfony2. Os parametros do Symfony2 são simples pares chave/valor. Um valor
sendo qualquer valor PHP válido. Cada nome de parametro deve iniciar uma versão
em minúsicula do nome do bundle (``hello`` para ``HelloBundle``, ou 
``sensio.social.blog`` para ``Sensio\Social\BlogBundle`` por exemplo).

O usuário final pode disponibilizar valores em qualquer arquivo de configuração
XML/YAML/INI:

.. code-block:: xml

    <!-- XML format -->
    <parameters>
        <parameter key="hello.email.from">fabien@example.com</parameter>
    </parameters>

.. code-block:: yaml

    parameters:
        hello.email.from: fabien@example.com

.. code-block:: ini

    [parameters]
    hello.email.from=fabien@example.com

Recupere os parametros de configuração no seu código pelo container::
    
    $container->getParameter('hello.email.from');

Mesmo sendo esse mecanismo simples o bastante, você é encorajado a usar
a configuração semântica descrita abaixo.

.. index::
   single: Configuration; Semantic
   single: Bundle; Extension Configuration

Configuração Semantica
----------------------

Configuração Semantica prove uma maneira ainda mais flexível de prover
configurações para um bundle com as seguintes vantagens sobre parametros
simples:

* Possiblidade de definir mais do que uma configuração (serviços por 
  exemplo);
* Melhor hierarquia na configuração (você pode definir configurações 
  aninhadas);
* Mesclagem inteligente quando vários arquivos de configuração 
  sobrescrevem uma configuração existente;
* Validação de Configuração (Se você definir um arquivo XSD e usar XML);
* Acabamento quando você usar XSD e XML.

Para definiar uma configuração semantica, crie uma extensão da Dependency Injection::

    // HelloBundle/DependencyInjection/HelloExtension.php
    class DocExtension extends LoaderExtension
    {
        public function configLoad($config)
        {
            // ...
        }

        public function getXsdValidationBasePath()
        {
            return __DIR__.'/../Resources/config/';
        }

        public function getNamespace()
        {
            return 'http://www.example.com/symfony/schema/';
        }

        public function getAlias()
        {
            return 'hello';
        }
    }

Siga essas regras:

* A extensão deve ser salva no sub-namespace ``DependencyInjection``;
* A extensão deve ser nomeada depois do nome do bundle a ter um sufixo
  ``Extension`` (``HelloExtension`` para ``HelloBundle``);
* O alias deve ser único e nomeado depois do nome do bundle (``hello`` para
  ``HelloBundle`` ou ``sensio.social.blog`` para ``Sensio\Social\BlogBundle``);
* A extensão deve disponibilizar um schema XSD.

Eventualmente, registre a extensão::

    class HelloBundle extends BaseBundle
    {
        public function buildContainer(ContainerInterface $container)
        {
            Loader::registerExtension(new HelloExtension());
        }
    }

Convenção de Nomes
------------------

Todos os nomes de parametros e serviços começando com ``_`` são reservados para
o framework, os novos arquivos não podem ser definidos pelos bundles.
