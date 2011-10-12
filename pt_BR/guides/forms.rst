.. index::
   single: Forms

Formul�rios
===========

O Symfony2 possui um componente de Formul�rio sofisticado que lhe permite criar 
facilmente formul�rios HTML poderosos. 

Seu primeiro formul�rio
-----------------------

Um formul�rio no Symfony2 � uma camada transparente no topo do seu modelo de dom�nio. 
Ele l� as propriedades de um objeto, exibe os valores no formul�rio e permite ao 
usu�rio alter�-los. Quando o formul�rio � submetido, os valores s�o escritos 
de volta no objeto.

Vamos ver como isso funciona em um exemplo pr�tico. Vamos criar uma classe
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

A classe cont�m duas propriedades: ``name`` e "age". A propriedade ``$name`` � p�blica,
enquanto a ``$age`` s� pode ser modificada atrav�s de getters e setters.

Agora vamos criar um formul�rio para deixar o visitante preencher os dados do objeto::

    // src/Application/HelloBundle/Controller/HelloController.php
    public function signupAction()
    {
      $customer = new Customer();
      
      $form = new Form('customer', $customer, $this->container->getValidatorService());
      $form->add(new TextField('name'));
      $form->add(new IntegerField('age'));
 
      return $this->render('HelloBundle:Hello:signup', array('form' => $form));
    }

Um formul�rio � composto de v�rios campos. Cada campo representa uma propriedade
na sua classe. A propriedade deve ter o mesmo nome do campo e deve ser p�blica 
ou acess�vel atrav�s de getters e setters p�blicos. Agora vamos criar um 
template simples para renderizar o formul�rio:

.. code-block:: html+php

    # src/Application/HelloBundle/Resources/views/Hello/signup.php
    <?php $view->extend('HelloBundle::layout') ?>

    <?php echo $form->renderFormTag('#') ?>
      <?php echo $form->renderErrors() ?>
      <?php echo $form->render() ?>
      <input type="submit" value="Send!" />
    </form>

Quando o usu�rio submete o formul�rio, tamb�m precisamos lidar com os dados submetidos.
Todos os dados s�o armazenados em um par�metro POST com o nome do formul�rio:

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

Parab�ns! Voc� acabou de criar seu primeiro formul�rio totalmente funcional
com o Symfony2.

.. index::
   single: Forms; Fields


Campos do Formul�rio
--------------------

Como voc� aprendeu, um formul�rio consiste de um ou mais campos. No Symfony2, 
os campos do formul�rio t�m duas responsabilidades:

* Renderizar HTML
* Converter dados entre as representa��es normalizada e humana

Vamos olhar o ``DateField``, por exemplo. Enquanto voc� provavelmente preferir� guardar 
datas como strings ou objetos ``DateTime``, os usu�rios preferem selecion�-las de  
listas drop down. O DateField manipula o processamento e convers�o de tipo para voc�.

Campos B�sicos
~~~~~~~~~~~~~~

O Symfony2 possui todos os campos que est�o dispon�veis em HTML simples:

============= ==================
Campo         Nome Descri��o
============= ==================
TextField     Uma tag input para a entrada de texto curto
TextareaField Uma tag textarea para inserir texto longo
CheckboxField Um checkbox 
ChoiceField   Um drop-down ou m�ltiplos radio-buttons/checkboxes para a sele��o de valores
PasswordField Uma tag input para senha
HiddenField   Uma tag input do tipo oculto
============= ==================

Campos localizados
~~~~~~~~~~~~~~~~~~

O componente de Formul�rio tamb�m disp�e de campos que s�o processados de 
forma diferente dependendo da localidade do usu�rio:

============= ==================
Campo         Nome Descri��o
============= ==================
NumberField   Um campo texto para inserir n�meros
IntegerField  Um campo texto para inserir n�meros inteiros
PercentField  Um campo texto para inserir valores percentuais
MoneyField    Um campo texto para inserir valores monet�rios
DateField     Um campo texto ou v�rios drop-downs para inserir datas
BirthdayField Uma extens�o do DateField para selecionar anivers�rios
TimeField     Um campo texto ou v�rios drop-downs para inserir um hor�rio
DateTimeField Uma combina��o de DateField e TimeField
TimezoneField Uma extens�o do ChoiceField para selecionar um fuso hor�rio
============= ==================

Grupos de Campos
~~~~~~~~~~~~~~~~

Os grupos de campos permitem que voc� combine v�rios campos em conjunto. 
Enquanto os campos normais s� permitem editar tipos de dados escalares, os 
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

