Enviando um patch
=================

Patches são a melhor maneira de ajudar a corrigir um bug ou propor uma melhoria
no Symfony.

Configuração Inicial
--------------------

Antes de trabalhar no Symfony2, configure um ambiente amigável com os seguintes
programas:

* Git;

* PHP versão 5.3.2 ou superior;

* PHPUnit 3.5.0 ou superior.

Configure suas informações de usuário com seu nome real e e-mail:

.. code-block:: bash

    $ git config --global user.name "Seu nome"
    $ git config --global user.email voce@example.com

.. tip::
   Se você é novato usando o Git, nós recomendados que você leia o excente livro
   gratuito `ProGit`.
   

Baixe o código fonte do Symfony2:

* Crie uma conta no `Github`_ e logue-se;

* Fork o `repositório Symfony2`_ (clique no botão "Fork");

* Após a "ação hardcore forking" completar, clone seu fork localmente
  (isso irá criar um diretório `symfony`):

.. code-block:: bash

      $ git clone git@github.com:USERNAME/symfony.git

* Adicione o repositório  upstream como um remote:

.. code-block:: bash

      $ cd symfony
      $ git remote add upstream git://github.com/fabpot/symfony.git

Agora que o Symfony2 esta instalado, verifique se os testes unitários passam
para o seu ambiente, como explicado no documento dedicado aos :doc:`testes <tests>`.

Trabalhando em um Patch
-----------------------

Cada vez que você quiser trabalhar em um patch para um problema ou uma melhoria, 
cria um branch dedicado:

.. code-block:: bash

    $ git checkout -b BRANCH_NAME

.. tip::
   Use um nome descritivo para seu branch (`ticket_XXX` onde ``XXX` é o numero do 
   ticket é uma boa convenção para correcação de problemas).

O comando acima automaticamente troca o código para o branch reçem criado
(verifique o branch que você esta trabalhando com `git branch`.)

Trabalhe o quanto quiser no código e commit quanto você quiser, mas lembre-se
do seguinte:

* Siga os :doc:`padrões de codígo <standards>`(use `git diff --check` para 
  verificar os espaços finais); 

* Adicione testes unitários para provar que o problema esta corrigido ou que
  a nova funcionalidade realmente funciona;

* Faça commits atomicos e separados logicamente ( use o poder do `git rebase`
  para ter um histórico limpo e lógico);

* Escreve boas mensagens de commit.

.. tip::
   Uma boa mensagem de commit é composta de um resumo(primeira linha), seguida
   opcionalmente de uma linha em branco e umma descrição mais detalhada.
   O resumo deve iniciar com o componente que você esta trabalhando dentro de 
   colchetes (`[DependencyInjection]`, `[FoundationBundle]`, ...). Use um verbo
   (`fixed ...`, `added ...`, ...) para iniciar o resumo e não adicione ponto
   no final.

Enviando um Patch
------------------

Antes de enviar seu patch, atualize seu branch(necessario caso voce
demorou um tempo para finalizar suas alterações):

.. code-block:: bash

    $ git checkout master
    $ git fetch upstream
    $ git merge upstream/master
    $ git checkout BRANCH_NAME
    $ git rebase master

When doing the `rebase` command, you might have to fix merge conflicts. `git
st` gives you the *unmerged* files. Resolve all conflicts, then continue the
rebase:

.. code-block:: bash

    $ git add ... # add resolved files
    $ git rebase --continue

Verifique se todos os testes continuam passando e `push` seu branch remotamente:

.. code-block:: bash

    $ git push origin BRANCH_NAME

You can now advertise your patch on the `dev mailing-list`_. The email must
follow the following conventions:

* O assunto deve iniciar com `[PATCH]`, seguido de um resumo sobre o patch
  (com uma referência para o ticket, caso seja uma correção de problema - `#XXX`);

* O corpo deve conter o link para seu branch;

* O corpo deve então descrever o que o patch faz (informe um ticket ou cole
  a mensagem de commit).

De acordo com os comentários, talvez seja necessário refazer seu patch. Antes de
reenviar o patch, rebase seu master, não faça merge, então force o `push` para o
`origin`:

.. code-block:: bash

    $ git push -f origin BRANCH_NAME

.. _ProGit: http://progit.org/
.. _Github: https://github.com/signup/free
.. _Symfony2 repositório: http://www.github.com/fabpot/symfony
.. _dev mailing-list: http://groups.google.com/group/symfony-devs
