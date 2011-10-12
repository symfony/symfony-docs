Configuração do Doctrine
========================

Configuração DBAL
-----------------

.. code-block:: yaml

    # config/config.yml
    doctrine.dbal:
        driver:   PDOMySql
        dbname:   Symfony2
        user:     root
        password: null

Você também pode especificar algumas configurações adicionais em uma conexão, 
mas elas não são obrigatórias:

.. code-block:: yaml

    # ...

    doctrine.dbal:
      # ...

      host:                 localhost
      port:                 ~
      path:                 %kernel.data_dir%/symfony.sqlite
      event_manager_class:  Doctrine\Common\EventManager
      configuration_class:  Doctrine\DBAL\Configuration
      wrapper_class:        ~
      options:              []


Se você deseja configurar múltiplas conexões, você pode fazer isso simplesmente 
listando-as sob a chave chamada ``connections``:

.. code-block:: yaml

    doctrine.dbal:
      default_connection:       default
      connections:
        default:
          dbname:               Symfony2
          user:                 root
          password:             null
          host:                 localhost
        customer:
          dbname:               customer
          user:                 root
          password:             null
          host:                 localhost

Se você definiu múltiplas conexões, você pode usar o ``getDatabaseConnection()`` também, 
mas deve passar um argumento com o nome da conexão que deseja obter::

    class UserController extends Controller
    {
        public function indexAction()
        {
            $conn = $this->container->getDatabaseConnection('customer');
        }
    }

Configuração ORM 
----------------

.. code-block:: yaml

    doctrine.orm:
      default_entity_manager:   default
      cache_driver:             apc           # array, apc, memcache, xcache
      entity_managers:
        default:
          connection:           default
        customer:
          connection:           customer

Assim como para o DBAL, se você tiver configurado múltiplas instâncias ``EntityManager`` e deseja
obter uma específica, você pode usar o método ``getEntityManager()`` apenas passando à ele
um argumento que é o nome do ``EntityManager`` que você deseja::

    class UserController extends Controller
    {
        public function indexAction()
        {
            $em = $this->container->getService('doctrine.orm.customer_entity_manager');
        }
    }

Agora surge o cenário onde você deseja alterar as informações de mapeamento 
e atualizar seu esquema do banco de dados de desenvolvimento, sem bagunçar 
tudo e perder os dados existentes. Então, primeiro, vamos adicionar uma 
nova propriedade à nossa entidade ``User``::

    namespace Application\HelloBundle\Entities;

    /** @Entity */
    class User
    {
        /** @Column(type="string") */
        protected $new;

        // ...
    }

Após feito isso, para obter o esquema do banco de dados atualizado com a 
nova coluna, basta executar o seguinte comando:

    $ php hello/console doctrine:schema:update

Agora, seu banco de dados será atualizado e a nova coluna adicionada à tabela.

Comandos de Console
-------------------

A integração do ORM Doctrine2 oferece vários comandos de console no namespace ``doctrine``.
Para ver uma lista dos comandos que você pode executar no console sem argumentos ou opções:

    $ php hello/console
    ...


    doctrine
      :ensure-production-settings  Verifica se o Doctrine está devidamente configurado para um ambiente de produção.
      :schema-tool                 Processa o esquema e também aplica-o diretamente no EntityManager ou gera uma saída SQL.
    doctrine:cache
      :clear-metadata              Limpa todo o cache de metadados para um gerenciador de entidades.
      :clear-query                 Limpa todo o cache de consultas para um gerenciador de entidades. 
      :clear-result                Limpa o cache de resultado para o gerenciador de entidade.
    doctrine:data
      :load                        Carrega os dados definidos nas fixtures para seu banco de dados.
    doctrine:database
      :create                      Cria os bancos de dados configurados.
      :drop                        Remove os bancos de dados configurados.
    doctrine:generate
      :entities                    Gera classes de entidade e métodos stub a partir das informações de mapeamento. 
      :entity                      Gera uma nova entidade do Doctrine dentro de um pacote.
      :proxies                     Gera classes proxy para as classes de entidade.
      :repositories                Gera classes de repositório a partir da sua informação de mapeamento.
    doctrine:mapping
      :convert                     Converte as informações de mapeamento entre os formatos suportados.
      :convert-d1-schema           Converte um esquema do Doctrine1 para arquivos de mapeamento do Doctrine2.
      :import                      Importa as informações de mapeamento de um banco de dados existente.
    doctrine:query
      :dql                         Executa DQL arbitrária diretamente da linha de comando.
      :sql                         Executa SQL arbitrário diretamente da linha de comando.
    doctrine:schema
      :create                      Processa o esquema e também o cria diretamente no `EntityManager Storage Connection` ou gera a saída SQL.
      :drop                        Processa o esquema e descarta o esquema do banco de dados do `EntityManager Storage Connection` ou gera a saída SQL. 
      :update                      Processa o esquema e também atualiza o esquema do banco de dados do `EntityManager Storage Connection` ou gera a saída SQL.

    ...
