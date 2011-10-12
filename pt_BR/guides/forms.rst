.. index::
   single: Forms

Formulários
===========

O Symfony2 possui um componente de Formulário sofisticado que lhe permite criar 
facilmente formulários HTML poderosos. 

Seu primeiro formulário
-----------------------

Um formulário no Symfony2 é uma camada transparente no topo do seu modelo de domínio. 
Ele lê as propriedades de um objeto, exibe os valores no formulário e permite ao 
usuário alterá-los. Quando o formulário é submetido, os valores são escritos 
de volta no objeto.

Vamos ver como isso funciona em um exemplo prático. Vamos criar uma classe
simples ``Customer``:

    class Customer
    {
      public $name;
      
      private $age = 20;
      
      public function getAge() {
          return $this->age;
      }
      
      public function setAge($age) {
          $this->age = $age;
      }
    }

A classe contém duas propriedades: ``name`` e "age". A propriedade ``$name`` é pública,
enquanto a ``$age`` só pode ser modificada através de getters e setters.

Agora vamos criar um formulário para deixar o visitante preencher os dados do objeto::

    // src/Application/HelloBundle/Controller/HelloController.php
    public function signupAction()
    {
      $customer = new Customer();
      
      $form = new Form('customer', $customer, $this->container->getValidatorService());
      $form->add(new TextField('name'));
      $form->add(new IntegerField('age'));
 
      return $this->render('HelloBundle:Hello:signup', array('form' => $form));
    }

Um formulário é composto de vários campos. Cada campo representa uma propriedade
na sua classe. A propriedade deve ter o mesmo nome do campo e deve ser pública 
ou acessível através de getters e setters públicos. Agora vamos criar um 
template simples para renderizar o formulário:

.. code-block:: html+php

    # src/Application/HelloBundle/Resources/views/Hello/signup.php
    <?php $view->extend('HelloBundle::layout') ?>

    <?php echo $form->renderFormTag('#') ?>
      <?php echo $form->renderErrors() ?>
      <?php echo $form->render() ?>
      <input type="submit" value="Send!" />
    </form>

Quando o usuário submete o formulário, também precisamos lidar com os dados submetidos.
Todos os dados são armazenados em um parâmetro POST com o nome do formulário:

    # src/Application/HelloBundle/Controller/HelloController.php
    public function signupAction()
    {
      $customer = new Customer();
      $form = new Form('customer', $customer, $this->container->getValidatorService());
      
      // form setup...
      
      if ($this->getRequest()->getMethod() == 'POST')
      {
        $form->bind($this->getRequest()->getParameter('customer'));
        
        if ($form->isValid())
        {
          // save $customer object and redirect
        }
      }
 
      return $this->render('HelloBundle:Hello:signup', array('form' => $form));
    }

Parabéns! Você acabou de criar seu primeiro formulário totalmente funcional
com o Symfony2.

.. index::
   single: Forms; Fields


Campos do Formulário
--------------------

Como você aprendeu, um formulário consiste de um ou mais campos. No Symfony2, 
os campos do formulário têm duas responsabilidades:

* Renderizar HTML
* Converter dados entre as representações normalizada e humana

Vamos olhar o ``DateField``, por exemplo. Enquanto você provavelmente preferirá guardar 
datas como strings ou objetos ``DateTime``, os usuários preferem selecioná-las de  
listas drop down. O DateField manipula o processamento e conversão de tipo para você.

Campos Básicos
~~~~~~~~~~~~~~

O Symfony2 possui todos os campos que estão disponíveis em HTML simples:

============= ==================
Campo         Nome Descrição
============= ==================
TextField     Uma tag input para a entrada de texto curto
TextareaField Uma tag textarea para inserir texto longo
CheckboxField Um checkbox 
ChoiceField   Um drop-down ou múltiplos radio-buttons/checkboxes para a seleção de valores
PasswordField Uma tag input para senha
HiddenField   Uma tag input do tipo oculto
============= ==================

Campos localizados
~~~~~~~~~~~~~~~~~~

O componente de Formulário também dispõe de campos que são processados de 
forma diferente dependendo da localidade do usuário:

============= ==================
Campo         Nome Descrição
============= ==================
NumberField   Um campo texto para inserir números
IntegerField  Um campo texto para inserir números inteiros
PercentField  Um campo texto para inserir valores percentuais
MoneyField    Um campo texto para inserir valores monetários
DateField     Um campo texto ou vários drop-downs para inserir datas
BirthdayField Uma extensão do DateField para selecionar aniversários
TimeField     Um campo texto ou vários drop-downs para inserir um horário
DateTimeField Uma combinação de DateField e TimeField
TimezoneField Uma extensão do ChoiceField para selecionar um fuso horário
============= ==================

Grupos de Campos
~~~~~~~~~~~~~~~~

Os grupos de campos permitem que você combine vários campos em conjunto. 
Enquanto os campos normais só permitem editar tipos de dados escalares, os 
grupos de campos podem ser usados para editar objetos inteiros ou arrays. 
Vamos adicionar uma nova classe ``Address`` ao nosso modelo::

    class Address
    {
      public $street;
      public $zipCode;
    }

Agora podemos adicionar a propriedade ``$address`` para o customer que armazena um objeto 
``Address``::

    class Customer
    {
       // other properties ...
       
       public $address;
    }

Podemos usar um grupo de campos para mostrar os campos para o customer e o ``address``
aninhado ao mesmo tempo::

    # src/Application/HelloBundle/Controller/HelloController.php
    public function signupAction()
    {
      $customer = new Customer();
      $customer->address = new Address();
      
      // form configuration ...
      
      $group = new FieldGroup('address');
      $group->add(new TextField('street'));
      $group->add(new TextField('zipCode'));
      $form->add($group);
      
      // process form ...
    }

