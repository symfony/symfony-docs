.. index::
   single: Forms; Validators
   single: Validators

Validador
=========

O B�sico
--------

O novo componente validador � baseado na `Especifica��o de Valida��o JSR303 Bean`_. 
O qu�? A especifica��o do Java no PHP? Voc� ouviu direito, 
mas n�o � t�o ruim quanto parece. Vamos ver como us�-la em PHP.

O Validador � projetado para validar objetos comparando � diferentes constraints.
Estas constraints podem ser colocadas na pr�pria classe, em propriedades e nos 
m�todos prefixados com "get" ou "is". Vejamos um exemplo de configura��o::

    class Author
    {
      /**
       * @Validation({
       *   @NotBlank,
       *   @MinLength(4)
       * })
       */
      public $firstName;
      
      /**
       * @Validation({
       *   @Email(message="Ok, s�rio agora. Seu endere�o de e-mail, por favor")
       * })
       */
      public function getEmail()
      {
        return 'foobar';
      }
    }

Este trecho de c�digo mostra uma classe ``Author`` muito simples com uma propriedade 
e um getter. Cada constraint tem um nome, e a maioria deles tamb�m tem algumas 
op��es. Aqui n�s configuramos as constraints com anota��es, mas o Symfony2 tamb�m 
oferece muitos outros drivers de configura��o.

Devido ao driver de anota��o depender da biblioteca Doctrine, ele n�o � habilitado
por padr�o. Voc� pode habilit�-lo em seu ``config.yml``:

.. code-block:: yaml

    # hello/config/config.yml
    web.validation:
      annotations: true

Agora vamos validar um objeto::

    $validator = $this->container->getValidatorService();
    
    $author = new Author();
    $author->firstName = 'B.';
    
    print $validator->validate($author);
    
Voc� dever� ver o seguinte resultado::

    Author.firstName:
      This value is too short. It should have 4 characters or more
    Author.email:
      Ok, s�rio agora. Seu endere�o de email, por favor

O m�todo ``validate()`` retorna um objeto ``ConstraintViolationList`` que pode 
simplesmente ser impresso ou processado em seu c�digo. Essa foi f�cil!

.. index::
   single: Validators; Constraints

As Constraints
--------------

O Symfony possui muitas constraints diferentes. A lista a seguir ir� mostrar-lhe
quais est�o dispon�veis e como voc� pode usar e configur�-las. Algumas 
constraints t�m uma op��o padr�o. Se voc� apenas definir essa op��o, 
voc� pode omitir o seu nome::

    /** @Validation({ @Min(limit=3) }) */

� id�ntico �::

    /** @Validation({ @Min(3) }) */

AssertFalse
~~~~~~~~~~~

Valida se um valor � ``false``. Muito �til para testar os valores de retorno dos 
m�todos::

    /** @Validation({ @AssertFalse }) */
    public function isInjured();

Op��es:

* message: A mensagem de erro se a valida��o falhar

AssertTrue
~~~~~~~~~~

Funciona como o ``AssertFalse``.

NotBlank
~~~~~~~~

Valida se um valor n�o est� vazio::

    /** @Validation({ @NotBlank }) */
    private $firstName;

Op��es:

* message: A mensagem de erro se a valida��o falhar

Blank
~~~~~

Funciona como o ``NotBlank``.

NotNull
~~~~~~~

Valida se um valor n�o � ``NULL``::

    /** @Validation({ @NotNull }) */
    private $firstName;

Null
~~~~

Funciona como o ``NotNull``.

AssertType
~~~~~~~~~~

Valida se um valor tem um tipo de dados espec�fico::

    /** @Validation({ @AssertType("integer") }) */
    private $age;

Op��es:

* type (default): O tipo

Choice
~~~~~~

Valida se um valor � um ou mais de uma lista de op��es::

    /** @Validation({ @Choice({"male", "female"}) }) */
    private $gender;

Op��es:

