.. index::
   single: Forms; Validators
   single: Validators

Validador
=========

O Básico
--------

O novo componente validador é baseado na `Especificação de Validação JSR303 Bean`_. 
O quê? A especificação do Java no PHP? Você ouviu direito, 
mas não é tão ruim quanto parece. Vamos ver como usá-la em PHP.

O Validador é projetado para validar objetos comparando à diferentes constraints.
Estas constraints podem ser colocadas na própria classe, em propriedades e nos 
métodos prefixados com "get" ou "is". Vejamos um exemplo de configuração::

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
       *   @Email(message="Ok, sério agora. Seu endereço de e-mail, por favor")
       * })
       */
      public function getEmail()
      {
        return 'foobar';
      }
    }

Este trecho de código mostra uma classe ``Author`` muito simples com uma propriedade 
e um getter. Cada constraint tem um nome, e a maioria deles também tem algumas 
opções. Aqui nós configuramos as constraints com anotações, mas o Symfony2 também 
oferece muitos outros drivers de configuração.

Devido ao driver de anotação depender da biblioteca Doctrine, ele não é habilitado
por padrão. Você pode habilitá-lo em seu ``config.yml``:

.. code-block:: yaml

    # hello/config/config.yml
    web.validation:
      annotations: true

Agora vamos validar um objeto::

    $validator = $this->container->getValidatorService();
    
    $author = new Author();
    $author->firstName = 'B.';
    
    print $validator->validate($author);
    
Você deverá ver o seguinte resultado::

    Author.firstName:
      This value is too short. It should have 4 characters or more
    Author.email:
      Ok, sério agora. Seu endereço de email, por favor

O método ``validate()`` retorna um objeto ``ConstraintViolationList`` que pode 
simplesmente ser impresso ou processado em seu código. Essa foi fácil!

.. index::
   single: Validators; Constraints

As Constraints
--------------

O Symfony possui muitas constraints diferentes. A lista a seguir irá mostrar-lhe
quais estão disponíveis e como você pode usar e configurá-las. Algumas 
constraints têm uma opção padrão. Se você apenas definir essa opção, 
você pode omitir o seu nome::

    /** @Validation({ @Min(limit=3) }) */

é idêntico à::

    /** @Validation({ @Min(3) }) */

AssertFalse
~~~~~~~~~~~

Valida se um valor é ``false``. Muito útil para testar os valores de retorno dos 
métodos::

    /** @Validation({ @AssertFalse }) */
    public function isInjured();

Opções:

* message: A mensagem de erro se a validação falhar

AssertTrue
~~~~~~~~~~

Funciona como o ``AssertFalse``.

NotBlank
~~~~~~~~

Valida se um valor não está vazio::

    /** @Validation({ @NotBlank }) */
    private $firstName;

Opções:

* message: A mensagem de erro se a validação falhar

Blank
~~~~~

Funciona como o ``NotBlank``.

NotNull
~~~~~~~

Valida se um valor não é ``NULL``::

    /** @Validation({ @NotNull }) */
    private $firstName;

Null
~~~~

Funciona como o ``NotNull``.

AssertType
~~~~~~~~~~

Valida se um valor tem um tipo de dados específico::

    /** @Validation({ @AssertType("integer") }) */
    private $age;

Opções:

* type (default): O tipo

Choice
~~~~~~

Valida se um valor é um ou mais de uma lista de opções::

    /** @Validation({ @Choice({"male", "female"}) }) */
    private $gender;

Opções:

* choices (default): As opções disponíveis
* callback: Pode ser usado em vez do ``choices``. Um método callback estático
  retornando as escolhas. Se você definir como uma string, o método deve estar
  na classe validada.
* multiple: Se são permitidas escolhas múltiplas. Default: ``false``
* min: A quantidade mínima de opções selecionadas
* max: A quantidade máxima de opções selecionadas
* message: A mensagem de erro se a validação falhar
* minMessage: A mensagem de erro se a validação ``min`` falhar 
* maxMessage: A mensagem de erro se a validação ``max`` falhar 

Valid
~~~~~

Valida se um objeto é válido. Pode ser colocado em propriedades ou getters para 
validar objetos relacionados::

    /** @Validation({ @Valid }) */
    private $address;

Opções:

* class: A classe esperada do objeto (opcional)
* message: A mensagem de erro se a classe não corresponde

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
    
Opções:

* fields (default): Um array associativo de chaves de array e uma ou mais constraints
* allowMissingFields: Se alguma das chaves não estão presentes no array. Default: ``false``
* allowExtraFields: Se o array pode conter chaves não presentes na opção 
  ``fields``. Default: ``false``