Com apenas essas pequenas mudan�as, agora voc� pode editar tamb�m o objeto ``Address``! 
Legal, n�?
    
Campos Repetidos
~~~~~~~~~~~~~~~~

O ``RepeatedField`` � um grupo de campos estendido que permite a sa�da de um campo duas 
vezes. O campo repetido somente ser� validado se o usu�rio digitar o mesmo valor 
em ambos os campos::

    $form->add(new RepeatedField(new TextField('email')));

Este � um campo muito �til para consultar endere�os de e-mail ou senhas!

Cole��o de Campos
~~~~~~~~~~~~~~~~~

O ``CollectionField`` � um grupo de campos especial para manipular arrays ou 
objetos que implementam a interface ``Traversable``. Para demonstrar isso, 
vamos estender a classe ``Customer`` para armazenar tr�s endere�os de e-mail::

    class Customer
    {
      // other properties ...
      
      public $emails = array('', '', '');
    }

Vamos agora adicionar um `CollectionField`` para manipular esses endere�os::

    $form->add(new CollectionField(new TextField('emails')));

Se voc� definir a op��o "modifiable" como ``true``, voc� ainda pode adicionar ou 
remover linhas na cole��o via Javascript! O ``CollectionField`` notar� e 
redimensionar� o array subjacente de acordo.

.. index::
   single: Forms; Validation

Valida��o de Formul�rio
-----------------------

Voc� j� aprendeu na �ltima parte deste tutorial como configurar as constraints
de valida��o para uma classe PHP. A parte boa � que isso � suficiente para 
validar um formul�rio! Lembre-se que um formul�rio nada mais � do que um 
gateway para alterar os dados em um objeto.

Mas, e se houverem constraints de valida��o adicionais para um formul�rio 
espec�fico, que s�o irrelevantes para a classe b�sica? E se o formul�rio cont�m 
campos que n�o devem ser escritos dentro do objeto?

A resposta a essa pergunta �, na maioria do vezes, estender o seu modelo de 
dom�nio. Vamos demonstrar essa abordagem estendendo o nosso formul�rio com 
um checkbox para aceitar os termos e condi��es.

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

Agora podemos facilmente adaptar o formul�rio no controlador::

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

O grande benef�cio desta refatora��o � que podemos reutilizar a classe ``Registration``. 
Estender a aplica��o para permitir aos usu�rios se inscreverem via XML n�o � 
nenhum problema!    

.. index::
   single: Forms; View

Personalizando a Vis�o
----------------------

Infelizmente, a sa�da do ``$form->render()`` n�o tem uma apar�ncia muito boa. 
O Symfony 2.0 torna muito f�cil a personaliza��o do HTML de um formul�rio. Voc� 
pode acessar cada campo e um grupo de campos no formul�rio por seus nomes. Todos 
os campos oferecem o m�todo ``render()`` para processar o widget e ``renderErrors()`` 
para processar uma lista ``<ul>`` com os erros dos campos.

O exemplo a seguir mostra como aperfei�oar o HTML para um campo individual do 
formul�rio::

    # src/Application/HelloBundle/Resources/views/Hello/signup.php
    <div class="form-row">
      <label for="<?php echo $form['firstName']->getId() ?>">First name:</label>
      <div class="form-row-content">
        <?php echo $form['firstName']->renderErrors() ?>
        <?php echo $form['firstName']->render() ?>
      </div>
    </div>

Voc� pode acessar os campos em grupos de campos da mesma forma:

.. code-block:: html+php

    <?php echo $form['address']['street']->render() ?>

Pode-se realizar itera��o nos Formul�rios e grupos de campos para processar 
convenientemente todos os campos da mesma forma. Voc� s� precisa tomar cuidado 
para n�o criar linhas de formul�rio ou labels para os campos ocultos:

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

Usando HTML simples, voc� tem maior flexibilidade poss�vel no projeto de seus 
formul�rios. Especialmente seus designers ficar�o felizes que podem manipular 
a sa�da do formul�rio sem ter que lidar com (muito) PHP!

Pensamentos Finais
------------------

Este cap�tulo mostrou como o componente de Formul�rio do Symfony2 pode ajud�-lo 
a criar rapidamente formul�rios para os seus objetos de dom�nio. O componente 
adota uma separa��o estrita entre a l�gica de neg�cios e a apresenta��o. 
Muitos campos s�o automaticamente localizados para fazer com que seus visitantes 
sintam-se confort�veis em seu site. E com a nova arquitetura, este � apenas o 
come�o de muitos novos e poderosos campos criados pelo usu�rio!