* choices (default): As op��es dispon�veis
* callback: Pode ser usado em vez do ``choices``. Um m�todo callback est�tico
  retornando as escolhas. Se voc� definir como uma string, o m�todo deve estar
  na classe validada.
* multiple: Se s�o permitidas escolhas m�ltiplas. Default: ``false``
* min: A quantidade m�nima de op��es selecionadas
* max: A quantidade m�xima de op��es selecionadas
* message: A mensagem de erro se a valida��o falhar
* minMessage: A mensagem de erro se a valida��o ``min`` falhar 
* maxMessage: A mensagem de erro se a valida��o ``max`` falhar 

Valid
~~~~~

Valida se um objeto � v�lido. Pode ser colocado em propriedades ou getters para 
validar objetos relacionados::

    /** @Validation({ @Valid }) */
    private $address;

Op��es:

* class: A classe esperada do objeto (opcional)
* message: A mensagem de erro se a classe n�o corresponde

Collection
~~~~~~~~~~

Valida entradas de array comparando a diferentes constraints::

    /**
     * @Validation({ @Collection(
     *   fields = {
     *     "firstName" = @NotNull,
     *     "lastName" = { @NotBlank, @MinLength(4) }
     *   },
     *   allowMissingFields = true
     * )})
     */
    private $options = array();
    
Op��es:

* fields (default): Um array associativo de chaves de array e uma ou mais constraints
* allowMissingFields: Se alguma das chaves n�o est�o presentes no array. Default: ``false``
* allowExtraFields: Se o array pode conter chaves n�o presentes na op��o 
  ``fields``. Default: ``false``
* missingFieldsMessage: A mensagem de erro se a valida��o ``allowMissingFields``
  falhar 
* allowExtraFields: A mensagem de erro se a valida��o ``allowExtraFields`` falhar 

Date
~~~~

Valida se um valor � uma string de data v�lida com o formato ``YYYY-MM-DD``::

    /** @Validation({ @Date }) */
    private $birthday;

Op��es:

* message: A mensagem de erro se a valida��o falhar

DateTime
~~~~~~~~

Valida se um valor � uma string datetime v�lida com o formato ``YYYY-MM-DD
HH:MM:SS``::

    /** @Validation({ @DateTime }) */
    private $createdAt;

Op��es:

* message: A mensagem de erro se a valida��o falhar

Time
~~~~

Valida se um valor � uma string de tempo v�lida com o formato ``HH:MM:SS``::

    /** @Validation({ @Time }) */
    private $start;

Op��es:

* message: A mensagem de erro se a valida��o falhar

Email
~~~~~

Valida se um valor � um endere�o de e-mail v�lido::

    /** @Validation({ @Email }) */
    private $email;

Op��es:

* message: A mensagem de erro se a valida��o falhar
* checkMX: Se os registros MX devem ser verificados para o dom�nio. Default: ``false``

File
~~~~

Valida se um valor � um arquivo existente::

    /** @Validation({ @File(maxSize="64k") }) */
    private $filename;

Op��es:

* maxSize: O tamanho m�ximo permitido. Pode ser fornecido em bytes, kilobytes (com o sufixo "k") ou megabytes (com o sufixo "M")
* mimeTypes: Um ou mais tipos mime permitidos
* notFoundMessage: A mensagem de erro se o arquivo n�o foi encontrado
* notReadableMessage: A mensagem de erro se o arquivo n�o p�de ser lido
* maxSizeMessage: A mensagem de erro se a valida��o ``maxSize`` falhar 
* mimeTypesMessage: A mensagem de erro se a valida��o ``mimeTypes`` falhar 

Max
~~~

Valida se um valor est� no m�ximo do limite estabelecido::

    /** @Validation({ @Max(99) }) */
    private $age;

Op��es:

* limit (default): O limite
* message: A mensagem de erro se a valida��o falhar

Min
~~~

Funciona como o ``Max``.

