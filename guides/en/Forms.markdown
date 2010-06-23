Forms
=====

In the last part you learnt a lot about data validation. Data validation is
hardly useful though if no-one provides you with data. Symfony 2.0 features
a sophisticated Form component that allows you to easily create mighty HTML forms.

Creating Forms
--------------

A form in Symfony 2.0 is a view on your objects. Imagine that you give the 
visitor of your website a domain object. He then sets the values in the object 
and hands it back to you. You can use the Validator to validate the object,
and if the validation fails, you give the object back to the customer and the
cycle continues.

Let's see how this works in a practical example. Let's create a simple
`Customer` class that can be found in many web applications.

    [php]
    class Customer
    {
      public $firstName;
      public $lastName;
      public $age = 20;
      public $email;
      public $gender;
      
      public static function getGenders()
      {
        return array('male', 'female');
      }
    }
    
For the sake of simplicity in this tutorial, all the fields in this class
are public. In your application you would probably make them protected and
create setters and getters to read or manipulate them.

Now let's create a form to let the visitor fill the data of the object.

    [php]
    # src/Application/HelloBundle/Controller/HelloController.php
    public function signupAction()
    {
      $customer = new Customer();
      
      $form = new Form('customer', $customer, $this->container->getValidatorService());
      $form->add(new TextField('firstName'));
      $form->add(new TextField('lastName'));
      $form->add(new IntegerField('age'));
      $form->add(new TextField('email'));
      $form->add(new ChoiceField('gender', array(
        'choices' => array_combine(User::getGenders(), User::getGenders()),
      )));
 
      return $this->render('HelloBundle:Hello:signup', array('form' => $form));
    }
    
The form consists of a number of fields. Each field needs a name, which is also
the name of the property that is modified in the underlying object. Now let's 
create a simple template to render the form.

    [php]
    # src/Application/HelloBundle/Resources/views/Hello/signup.php
    <?php $view->extend('HelloBundle::layout') ?>

    <?php echo $form->renderFormTag() ?>
      <?php echo $form->renderErrors() ?>
      <?php echo $form->render() ?>
      <input type="submit" value="Send!" />
    </form>
    
That's all to display a fully functional form! But what if the user submits the 
form? We still need a few more lines of code to handle that.

    [php]
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
    
All the form data is submitted in a POST parameter with the name of the form.
This parameter needs to be bound to the form. Symfony then automatically fills
your object with the data and launches the validation. We're finished!

Form Fields
-----------

A form usually consists of one or more form fields. In Symfony 2.0, form
fields are objects that render HTML tags and know how to translate the values
submitted in these tags to some PHP data type.

Let's look at the `DateField` for example. While you probably prefer to store 
dates as strings or `DateTime` objects, visitors usually like to enter them
in text input fields or by choosing from a list of drop downs. `DateField` 
handles the rendering and type conversion for you.

### Basic Fields

Symfony 2.0 ships with the following fields:

<table>
<tr><th>Field Name</th><th>Description</th></tr>
<tr>
  <td>TextField</td>
  <td>An input tag for entering text</td>
</tr>
<tr>
  <td>TextareaField</td>
  <td>A textarea tag for entering more text</td>
</tr>
<tr>
  <td>CheckboxField</td>
  <td>A checkbox input tag</td>
</tr>
<tr>
  <td>ChoiceField</td>
  <td>A drop-down, radio-button or checkbox list for selecting one or more values</td>
</tr>
<tr>
  <td>PasswordField</td>
  <td>A password input tag</td>
</tr>
<tr>
  <td>HiddenField</td>
  <td>A hidden input tag</td>
</tr>
</table>

### Localized Fields

The Form component also features fields that render differently depending on
the locale of the user. 

<table>
<tr><th>Field Name</th><th>Description</th></tr>
<tr>
  <td>NumberField</td>
  <td>A localized text input tag for entering numbers</td>
</tr>
<tr>
  <td>IntegerField</td>
  <td>A localized text input tag for entering integers</td>
</tr>
<tr>
  <td>PercentField</td>
  <td>A localized text input tag for entering percent values</td>
</tr>
<tr>
  <td>MoneyField</td>
  <td>A localized text input tag for entering money values</td>
</tr>
<tr>
  <td>DateField</td>
  <td>A localized text input tag or a list of select tags for entering dates</td>
