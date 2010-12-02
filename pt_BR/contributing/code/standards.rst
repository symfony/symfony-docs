Padrões de Codificação
======================

Quando você contribuir com código para o Symfony, você deve seguir seus
padrões de codificação. Para encurtar a história, aqui está a regra de ouro:
*Imite a código existente do Symfony*.

Estrutura
---------

* Nunca use short tags (`<?`);

* Não termine o arquivo com uma classe com a usual tag de fechamento (`?>`);

* A identação é feita usando quatro espaços (tabs são proibidas);

* Use o caracter de nova linha (`0x0A`) ao fim de cada linha;

* Adicione um espaço simples após cada virgula delimitadora;

* Não coloque espaços depois da abertura de um parênteses e antes de fechar um;

* Adicione um espaço simples em volta dos operadores (`==`, `&&`, ...);

* Adicione um espaço simples antes da abertura de um parênteses de uma função 
  de controle (`if`, `else`, `for`, `while`, ...);

* Adicione uma linha em branco antes das declarações de `return`;

* Não adicione espaços a direita no fim da linha;

* Use chaves para indicar o corpo de uma estrutura de controle não importando
  o número de declarações que ele contem;

* Coloque as chaves em suas próprias linhas para declarações de classes, métodos 
  e funções;

* Separe a declaração condicional e a chave de abertura com um espaço simples
  e sem linhas em branco;

* Declare explicitamente a visibilidade para classes, métodos e propriedades 
  (uso de `var` é proibido);

* Use constantes tipadas do PHP em minúsculo: `false`, `true`, e `null`. O mesmo
  se aplica para `array()`;

* Use maiúsculo para constantes com palavras separadas por underscore;

* Defina uma classe por arquivo;

* Declare as propriedades da classe antes dos métodos;

* Declare métodos públicos primeiro, depois protegidos e por fim os privados;

Convenção de Nomeação
---------------------

* Use camelCase ao invés de underscores, para nomes de variáveis, funções e métodos;

* Use underscores para nomes de opções, argumentos e parametros;

* Use namespaces para todas as classes;

* Use `Symfony` como o primeiro nível de namespace;

* Use `Interface` como sufixos para interfaces;

* Use caracteres alfanuméricos e undercores para nomes de arquivo;

Documentação
------------

* Adicione blocos de PHPDoc para todas as classes, métodos e funções;

* As anotações `@package` e `@subpackage` não são usadas.