MaxLength
~~~~~~~~~

Valida se o comprimento da string do valor est� no m�ximo do limite estabelecido::

    /** @Validation({ @MaxLength(32) }) */
    private $hash;

Op��es:

* limit (default): O tamanho do limite
* message: A mensagem de erro se a valida��o falhar

MinLength
~~~~~~~~~

Funciona como o ``MaxLength``.

Regex
~~~~~

Valida se um valor corresponde � express�o regular fornecida::

    /** @Validation({ @Regex("/\w+/") }) */
    private $title;

Op��es:

* pattern (default): O padr�o da express�o regular
* match: Se o padr�o deve ou n�o corresponder.
  Default: ``true``
* message: A mensagem de erro se a valida��o falhar

Url
~~~

Valida se um valor � uma URL v�lida::

    /** @Validation({ @Url }) */
    private $website;

Op��es:

* protocols: A lista de protocolos permitidos. Default: "http", "https", "ftp"
  e "ftps".
* message: A mensagem de erro se a valida��o falhar

.. index::
   single: Validators; Configuration

Outros drivers de configura��o
------------------------------

Como sempre no Symfony, existem v�rias formas de configurar as constraints para 
as suas classes. O Symfony suporta os quatro seguintes drivers.

Configura��o XML
~~~~~~~~~~~~~~~~

O driver XML � um pouco prolixo, mas tem a vantagem de que o arquivo XML pode
ser validado para evitar erros. Para usar o driver, basta colocar um arquivo 
chamado ``validation.xml`` no diret�rio ``Resources/config/`` do seu pacote (bundle):

.. code-block:: xml

    <?xml version="1.0" ?>
    <constraint-mapping xmlns="http://www.symfony-project.org/schema/dic/constraint-mapping"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.symfony-project.org/schema/dic/constraint-mapping 
            http://www.symfony-project.org/schema/dic/services/constraint-mapping-1.0.xsd">

      <class name="Application\HelloBundle\Model\Author">
        <property name="firstName">
          <constraint name="NotBlank" />
          <constraint name="MinLength">4</constraint>
        </property>
        <getter property="email">
          <constraint name="Email">
            <option name="message">Ok, seriously now. Your email address please</option>
          </constraint>
        </getter>
      </class>
    </constraint-mapping>

Configura��o YAML
~~~~~~~~~~~~~~~~~

O driver YAML oferece a mesma funcionalidade do driver XML. Para us�-lo, 
coloque o arquivo ``validation.yml`` no diret�rio``Resources/config/`` do seu 
pacote (bundle):

.. code-block:: yaml

    Application\HelloBundle\Model\Author:
      properties:
        firstName:
          - NotBlank: ~
          - MinLength: 4
          
      getters:
        email:
          - Email: { message: "Ok, s�rio agora. Seu endere�o de e-mail, por favor" }

Configura��o PHP
~~~~~~~~~~~~~~~~

Se voc� preferir gravar as configura��es em velho plain PHP, voc� pode adicionar 
o m�todo est�tico ``loadValidatorMetadata()`` para as classes que voc� deseja validar:

    use Symfony\Components\Validator\Constraints;
    use Symfony\Components\Validator\Mapping\ClassMetadata;

    class Author
    {
      public static function loadValidatorMetadata(ClassMetadata $metadata)
      {
        $metadata->addPropertyConstraint('firstName', new Constraints\NotBlank());
        $metadata->addPropertyConstraint('firstName', new Constraints\MinLength(3));
        $metadata->addGetterConstraint('email', new Constraints\Email(array(
          'message' => 'Ok, seriously now. Your email address please',
        )));
      }
    }

Voc� pode usar qualquer um dos drivers de configura��o ou todos juntos. 
O symfony ir� fazer o merge de todas as informa��es que puder encontrar.

.. _Especifica��o de Valida��o JSR303 Bean: http://jcp.org/en/jsr/detail?id=303
