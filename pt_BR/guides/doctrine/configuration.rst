Configura��o do Doctrine
========================

Configura��o DBAL
-----------------

.. code-block:: yaml

    # config/config.yml
    doctrine.dbal:
        driver:   PDOMySql
        dbname:   Symfony2
        user:     root
        password: null

Voc� tamb�m pode especificar algumas configura��es adicionais em uma conex�o, 
mas elas n�o s�o obrigat�rias:

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


Se voc� deseja configurar m�ltiplas conex�es, voc� pode fazer isso simplesmente 
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

Se voc� definiu m�ltiplas conex�es, voc� pode usar o ``getDatabaseConnection()`` tamb�m, 
mas deve passar um argumento com o nome da conex�o que deseja obter::

    class UserController extends Controller
    {
        public function indexAction()
        {
            $conn = $this->container->getDatabaseConnection('customer');
        }
    }

Configura��o ORM 
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

Assim como para o DBAL, se voc� tiver configurado m�ltiplas inst�ncias ``EntityManager`` e deseja
obter uma espec�fica, voc� pode usar o m�todo ``getEntityManager()`` apenas passando � ele
um argumento que � o nome do ``EntityManager`` que voc� deseja::

    class UserController extends Controller
    {
        public function indexAction()
        {
            $em = $this->container->getService('doctrine.orm.customer_entity_manager');
        }
    }

Agora surge o cen�rio onde voc� deseja alterar as informa��es de mapeamento 
e atualizar seu esquema do banco de dados de desenvolvimento, sem bagun�ar 
tudo e perder os dados existentes. Ent�o, primeiro, vamos adicionar uma 
nova propriedade � nossa entidade ``User``::

    namespace Application\HelloBundle\Entities;

    /** @Entity */
    class User
    {
        /** @Column(type="string") */
        protected $new;

        // ...
    }

Ap�s feito isso, para obter o esquema do banco de dados atualizado com a 
nova coluna, basta executar o seguinte comando:

    $ php hello/console doctrine:schema:update

Agora, seu banco de dados ser� atualizado e a nova coluna adicionada � tabela.

Comandos de Console
-------------------

A integra��o do ORM Doctrine2 oferece v�rios comandos de console no namespace ``doctrine``.
Para ver uma lista dos comandos que voc� pode executar no console sem argumentos ou op��es:

    $ php hello/console
    ...


    doctrine
      :ensure-production-settings  Verifica se o Doctrine est� devidamente configurado para um ambiente de produ��o.
      :schema-tool                 Processa o esquema e tamb�m aplica-o diretamente no EntityManager ou gera uma sa�da SQL.
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
      :entities                    Gera classes de entidade e m�todos stub a partir das informa��es de mapeamento. 
      :entity                      Gera uma nova entidade do Doctrine dentro de um pacote.
      :proxies                     Gera classes proxy para as classes de entidade.
      :repositories                Gera classes de reposit�rio a partir da sua informa��o de mapeamento.
    doctrine:mapping
      :convert                     Converte as informa��es de mapeamento entre os formatos suportados.
      :convert-d1-schema           Converte um esquema do Doctrine1 para arquivos de mapeamento do Doctrine2.
      :import                      Importa as informa��es de mapeamento de um banco de dados existente.
    doctrine:query
      :dql                         Executa DQL arbitr�ria diretamente da linha de comando.
      :sql                         Executa SQL arbitr�rio diretamente da linha de comando.
    doctrine:schema
      :create                      Processa o esquema e tamb�m o cria diretamente no `EntityManager Storage Connection` ou gera a sa�da SQL.
      :drop                        Processa o esquema e descarta o esquema do banco de dados do `EntityManager Storage Connection` ou gera a sa�da SQL. 
      :update                      Processa o esquema e tamb�m atualiza o esquema do banco de dados do `EntityManager Storage Connection` ou gera a sa�da SQL.

    ...
