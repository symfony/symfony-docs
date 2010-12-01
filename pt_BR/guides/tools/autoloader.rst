.. index::
   pair: Autoloader; Configuration

Autoloader
==========

Sempre que você usa uma classe indefinida, o PHP usa o mecanismo de autoloading para
delegar o carregamento de um arquivo que defina a classe. O Symfony2 fornece um 
autoloader "universal", que é capaz de carregar as classes de arquivos que implementem
uma das seguintes convenções:

* As `normas`_ técnicas de interoperabilidade para os namespaces e classes do PHP 5.3
  names;

* A nomeação conforme a convenção do `PEAR`_ para classes.

Se suas classes e as bibliotecas de terceiros que você usa no seu projeto seguem 
essas normas, o autoloader do Symfony2 será o único autoloader que você irá 
precisar

Uso
---

Registrar o autoloader é simples::

    require_once '/path/to/src/Symfony/Foundation/UniversalClassLoader.php';

    use Symfony\Foundation\UniversalClassLoader;

    $loader = new UniversalClassLoader();
    $loader->register();

O autoloader é útil apenas se você que adicionar algumas bibliotecas para carregarem
automaticamente.

.. note::
   O autoloader é automaticamente registrado em uma aplicação do Symfony2 (veja
   ``src/autoload.php``).

Se as classes a serem carregadas automaticamente usam namespaces, use o método
``registerNamespace()`` ou ``registerNamespaces()`` ::

    $loader->registerNamespace('Symfony', __DIR__.'/vendor/symfony/src');

    $loader->registerNamespaces(array(
      'Symfony' => __DIR__.'/vendor/symfony/src',
      'Zend'    => __DIR__.'/vendor/zend/library',
    ));

Para classes que seguem a nomeclatura padrão do PEAR, use o método ``registerPrefix``
ou ``registerPrefixes`` ::

    $loader->registerPrefix('Twig_', __DIR__.'/vendor/twig/lib');

    $loader->registerPrefixes(array(
      'Swift_' => __DIR__.'/vendor/swiftmailer/lib/classes',
      'Twig_'  => __DIR__.'/vendor/twig/lib',
    ));

.. note::
   Algumas bibliotecas também precisam que o caminho da raiz seja registrado no include path do
   PHP (``set_include_path()``).

Classes de um sub-namespace ou de uma sub-hierarquia de classes PEAR poden ser procuradas
em uma lista de locais para facilitar o agrupamento de um sub-conjunto de classes para 
grandes projetos::

    $loader->registerNamespaces(array(
      'Doctrine\Common'          => __DIR__.'/vendor/doctrine/lib/vendor/doctrine-common/lib',
      'Doctrine\DBAL\Migrations' => __DIR__.'/vendor/doctrine-migrations/lib',
      'Doctrine\DBAL'            => __DIR__.'/vendor/doctrine/lib/vendor/doctrine-dbal/lib',
      'Doctrine'                 => __DIR__.'/vendor/doctrine/lib',
    ));

Neste exemplo, se você tentar usar a classe no namespace ``Doctrine\Common``
ou em um dos seus filhos, o autoloader vai procurar primeiro pela classe no
diretório ``doctrine-common``, e vai procurar no diretório padrão
``Doctrine`` (o último configurado) se não encontrar, antes de desistir.
A ordem dos registros é importante neste caso. 

.. _normas: http://groups.google.com/group/php-standards/web/psr-0-final-proposal
.. _PEAR:      http://pear.php.net/manual/en/standards.php
