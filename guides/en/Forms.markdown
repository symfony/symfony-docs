Forms
=====

Symfony2 features a sophisticated Form component that allows you to easily
create mighty HTML forms.

Your First Form
---------------

A form in Symfony2 is a transparent layer on top of your domain model. It
reads properties from an object, displays the values in the form and allows
the user to change them. When the form is submitted, the values are written
back into the object.

Let's see how this works in a practical example. Let's create a simple
`Customer` class.

    [php]
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
    
The class contains two properties `name` and "age". The property `$name` is
public, while `$age` can only be modified through setters and getters. 

Now let's create a form to let the visitor fill the data of the object.

    [php]
    # src/Application/HelloBundle/Controller/HelloController.php
    public function signupAction()
    {
      $customer = new Customer();
      
      $form = new Form('customer', $customer, $this->container->getValidatorService());
      $form->add(new TextField('name'));
      $form->add(new IntegerField('age'));
 
      return $this->render('HelloBundle:Hello:signup', array('form' => $form));
    }
    
A form consists of various fields. Each field represents a property in your
class. The property must have the same name as the field and must either be
public or accessible through public getters and setters. Now let's create a 
simple template to render the form.

    [php]
    # src/Application/HelloBundle/Resources/views/Hello/signup.php
    <?php $view->extend('HelloBundle::layout') ?>

    <?php echo $form->renderFormTag('#') ?>
      <?php echo $form->renderErrors() ?>
      <?php echo $form->render() ?>
      <input type="submit" value="Send!" />
    </form>
    
When the user submits the form, we also need to handle the submitted data.
All the data is stored in a POST parameter with the name of the form.

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
    
Congratulations! You just created your first fully-functional form with
Symfony2.

Form Fields
-----------

As you have learned, a form consists of one or more form fields. In Symfony2, 
form fields have two responsibilities:

  * Render HTML
  * Convert data between normalized and humane representations

Let's look at the `DateField` for example. While you probably prefer to store 
dates as strings or `DateTime` objects, users rather like to choose them from a
list of drop downs. `DateField` handles the rendering and type conversion for you.

### Basic Fields

Symfony2 ships with all fields available in plain HTML.

<table>
<tr><th>Field Name</th><th>Description</th></tr>
<tr>
  <td>TextField</td>
  <td>An input tag for entering short text</td>
</tr>
<tr>
  <td>TextareaField</td>
  <td>A textarea tag for entering long text</td>
</tr>
<tr>
  <td>CheckboxField</td>
  <td>A checkbox</td>
</tr>
<tr>
  <td>ChoiceField</td>
  <td>A drop-down or multiple radio-buttons/checkboxes for selecting values</td>
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
  <td>A text field for entering numbers</td>
</tr>
<tr>
  <td>IntegerField</td>
  <td>A text field for entering integers</td>
</tr>
<tr>
  <td>PercentField</td>
  <td>A text field for entering percent values</td>
</tr>
<tr>
  <td>MoneyField</td>
  <td>A text field for entering money values</td>
</tr>
<tr>
  <td>DateField</td>
  <td>A text field or multiple drop-downs for entering dates</td>
</tr>
<tr>
  <td>BirthdayField</td>
  <td>An extension of DateField for selecting birthdays</td>
</tr>
<tr>
  <td>TimeField</td>
  <td>A text field or multiple drop-downs for entering a time</td>
</tr>
<tr>
  <td>DateTimeField</td>
  <td>A combination of DateField and TimeField</td>
</tr>
<tr>
  <td>TimezoneField</td>
  <td>An extension of ChoiceField for selecting a timezone</td>
</tr>
</table>

### Field Groups

Field groups allow you to combine multiple fields together. While normal fields
only allow you to edit scalar data types, field groups can be used to edit
whole objects or arrays. Let's add a new class `Address` to our model.

    [php]
    class Address
    {
      public $street;
      public $zipCode;
    }
    
Now we can add a property `$address` to the customer that stores one `Address`
object.

    [php]
    class Customer
    {
       // other properties ...
       
       public $address;
    }
    
We can use a field group to show fields for the customer and the nested address
at the same time.

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
    
With only these little changes you can now edit also the `Address` object!
Cool, ey?

### Repeated Fields

The `RepeatedField` is an extended field group that allows you to output a field
twice. The repeated field will only validate if the user enters the same value
in both fields.

    [php]
    $form->add(new RepeatedField(new TextField('email')));
    
This is a very useful field for querying email addresses or passwords!
    
### Collection Fields

The `CollectionField` is a special field group for manipulating arrays or
objects that implement the interface `Traversable`. To demonstrate this, we 
will extend the `Customer` class to store three email addresses.

    [php]
    class Customer
    {
      // other properties ...
      
      public $emails = array('', '', '');
    }
    
We will now add a `CollectionField` to manipulate these addresses.

    [php]
    $form->add(new CollectionField(new TextField('emails')));
    
If you set the option "modifiable" to `true`, you can even add or remove rows
in the collection via Javascript! The `CollectionField` will notice it and
resize the underlying array accordingly.

Form Validation
---------------

You have already learned in the last part of this tutorial how to set up
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

This chapter showed you how the Form component of Symfony2 can help you to
rapidly create forms for your domain objects. The component embraces a strict
separation between business logic and presentation. Many fields are
automatically localized to make your visitors feel comfortable on your website.
And with the new architecture, this is just the beginning of many new, mighty
user-created fields!
