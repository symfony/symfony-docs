.. index::
   single: Finder

O Finder
==========

O Componente Finder ajuda você a encontrar de maneira rápida e fácil diretórrios e arquivos;

Uso
---

A classe ``Finder`` encontra arquivos e/ou diretórios::

    use Symfony\Components\Finder\Finder;

    $finder = new Finder();
    $finder->files()->in(__DIR__);

    foreach ($finder as $file) {
        print $file->getRealpath()."\n";
    }

O ``$file`` é uma instancia de [``SplFileInfo``][1].

O código acima mostra o nome de todos os arquivos no diretório atual recursivamente.
A classe Finder usa um interface fluente, então todos os métodos retorna uma instancia
do Finder.

.. tip::
   Uma instancia do Finder é um [``Iterator``][2] do PHP. Então, ao invés de iterar sobre o
   Finder com um ``foreach``, você também pode converte-lo para um array com o método
   ``iterator_to_array()``, ou pegar o número de itens com ``iterator_count()``.

Critérios
---------

Localização
~~~~~~~~~~~

A localização é o único critério obrigatório. Ela indica para o finder o
diretório a ser usado na busca::

    $finder->in(__DIR__);

Procure em vários lugares encadeando chamadas para ``in()``::

    $finder->files()->in(__DIR__)->in('/elsewhere');

Exclua diretório dos resultados com o método ``exclude()``::

    $finder->in(__DIR__)->exclude('ruby');

Como o Finder usar os iterators do PHP, você pode passar qualquer URL com um 
`protocolo`_ suportado::

    $finder->in('ftp://example.com/pub/');

E isso também funciona com streams definidos pelo usuário::

    use Symfony\Components\Finder\Finder;

    $s3 = new \Zend_Service_Amazon_S3($key, $secret);
    $s3->registerStreamWrapper("s3");

    $finder = new Finder();
    $finder->name('photos*')->size('< 100K')->date('since 1 hour ago');
    foreach ($finder->in('s3://bucket-name') as $file) {
        // do something

        print $file->getFilename()."\n";
    }

.. note::
   Leia a documentação sobre `Streams`_ para aprenser a criar seus próprios streams.

Arquivos e Diretório

Por padrão, o Finder retorna arquivos e diretórios; mas os métodos ``files()`` e ``directories()`` 
controlam isso::

    $finder->files();

    $finder->directories();

Se você quiser seguir os links, use o método ``followLinks()``::

    $finder->files()->followLinks();

Por padrão, o iterator ignora arquivos VCS populares. Isso pode ser alterado com o método
``ignoreVCS()``::

    $finder->ignoreVCS(false);

Ordenação
~~~~~~~~~

Ordenar o resultado por nome ou por tipo (diretórios primeiro, depois arquivos)::

    $finder->sortByName();

    $finder->sortByType();

.. note::
   Note que os métodos ``sort*`` precisam ter todos os elementos para fazer o seu trabalho.
   Em grandes iterators, isso é lento.

Você também pode definir seu próprio algoritimo de ordenação com ``sort()``::

    $sort = function (\SplFileInfo $a, \SplFileInfo $b)
    {
        return strcmp($a->getRealpath(), $b->getRealpath());
    };

    $finder->sort($sort);

Nome de Arquivo
~~~~~~~~~~~~~~~

Restrinja arquivos pelo nome com o método ``name()``::

    $finder->files()->name('*.php');

O método ``name()`` aceita globs, strings, ou regexes::

    $finder->files()->name('/\.php$/');

O método ``notNames()`` exclui arquivos que casem com o padrão::

    $finder->files()->notName('*.rb');

Tamanho de Arquivo
~~~~~~~~~~~~~~~~~~

Restrinja arquivos pelo tamanho usando o método ``size()``::

    $finder->files()->size('< 1.5K');

Restrinja por um intervalo de tamanhos encadeando chamadas::

    $finder->files()->size('>= 1K')->size('<= 2K');

O operador de comparação pode ser qualquer um dos seguintes: ``>``, ``>=``, ``<``, '<=',
'=='.

O valor alvo pode usar medidas em kilobytes (``k``, ``ki``), megabytes (``m``,
``mi``), ou gigabytes (``g``, ``gi``). Esses com um sufixo ``i`` utilizam a versão
 ``2**n`` de acordo com o `padrão IEC`_.

Data do Arquivo
~~~~~~~~~~~~~~~

Restrinja os arquivos pela data da última alteração com o método ``date()``::

    $finder->date('since yesterday');

O operador de comparação pode ser qualquer um dos seguintes: ``>``, ``>=``, ``<``, '<=',
'=='. Você também pode usar ``since`` ou ``after`` como um apelido para ``>``, e ``until`` ou
``before`` como apelido para ``<``.

O valor alvo pode ser qualquer data suportada pela função [``strtotime()``][6].

Profundidade do Diretório
~~~~~~~~~~~~~~~~~~~~~~~~~

Por padrão, o Finder vasculha diretórios recursivamente. Restrinja a profundidade em que 
ele procura com ``depth()``::

    $finder->depth('== 0');
    $finder->depth('< 3');

Filtros Personalizados
~~~~~~~~~~~~~~~~~~~~~~

Para restringir os arquivos encontrados com sua própria estratégia, use ``filter()``::

    $filter = function (\SplFileInfo $file)
    {
      if (strlen($file) > 10)
      {
        return false;
      }
    };

    $finder->files()->filter($filter);

O método ``filter()`` usa uma Closure como um argumento. Para cara arquivo encontrado,
é ela é chamada com o arquivo sendo uma instancia de [``SplFileInfo``][1]. O arquivo é 
excluído dos resultados se a Closure retornar ``false``.

[1]: http://www.php.net/manual/en/class.splfileinfo.php
[2]: http://www.php.net/manual/en/spl.iterators.php
[6]: http://www.php.net/manual/en/datetime.formats.php

.. _protocolo:    http://www.php.net/manual/en/wrappers.php
.. _Streams:      http://www.php.net/streams
.. _padrão IEC:   http://physics.nist.gov/cuu/Units/binary.html