* missingFieldsMessage: A mensagem de erro se a validação ``allowMissingFields``
  falhar 
* allowExtraFields: A mensagem de erro se a validação ``allowExtraFields`` falhar 

Date
~~~~

Valida se um valor é uma string de data válida com o formato ``YYYY-MM-DD``::

    /** @Validation({ @Date }) */
    private $birthday;

Opções:

* message: A mensagem de erro se a validação falhar

DateTime
~~~~~~~~

Valida se um valor é uma string datetime válida com o formato ``YYYY-MM-DD
HH:MM:SS``::

    /** @Validation({ @DateTime }) */
    private $createdAt;

Opções:

* message: A mensagem de erro se a validação falhar

Time
~~~~

Valida se um valor é uma string de tempo válida com o formato ``HH:MM:SS``::

    /** @Validation({ @Time }) */
    private $start;

Opções:

* message: A mensagem de erro se a validação falhar

Email
~~~~~

Valida se um valor é um endereço de e-mail válido::

    /** @Validation({ @Email }) */
    private $email;

Opções:

* message: A mensagem de erro se a validação falhar
* checkMX: Se os registros MX devem ser verificados para o domínio. Default: ``false``

File
~~~~

Valida se um valor é um arquivo existente::

    /** @Validation({ @File(maxSize="64k") }) */
    private $filename;

Opções:

* maxSize: O tamanho máximo permitido. Pode ser fornecido em bytes, kilobytes (com o sufixo "k") ou megabytes (com o sufixo "M")
* mimeTypes: Um ou mais tipos mime permitidos
* notFoundMessage: A mensagem de erro se o arquivo não foi encontrado
* notReadableMessage: A mensagem de erro se o arquivo não pôde ser lido
* maxSizeMessage: A mensagem de erro se a validação ``maxSize`` falhar 
* mimeTypesMessage: A mensagem de erro se a validação ``mimeTypes`` falhar 

Max
~~~

Valida se um valor está no máximo do limite estabelecido::

    /** @Validation({ @Max(99) }) */
    private $age;

Opções:

* limit (default): O limite
* message: A mensagem de erro se a validação falhar

Min
~~~

Funciona como o ``Max``.

MaxLength
~~~~~~~~~

Valida se o comprimento da string do valor está no máximo do limite estabelecido::

    /** @Validation({ @MaxLength(32) }) */
    private $hash;

Opções:

* limit (default): O tamanho do limite
* message: A mensagem de erro se a validação falhar

MinLength
~~~~~~~~~

Funciona como o ``MaxLength``.

Regex
~~~~~

Valida se um valor corresponde à expressão regular fornecida::

    /** @Validation({ @Regex("/\w+/") }) */
    private $title;

Opções:

* pattern (default): O padrão da expressão regular
* match: Se o padrão deve ou não corresponder.
  Default: ``true``
* message: A mensagem de erro se a validação falhar

Url
~~~

Valida se um valor é uma URL válida::

    /** @Validation({ @Url }) */
    private $website;

Opções:

* protocols: A lista de protocolos permitidos. Default: "http", "https", "ftp"
  e "ftps".
* message: A mensagem de erro se a validação falhar

.. index::
   single: Validators; Configuration

Outros drivers de configuração
------------------------------

Como sempre no Symfony, existem várias formas de configurar as constraints para 
as suas classes. O Symfony suporta os quatro seguintes drivers.

Configuração XML
~~~~~~~~~~~~~~~~

O driver XML é um pouco prolixo, mas tem a vantagem de que o arquivo XML pode
ser validado para evitar erros. Para usar o driver, basta colocar um arquivo 
chamado ``validation.xml`` no diretório ``Resources/config/`` do seu pacote (bundle):

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

Configuração YAML
~~~~~~~~~~~~~~~~~

O driver YAML oferece a mesma funcionalidade do driver XML. Para usá-lo, 
coloque o arquivo ``validation.yml`` no diretório``Resources/config/`` do seu 
pacote (bundle):

.. code-block:: yaml

    Application\HelloBundle\Model\Author:
      properties:
        firstName:
          - NotBlank: ~
          - MinLength: 4
          
      getters:
        email:
          - Email: { message: "Ok, sério agora. Seu endereço de e-mail, por favor" }

Configuração PHP
~~~~~~~~~~~~~~~~

Se você preferir gravar as configurações em velho plain PHP, você pode adicionar 
o método estático ``loadValidatorMetadata()`` para as classes que você deseja validar:

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

Você pode usar qualquer um dos drivers de configuração ou todos juntos. 
O symfony irá fazer o merge de todas as informações que puder encontrar.

.. _Especificação de Validação JSR303 Bean: http://jcp.org/en/jsr/detail?id=303
