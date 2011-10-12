Traduções
=========

A documentação do Symfony2 é escrita em inglês e muitas pessoas estão envolvidas
no proceso de tradução.

Contribuindo
------------

Primeiro, se familiarize com a :doc:`linguagem de marcação <format>` usada na 
documentação.

Então, inscreva-se na `lista de discussão do Symfony docs`_, por a colaboração
acontecer lá.

Finalmente encontre o repositório *master* para a linguagem que você quer contribuir.
Aqui está uma lista com os repositórios *master* oficiais:

* *Inglês*: http://github.com/symfony/symfony-docs
* ...

.. note::
   Se você quer contribuir com traduções de uma nova linguagem, leia a
   :ref: `sessão dedicada <translations-adding-a-new-language>`.

Juntando-se ao Time de Tradução
-------------------------------

Se você quer ajudar a traduzir alguns documentos para a sua linguagem ou corrigir
algum erro, considere se juntar a nós; é um processo muito simples:


* Apresente-se na `lista de discussão do Symfony docs`_;
* *(opcional)* Pergunte em quais documentos você pode trabalhar;
* Faça o Fork do repositório *master* da sua linguagem (clique no botão "Fork" na 
  página do Github);
* Traduza alguns documentos;
* Peça um pull request (clique em "Pull Request" na sua página do Github);
* O coordenador do time aceita as suas modificações e mescla elas no repositório
  master;
* O site da documentação é atualizado toda noite posterior a atualização do repositório
  master.

.. _translations-adding-a-new-language:

Adicionando uma nova linguagem
------------------------------

Essa seção apresenta algumas orientações para iniciar a tradução do Symfony2
para uma nova linguagem.

Como iniciar uma tradução é muito trabalhoso, converse sobre seus planos na
`lista de discussão do Symfony docs`_ e tente encontrar algumas pessoas 
motivadas dispostas a ajudar.

Quando o time estiver pronto, nomeie um coordenador. ele vai ser o responsável
pelo repositório *master*

Crie o repositório e copie os documentos em  *Inglês*.

O time pode então iniciar o processo de tradução.

Quando o time estiver confiante que o repositório está consistente e estável 
(tudo está traduzido, ou os documentos não traduzidos foram removidos dos
toctress -- arquivos chamados ``index.rst`` e ``map.rst.inc``), o coordenador
do time pode solicitar que o repositório seja adicionado a listar de repositórios
*master* oficiais, enviando um e-mail para Fabien 
(fabien.potencier at symfony-project.org).

Manutenção
----------

A tradução não termina quando tudo está traduzido. A documentação é um alvo em
movimento (novos documentos são adicionados, erros são corrigido, paragrafos são
reorganizados, ...). O time de tradução precisa seguir de perto o repositório em 
Inglês e aplicar as mudanças nos documentos traduzidos assim que possível.

.. caution::
   Lingagens sem manutenção são removidas da lista oficial de repositórios pois
   documentação obsoleta é perigosa.

.. _lista de discussão do Symfony docs: http://groups.google.com/group/symfony-docs