</tr>
<tr>
  <td>BirthdayField</td>
  <td>An extension of `DateField` for selecting birthdays</td>
</tr>
<tr>
  <td>TimeField</td>
  <td>A localized text input tag or a list of select tags for entering dates</td>
</tr>
<tr>
  <td>DateTimeField</td>
  <td>A combination of `DateField` and `TimeField`</td>
</tr>
<tr>
  <td>TimezoneField</td>
  <td>An extension of `ChoiceField` for selecting a timezone</td>
</tr>
</table>

### Repeated Fields

The special `RepeatedField` allows you to output another field twice.
The field will only validate if the user enters the same value in both fields.

    [php]
    $form->add(new RepeatedField(new TextField('email')));
    
### Collection Fields

The `CollectionField` is a special field for manipulating arrays. Let's extend
the `Customer` class to store up to three email addresses.

    [php]
    class Customer
    {
      // other properties ...
      
      public $emails = array('', '', '');
    }
    
Now let's add a `CollectionField` to manipulate the addresses.

    [php]
    $form->add(new CollectionField(new TextField('emails')));

### Field Groups

Field groups allow you to combine multiple fields together. While normal fields
only allow you to edit scalar data types, field groups can be used to edit
related records of an object. Let's add a new class `Address` to our model.

    [php]
    class Address
    {
      public $street;
      public $zipCode;
    }
    
Let's also add a new property `$address` to the customer.

    [php]
    class Customer
    {
       // other properties ...
       
       public $address;
    }
    
Now the last missing step is to adapt the controller.

    [php]
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
    
That's all that is required to edit related objects in your form! Since
the `Form` class is an extension of `FieldGroup`, you can also add forms to
other forms, or edit one-to-many relations by combining `CollectionField` and
`FieldGroup`. The architecture of the Form component was designed to give you
the greates possible flexibility!

Form Validation
---------------

You have already learnt in the last part of this tutorial how to set up
validation constraints for a PHP class. The nice thing is that this is enough 
to validate a Form! Remember that a form is nothing more than a gateway for
changing data in an object.

What now if there are further validation constraints for a specific form, that
are irrelevant for the underlying class? What if the form contains fields that
should not be written into the object?

The answer to that question is most of the time to extend your domain model.
We'll demonstrate this approach by extending our form with a checkbox for
accepting terms and conditions.

Let's create a simple `Registration` class for this purpose.

    [php]
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
    
Now we can easily adapt the form in the controller:

    [php]
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
    
The big benefit of this refactoring is that we can reuse the `Registration`
class. Extending the application to allow users to sign up via XML is no 
problem at all!

Customizing the View
--------------------

Unfortunately the output of `$form->render()` doesn't look too great. Symfony
2.0 makes it very easy though to customize the HTML of a form. You can access
every field and field group in the form by its name. All fields offer the
method `render()` for rendering the widget and `renderErrors()` for rendering
a `<ul>`-list with the field errors.

The following example shows you how to refine the HTML of an individual form
field.

    [php]
    # src/Application/HelloBundle/Resources/views/Hello/signup.php
    <div class="form-row">
      <label for="<?php echo $form['firstName']->getId() ?>">First name:</label>
      <div class="form-row-content">
        <?php echo $form['firstName']->renderErrors() ?>
        <?php echo $form['firstName']->render() ?>
      </div>
    </div>
    
You can access fields in field groups in the same way.

    [php]
    <?php echo $form['address']['street']->render() ?>
    
Forms and field groups can be iterated for conveniently rendering all fields
in the same way. You only need to take care not to create form rows or labels
for your hidden fields.

    [php]
    <?php foreach ($form as $field): ?>
      <?php if ($field->isHidden()): ?>
        <?php echo $field->render() ?>
      <?php else: ?>
        <div class="form-row">
          ...
        </div>
      <?php endif ?>
    <?php endforeach ?>
    
By using plain HTML, you have the greatest possible flexibility in designing
your forms. Especially your designers will be happy that they can manipulate
the form output without having to deal with (much) PHP!

Final Thoughts
--------------

This chapter showed you how the Form component of Symfony 2.0 can help you to
rapidly create forms for your domain objects. The component embraces a strict
separation between business logic and presentation. Many fields are
automatically localized to make your visitors feel comfortable on your website.
And with the new architecture, this is just the beginning of many new, mighty
user-created fields!
