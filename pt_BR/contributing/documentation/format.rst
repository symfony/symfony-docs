Formato da Documentação
=======================

A documentação do Symfony2 usa a `reStructuredText`_ como sua linguagem de marcação
e o `Sphinx`_ para gerar a saida (HTML, PDF, ...).

reStructuredText
----------------

reStructuredText "é uma sitaxe de marcação e um sistema analisador fácil de ler e
o texto é o-que-você-vê-é-o-que-você-tem."

Você pode aprender mais sobre a sua sintaxe lendo a `documentação`_ existente do Symfony2
ou lendo o `reStructuredText Primer`_ no site do Sphinx.

Se você conhece Markdown, tenha cuidado, pois as vezes as coisas similares são muito 
diferentes:

* Listas começam no inicio de uma linha (não é permitido identação);

* Blocos de código na linha usam crases duplas (````assim````).

Sphinx
------

Sphinx é um sistema que adiciona ferramentas muito legas para criar documentação
para documentos em reStructuredText. Assim, ele adiciona novas diretrizes e 
interpreta textos para a `marcação`_ reST.

Destaque de Sintaxe
~~~~~~~~~~~~~~~~~~~

Todos os exemplos de código usam o PHP como a linguagem padrão de destaque. Você
pode trocar isso com a diretriz de ``code-block``:

.. code-block:: rst

    .. code-block:: yaml

        { foo: bar, bar: { foo: bar, bar: baz } }

Se o seu código PGP iniciar com ``<?php``, então você precisará usar ``html+php`` 
como a pseudo-linguagem de destaque:

.. code-block:: rst

    .. code-block:: html+php

        <?php echo $this->foobar(); ?>

.. note::
   Uma lista de linguagens suportadas encontra-se disponível no `Pygments website`_.

Configuração de Blocos
~~~~~~~~~~~~~~~~~~~~~~

Sempre que você mostrar uma configuração, você precisa usar a diretiva ``configuration-block``
para mostrar essa configuração em todos os formatos de configuração disponíveis
(``PHP``, ``YAML``, and ``XML``):

.. code-block:: rst

    .. configuration-block::

        .. code-block:: yaml

            # Configuration in YAML

        .. code-block:: xml

            <!-- Configuration in XML //-->

        .. code-block:: php

            // Configuration in XML

O pedaço anterior de reST renderiza o seguinte:

.. configuration-block::

    .. code-block:: yaml

        # Configuração em YAML

    .. code-block:: xml

        <!-- Configuração em XML //-->

    .. code-block:: php

        // Configuração em XML

.. _reStructuredText:        http://docutils.sf.net/rst.html
.. _Sphinx:                  http://sphinx.pocoo.org/
.. _documentação:            http://github.com/symfony/symfony-docs
.. _reStructuredText Primer: http://sphinx.pocoo.org/rest.html
.. _marcação:                http://sphinx.pocoo.org/markup/
.. _Pygments website:        http://pygments.org/languages/
