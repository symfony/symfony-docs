.. index::
   single: Internals

Visão Interna
==============

Parece que vocẽ quer entender como o Symfony2 funciona e como extende-lo.
Isso me deixa muito feliz! Essa seção é uma explicação em profundidade da
parte interna do Symfony2.

.. note::

    Você só deve precisar ser essa seção se você que entender como o 
    Symfony2 funciona por trás dos panos, ou se você quer extende-lo.

O código Symfony2 é feito de várias camadas inpendentes. Cada camada é feita
sob a camada anterior.

.. tip::

    O carregamento automático não é controlado diretamente pelo framework;
    isso é feito independentemente com a ajuda da classe 
    :class:`Symfony\\Component\\HttpFoundation\\UniversalClassLoader` 
    e o arquivo ``src/autoload.php``. Leia o :doc:`capitulo dedicado
    </guides/tools/autoloader>` para mais informações.

Componente ``HttpFoundation``
-----------------------------

O nível mais baixo é o component :`Symfony\\Component\\HttpFoundation`.
HttpFoundation prove os objetos principais necessários para lidar com HTTP.
É uma abstração Orientada a Objetos de algumas funções nativas do PHP e 
variáveis:

* A classe :class:`Symfony\\Component\\HttpFoundation\\Request`  abstrai
  as principais variáveis globais do PHP como ``$_GET``, ``$_POST``, ``$_COOKIE``,
  ``$_FILES``, e ``$_SERVER``;

* A classe :class:`Symfony\\Component\\HttpFoundation\\Response` abstrai algumas
  funções do PHP como ``header()``, ``setcookie()``, e ``echo``;

* A classe :class:`Symfony\\Component\\HttpFoundation\\Session` e a interface
  :class:`Symfony\\Component\\HttpFoundation\\SessionStorage\\SessionStorageInterface`
  abstraem as funções de controle de sessão ``session_*()``.

.. seealso::

    Leia mais sobre o component :doc:`HttpFoundation <http_foundation>`.

Componente ``HttpKernel``
-------------------------

No topo do componente HttpFoundation está o :namespace:`Symfony\\Component\\HttpKernel`.
HttpKernel manipula a parte dinamica do HTTP; ele é um fino empacotador no topo das
classes de Request e Response para padronizar a maneira que as requisições são
manipuladas. Ele também provê pontos de extensão e ferramentas que fazem dele o 
ponto de inicio ideal para criar um framework web sem muito custo.

Ele também adiciona opcionalmente a habilidade de configuração e extensão, graças ao
componente de Dependency Injection e um poderoso sistema de plugins (bundles).

.. seealso::

    Leia mais sobre o componente :doc:`HttpKernel <kernel>`. Leia mais sobre
    :doc:`Dependency Injection </guides/dependency_injection/index>` e
    :doc:`Bundles </guides/bundles/index>`.

Bundle ``FrameworkBundle``
--------------------------

O bundle :namespace:`Symfony\\Bundle\\FrameworkBundle` é o que amarra os principais 
componentes e bibliotecas juntos, para fazer um framework MVC leve e rápido.
Ele vem com uma leve configuração padrão e algumas convenções para facilitar a curva
de aprendizado.