Com apenas essas pequenas mudanças, agora você pode editar também o objeto ``Address``! 
Legal, né?
    
Campos Repetidos
~~~~~~~~~~~~~~~~

O ``RepeatedField`` é um grupo de campos estendido que permite a saída de um campo duas 
vezes. O campo repetido somente será validado se o usuário digitar o mesmo valor 
em ambos os campos::

    $form->add(new RepeatedField(new TextField('email')));

Este é um campo muito útil para consultar endereços de e-mail ou senhas!

Coleção de Campos
~~~~~~~~~~~~~~~~~

O ``CollectionField`` é um grupo de campos especial para manipular arrays ou 
objetos que implementam a interface ``Traversable``. Para demonstrar isso, 
vamos estender a classe ``Customer`` para armazenar três endereços de e-mail::

    class Customer
    {
      // other properties ...
      
      public $emails = array('', '', '');
    }

Vamos agora adicionar um `CollectionField`` para manipular esses endereços::

    $form->add(new CollectionField(new TextField('emails')));

Se você definir a opção "modifiable" como ``true``, você ainda pode adicionar ou 
remover linhas na coleção via Javascript! O ``CollectionField`` notará e 
redimensionará o array subjacente de acordo.

.. index::
   single: Forms; Validation

Validação de Formulário
-----------------------

Você já aprendeu na última parte deste tutorial como configurar as constraints
de validação para uma classe PHP. A parte boa é que isso é suficiente para 
validar um formulário! Lembre-se que um formulário nada mais é do que um 
gateway para alterar os dados em um objeto.

Mas, e se houverem constraints de validação adicionais para um formulário 
específico, que são irrelevantes para a classe básica? E se o formulário contém 
campos que não devem ser escritos dentro do objeto?

A resposta a essa pergunta é, na maioria do vezes, estender o seu modelo de 
domínio. Vamos demonstrar essa abordagem estendendo o nosso formulário com 
um checkbox para aceitar os termos e condições.

Vamos criar uma classe simples ``Registration`` para essa finalidade::

    class Registration
    {
      /** @Validation({ @Valid }) */
      public $customer;
      
      /** @Validation({ @AssertTrue(message="Please accept the terms and conditions") }) */
      public $termsAccepted = false;
      
      public process()
      {
        // save user, send emails etc.
      }
    }

Agora podemos facilmente adaptar o formulário no controlador::

    # src/Application/HelloBundle/Controller/HelloController.php
    public function signupAction()
    {
      $registration = new Registration();
      $registration->customer = new Customer();
      
      $form = new Form('registration', $registration, $this->container->getValidatorService());
      $form->add(new CheckboxField('termsAccepted'));
      
      $group = new FieldGroup('customer');
      
      // add customer fields to this group ...
      
      $form->add($group);
      
      if ($this->getRequest()->getMethod() == 'POST')
      {
        $form->bind($this->getRequest()->getParameter('customer'));
        
        if ($form->isValid())
        {
          $registration->process();
        }
      }
 
      return $this->render('HelloBundle:Hello:signup', array('form' => $form));
    }

O grande benefício desta refatoração é que podemos reutilizar a classe ``Registration``. 
Estender a aplicação para permitir aos usuários se inscreverem via XML não é 
nenhum problema!    

.. index::
   single: Forms; View

Personalizando a Visão
----------------------

Infelizmente, a saída do ``$form->render()`` não tem uma aparência muito boa. 
O Symfony 2.0 torna muito fácil a personalização do HTML de um formulário. Você 
pode acessar cada campo e um grupo de campos no formulário por seus nomes. Todos 
os campos oferecem o método ``render()`` para processar o widget e ``renderErrors()`` 
para processar uma lista ``<ul>`` com os erros dos campos.

O exemplo a seguir mostra como aperfeiçoar o HTML para um campo individual do 
formulário::

    # src/Application/HelloBundle/Resources/views/Hello/signup.php
    <div class="form-row">
      <label for="<?php echo $form['firstName']->getId() ?>">First name:</label>
      <div class="form-row-content">
        <?php echo $form['firstName']->renderErrors() ?>
        <?php echo $form['firstName']->render() ?>
      </div>
    </div>

Você pode acessar os campos em grupos de campos da mesma forma:

.. code-block:: html+php

    <?php echo $form['address']['street']->render() ?>

Pode-se realizar iteração nos Formulários e grupos de campos para processar 
convenientemente todos os campos da mesma forma. Você só precisa tomar cuidado 
para não criar linhas de formulário ou labels para os campos ocultos:

.. code-block:: html+php

    <?php foreach ($form as $field): ?>
      <?php if ($field->isHidden()): ?>
        <?php echo $field->render() ?>
      <?php else: ?>
        <div class="form-row">
          ...
        </div>
      <?php endif ?>
    <?php endforeach ?>

Usando HTML simples, você tem maior flexibilidade possível no projeto de seus 
formulários. Especialmente seus designers ficarão felizes que podem manipular 
a saída do formulário sem ter que lidar com (muito) PHP!

Pensamentos Finais
------------------

Este capítulo mostrou como o componente de Formulário do Symfony2 pode ajudá-lo 
a criar rapidamente formulários para os seus objetos de domínio. O componente 
adota uma separação estrita entre a lógica de negócios e a apresentação. 
Muitos campos são automaticamente localizados para fazer com que seus visitantes 
sintam-se confortáveis em seu site. E com a nova arquitetura, este é apenas o 
começo de muitos novos e poderosos campos criados pelo usuário!