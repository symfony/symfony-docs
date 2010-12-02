Executando os Testes do Symfony2
================================

Antes de enviar um :doc:`patch <patches>` para inclusão, você deve executar
a suite de testes do Symfony2 para se assegurar que você não quebrou nada.

PHPUnit
-------

Para executar a suite de testes do Symfony2, instale primeiro o PHPUnit 3.5.0 ou anterior. 
Como ele não está estável ainda, sua melhor chance é usar a última versão do repositório:

.. code-block:: bash

    $ git clone git://github.com/sebastianbergmann/phpunit.git
    $ cd phpunit
    $ pear package
    $ pear install PHPUnit-3.5.XXX.tgz

Dependencias (opcional)
-----------------------

Para executar toda a suite de testes, incluindo testes que dependem de dependências
externas, o Symfony2 precisa ser capaz de carrregá-las. Por padrão, elas são carregadas
automaticamente do `vendor/`, sob o diretório principal (veja `autoload.php.dist`).

A suite de testes precisa das seguintes bibliotecas de terceiros:

* Doctrine
* Doctrine Migrations
* Phing
* Propel
* Swiftmailer
* Twig
* Zend Framework

Para instalar elas todas, execute o script `install_vendors.sh`:

.. code-block:: bash

    $ sh install_vendors.sh

.. note::
   Note que o script leva um tempo para finalizar a execução.

Após a instalação, você pode atualizar os vendor a qualquer momento 
usando o script `update_vendors.sh`:

.. code-block:: bash

    $ sh update_vendors.sh

Executando
----------

Primeiro, atualize as bibliotecas de terceiros (veja acima).

Então, execute a suite de testes do Symfony2 no diretório raiz com o seguinte
comando:

.. code-block:: bash

    $ phpunit

A saida deve mostrar `OK`. Se não mostrar, você deve descobrir o que está errado
e se os testes estão quebrados pelas suas modificações.

.. tip::
   Execute a suite de testes antes de aplicar suas modificações para se certificar
   que eles ocorrem sem problemas na sua configuração.

Cobertura do Código
-------------------

Se você adicionar um novo recurso, você também precisa se certificar da cobertura
do código, usando a opção `coverage-html`:

.. code-block:: bash

    $ phpunit --coverage-html=cov/

Verifique a cobertura do código abrindo a página gerada `cov/index.html` em um 
navegador.

.. tip::
   A cobertura do código só funciona se você tiver o XDebug habilitado e todas 
   as dependências instaladas.
