.. index::
   single: Bundles; Best Practices

Melhores Práticas para Pacotes (Bundles)
========================================

Um pacote é um diretório que possui uma estrutura bem definida e pode hospedar 
qualquer coisa, de classes à controladores e web resources. Mesmo os 
pacotes sendo muito flexíveis, você deve seguir algumas boas práticas recomendadas 
se pretende distribuí-los.

.. index::
   pair: Bundles; Naming Conventions

Nome do Pacote
--------------

Um pacote é também um namespace PHP, composto por vários segmentos:

* O **namespace principal**: ou ``Bundle``, para pacotes reutilizáveis, ou
  ``Application`` para pacotes específicos de aplicações;
* O **namespace fornecedor (vendor)** (opcional para pacotes ``Application``): algo
  exclusivo para você ou sua empresa (por exemplo, Sensio);
* *(opcional)* Os **namespace(s) categoria**  para melhor organizar um conjunto
  grande de pacotes;
* O **nome do pacote**.

.. caution::
   O namespace fornecedor e os namespaces de categorias somente estão disponíveis a 
   partir do Symfony2 PR3.

O nome do pacote deve seguir as seguintes regras:

* Usar apenas caracteres alfanuméricos e sublinhados;
* Usar o nome em CamelCase;
* Usar um nome descritivo e curto (não mais do que 2 palavras);
* Prefixar o nome com a concatenação dos namespaces do fornecedor e 
  categoria;
* Sufixo do nome com ``Bundle``.

Alguns bons nomes para pacotes:

=================================== ==========================
Namespace                           Nome do Pacote
=================================== ==========================
``Bundle\Sensio\BlogBundle``        ``SensioBlogBundle``
``Bundle\Sensio\Social\BlogBundle`` ``SensioSocialBlogBundle``
``Application\BlogBundle``          ``BlogBundle``
=================================== ==========================

Estrutura de Diretório
----------------------

A estrutura básica de diretório de um pacote ``HelloBundle`` deve ser como 
a seguinte:

    XXX/...
        HelloBundle/
            HelloBundle.php
            Controller/
            Resources/
                meta/
                    LICENSE
                config/
                doc/
                    index.rst
                views/
                web/
            Tests/

Os diretórios ``XXX`` refletem a estrutura do namespace do pacote.

Os seguintes arquivos são obrigatórios:

* ``HelloBundle.php``;
* ``Resources/meta/LICENSE``: A licença completa para o código;
* ``Resources/doc/index.rst``: O arquivo raiz para a documentação do pacote.

.. note::
   Estas convenções garantem que as ferramentas automatizadas possam contar
   com essa estrutura padrão para funcionar.

A profundidade dos sub-diretórios deve ser mantida mínima para a maioria 
das classes e arquivos utilizados (2 níveis, no máximo). Mais níveis podem 
ser definidos para arquivos não-estratégicos e menos utilizados.

O diretório do pacote é somente leitura. Se você precisa gravar arquivos temporários, 
armazene-os sob o diretório ``cache/`` ou ``log/`` da aplicação host. As ferramentas 
podem gerar arquivos na estrutura de diretórios do pacote, mas, apenas se os 
arquivos gerados serão parte do repositório.

As seguintes classes e arquivos têm local específico:

========================= =====================
Tipo                      Diretório
========================= =====================
Controllers               ``Controller/``
Templates                 ``Resources/views/``
Unit and Functional Tests ``Tests/``
Web Resources             ``Resources/web/``
Configuration             ``Resources/config/``
Commands                  ``Command/``
========================= =====================

Classes
-------

A estrutura de diretórios do pacote é usada como a hierarquia do namespace. 
Por exemplo, um controlador ``HelloController`` é armazenado no 
``Bundle/HelloBundle/Controller/HelloController.php`` e o nome completo qualificado 
da classe é ``Bundle\HelloBundle\Controller\HelloController``.

Todas as classes e arquivos devem seguir os `padrões`_ de codificação do Symfony2.

Algumas classes devem ser vistas como fachadas (``Facade``) e devem ser tão curtas 
quanto possível, como Commands, Helpers, Listeners e Controllers.

As classes que se conectam ao ``Event Dispatcher`` devem ter um nome que termina 
com ``Listener``.

Classes de exceções (``Exceptions``) devem ser armazenadas em um sub-namespace ``Exception``.

Fornecedores (``Vendors``)
----------------------

Um pacote não deve incorporar bibliotecas PHP de terceiros. Em vez, deve 
contar com o padrão de autoloading do Symfony2.

Um pacote não deve incorporar bibliotecas de terceiros escritas em JavaScript, 
CSS ou qualquer outra linguagem.

Testes
------

Um pacote deverá vir com um conjunto de testes escritos com o PHPUnit e 
armazenados sob o diretório ``Tests/``. Os testes devem seguir os seguintes princípios:

* O conjunto de testes deve ser executável com um simples comando ``phpunit`` 
  a partir da aplicação sample;
* Os testes funcionais devem ser utilizados apenas para testar a saída de resposta 
  e algumas informações de perfil, se você tiver alguma;
* A cobertura de código deve ser de, pelo menos, 95% da base de código.

.. note::
   Um conjunto de testes não deve conter scripts ``AllTests.php``, mas deve contar
   com a existência de um arquivo ``phpunit.xml.dist``.

Documentação
------------

Todas as classes e funções devem vir com PHPDoc completo.

A documentação extensiva também deve ser fornecida no formato 
:doc:`reStructuredText </contributing/documentation/format>`, no diretório
``Resources/doc/``; o arquivo ``Resources/doc/index.rst`` é o único arquivo obrigatório.

Templates
---------

Se um pacote fornece templates, eles devem ser definidos em PHP simples. 
Um pacote não deve fornecer um layout principal, mas estender o template 
``base`` padrão (que deve fornecer dois slots: ``content`` e ``head``).

.. note::
   A única outra engine de template suportada é o Twig, mas somente para 
   casos específicos.

Configuração
------------

A configuração deve ser feita através do `mecanismo`_ embutido do Symfony2. Um 
pacote deve fornecer todas as suas configurações padrão em XML.

.. _padrões: http://www.symfony-reloaded.org/contributing/Code/Standards
.. _mecanismo: http://www.symfony-reloaded.org/guides/Bundles/Configuration
