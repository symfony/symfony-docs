.. index::
   single: Doctrine; MongoDB
   single: MongoDB

MongoDB
=======

O `Object Document Mapper` `MongoDB` é muito parecido com o ORM Doctrine2 na forma como
ele funciona e na sua arquitetura. Você lida somente com objetos PHP simples e eles
são persistidos transparente sem impor em seu modelo de domínio.

.. tip::
   Você pode ler mais sobre o Doctrine `Object Document Mapper` MongoDB na 
   `documentação`_ dos projetos.

Configuração
------------

Para começar a trabalhar com o Doctrine e o `Object Document Mapper` MongoDB basta 
ativá-los:

.. code-block:: yaml

    # config/config.yml
    doctrine_odm.mongodb: ~

O YAML acima é o exemplo mais simples e usa todos os valores padrão fornecidos, 
se você precisar personalizar mais, é possível especificar a configuração completa:

.. code-block:: yaml

    # config/config.yml
    doctrine_odm.mongodb:
      server: mongodb://localhost:27017
      options:
        connect: true
      metadata_cache_driver: array # array, apc, xcache, memcache

Se você deseja usar o memcache para o cache dos seus metadados e precisa configurar a 
instância ``Memcache``, pode-se fazer o seguinte:

.. code-block:: yaml

    # config/config.yml
    doctrine_odm.mongodb:
      server: mongodb://localhost:27017
      options:
        connect: true
      metadata_cache_driver:
        type: memcache
        class: Doctrine\Common\Cache\MemcacheCache
        host: localhost
        port: 11211
        instance_class: Memcache

Múltiplas conexões
~~~~~~~~~~~~~~~~~~

Se você precisar de múltiplas conexões e gestores de documento você pode usar a seguinte sintaxe:

.. code-block:: yaml

    doctrine_odm.mongodb:
      default_connection: conn2
      default_document_manager: dm2
      metadata_cache_driver: apc
      connections:
        conn1:
          server: mongodb://localhost:27017
          options:
            connect: true
        conn2:
          server: mongodb://localhost:27017
          options:
            connect: true
      document_managers:
        dm1:
          connection: conn1
          metadata_cache_driver: xcache
        dm2:
          connection: conn2

Agora você pode recuperar as configurações de serviços dos serviços de conexão::

    $conn1 = $container->getService('doctrine.odm.mongodb.conn1_connection');
    $conn2 = $container->getService('doctrine.odm.mongodb.conn2_connection');

E você também pode recuperar os serviços dos gestores de documentos configurados que utilizam 
os serviços de conexão acima::

    $dm1 = $container->getService('doctrine.odm.mongodb.dm1_connection');
    $dm2 = $container->getService('doctrine.odm.mongodb.dm1_connection');

XML
~~~

Você pode especificar a mesma configuração via XML, se preferir. Aqui estão os mesmos 
exemplos acima, em XML.

Conexão Única Simples:

.. code-block:: xml

    <?xml version="1.0" ?>

    <container xmlns="http://www.symfony-project.org/schema/dic/services"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xmlns:doctrine="http://www.symfony-project.org/schema/dic/doctrine/odm/mongodb"
        xsi:schemaLocation="http://www.symfony-project.org/schema/dic/services http://www.symfony-project.org/schema/dic/services/services-1.0.xsd
                            http://www.symfony-project.org/schema/dic/doctrine/odm/mongodb http://www.symfony-project.org/schema/dic/doctrine/odm/mongodb/mongodb-1.0.xsd">

        <doctrine:mongodb server="mongodb://localhost:27017">
            <metadata_cache_driver type="memcache">
                <class>Doctrine\Common\Cache\MemcacheCache</class>
                <host>localhost</host>
                <port>11211</port>
                <instance_class>Memcache</instance_class>
            </metadata_cache_driver>
            <options>
                <connect>true</connect>
            </options>
        </doctrine:mongodb>
    </container>

Múltiplas conexões:

.. code-block:: xml

    <?xml version="1.0" ?>

    <container xmlns="http://www.symfony-project.org/schema/dic/services"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xmlns:doctrine="http://www.symfony-project.org/schema/dic/doctrine/odm/mongodb"
        xsi:schemaLocation="http://www.symfony-project.org/schema/dic/services http://www.symfony-project.org/schema/dic/services/services-1.0.xsd
                            http://www.symfony-project.org/schema/dic/doctrine/odm/mongodb http://www.symfony-project.org/schema/dic/doctrine/odm/mongodb/mongodb-1.0.xsd">

        <doctrine:mongodb
                metadata_cache_driver="apc"
                default_document_manager="dm2"
                default_connection="dm2"
                proxy_namespace="Proxies"
                auto_generate_proxy_classes="true"
            >
            <doctrine:connections>
                <doctrine:connection id="conn1" server="mongodb://localhost:27017">
                    <options>
                        <connect>true</connect>
                    </options>
                </doctrine:connection>
                <doctrine:connection id="conn2" server="mongodb://localhost:27017">
                    <options>
                        <connect>true</connect>
                    </options>
                </doctrine:connection>
            </doctrine:connections>
            <doctrine:document_managers>
                <doctrine:document_manager id="dm1" server="mongodb://localhost:27017" metadata_cache_driver="xcache" connection="conn1" />
                <doctrine:document_manager id="dm2" server="mongodb://localhost:27017" connection="conn2" />
            </doctrine:document_managers>
        </doctrine:mongodb>
    </container>

Escrevendo Classes de Documento
-------------------------------

Você pode começar a escrever as classes de documento assim como você escreve normalmente 
as classes PHP. A única diferença é que você deve mapear as classes para o ODM MongoDB. 
Você pode fornecer as informações de mapeamento via yaml, xml ou anotações. Neste exemplo, 
para simplicidade e facilidade de leitura, vamos usar anotações.

Primeiro, vamos escrever uma classe simples `User`::

    // src/Application/HelloBundle/Document/User.php

    namespace Application\HelloBundle\Document;

    class User
    {
        protected $id;
        protected $name;

        public function getId()
        {
            return $this->id;
        }

        public function setName($name)
        {
            $this->name = $name;
        }

        public function getName()
        {
            return $this->name;
        }
    }

Esta classe pode ser usada independente de qualquer camada de persistência 
pois é uma classe PHP regular e não possui nenhuma dependência. Agora precisamos 
anotar a classe para o Doctrine poder ler as informações de mapeamento dos `doc blocks`::

    // ...

    /** @Document(collection="users") */
    class User
    {
        /** @Id */
        protected $id;

        /** @String */
        protected $name;

        // ...
    }

Utilizando Documentos
---------------------

Agora que você tem uma classe PHP que foi mapeada corretamente, você pode começar a 
trabalhar com instâncias do documento persistindo e recuperando do MongoDB.

Em seus controladores é possível acessar as instâncias ``DocumentManager`` do `container`::

    class UserController extends Controller
    {
        public function createAction()
        {
            $user = new User();
            $user->setName('Jonathan H. Wage');

            $dm = $this->container->getService('doctrine.odm.mongodb.document_manager');
            $dm->persist($user);
            $dm->flush();

            // ...
        }
    }

Posteriormente, pode-se recuperar o documento persistido através de seu id::

    class UserController extends Controller
    {
        public function editAction($id)
        {
            $dm = $this->container->getService('doctrine.odm.mongodb.document_manager');
            $user = $dm->find('HelloBundle:User', $id);

            // ...
        }
    }

.. _MongoDB:       http://www.mongodb.org/
.. _documentação: http://www.doctrine-project.org/projects/mongodb_odm/1.0/docs/en
