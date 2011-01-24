.. index::
   single: Forms

Forms
=====

Symfony2 features a sophisticated Form component that allows you to easily
create mighty forms.

Your First Form
---------------

A form in Symfony2 is a transparent layer on top of your domain model. It reads
properties from an object, displays the values in the form, and allows the user
to change them. When the form is submitted, the values are written back into
the object.

Let's see how this works in a practical example. Let's create a simple
``Customer`` class::

    namespace Application\HelloBundle\Entity;

    class Customer
    {
        public $name;
        private $age = 20;

        public function getAge()
        {
            return $this->age;
        }

        public function setAge($age)
        {
            $this->age = $age;
        }
    }

The class contains two properties ``name`` and "age". The property ``$name``
is public, while ``$age`` can only be modified through setters and getters.

Now let's create a form to let the visitor fill the data of the object::

    // src/Application/HelloBundle/Controller/HelloController.php

    use Application\HelloBundle\Entity\Customer;
    use Symfony\Component\Form\Form;
    use Symfony\Component\Form\TextField;
    use Symfony\Component\Form\IntegerField;

    public function signupAction()
    {
        $customer = new Customer();

        $form = new Form('customer', $customer, $this->get('validator'));
        $form->add(new TextField('name'));
        $form->add(new IntegerField('age'));

        return $this->render('HelloBundle:Hello:signup.html.twig', array(
            'form' => $form
        ));
    }

A form consists of various fields. Each field represents a property in your
class. The property must have the same name as the field and must either be
public or accessible through public getters and setters.

Instead of passing the form instance directly to the view, we wrap it with an
object that provides methods that help to render the form with more flexibility
(``$this->get('templating.form')->get($form)``).

Let's create a simple template to render the form:

.. code-block:: html+jinja

    # src/Application/HelloBundle/Resources/views/Hello/signup.html.twig
    {% extends 'HelloBundle::layout.html.twig' %}

    <form action="#" method="post">
        {{ form_field(form) }}

        <input type="submit" value="Send!" />
    </form>

.. note::

    Form rendering in templates is covered in chapter :doc:`Forms in Templates
    </guides/forms/view>`.

When the user submits the form, we also need to handle the submitted data. All
the data is stored in a POST parameter with the name of the form::

    # src/Application/HelloBundle/Controller/HelloController.php
    public function signupAction()
    {
        $customer = new Customer();
        $form = new Form('customer', $customer, $this->get('validator'));

        // form setup...

        if ('POST' === $this->get('request')->getMethod()) {
            $form->bind($this->get('request')->request->get('customer'));

            if ($form->isValid()) {
                // save $customer object and redirect
            }
        }

        return $this->render('HelloBundle:Hello:signup.html.twig', array('form' => $form));
    }

Congratulations! You just created your first fully-functional form with
Symfony2.

.. index::
   single: Forms; Fields

Form Fields
-----------

As you have learned, a form consists of one or more form fields. A field knows
how to convert data between normalized and human representations.

Let's look at the ``DateField`` for example. While you probably prefer to
store dates as strings or ``DateTime`` objects, users rather like to choose
them from a list of drop downs. ``DateField`` handles the rendering and type
conversion for you.

Basic Fields
~~~~~~~~~~~~

Symfony2 ships with all fields available in plain HTML:

============= ==================
Field         Name Description
============= ==================
TextField     An input tag for entering short text
TextareaField A textarea tag for entering long text
CheckboxField A checkbox
ChoiceField   A drop-down or multiple radio-buttons/checkboxes for selecting values
PasswordField A password input tag
HiddenField   A hidden input tag
============= ==================

Localized Fields
~~~~~~~~~~~~~~~~

The Form component also features fields that render differently depending on
the locale of the user:

============= ==================
Field         Name Description
============= ==================
NumberField   A text field for entering numbers
IntegerField  A text field for entering integers
PercentField  A text field for entering percent values
MoneyField    A text field for entering money values
DateField     A text field or multiple drop-downs for entering dates
BirthdayField An extension of DateField for selecting birthdays
TimeField     A text field or multiple drop-downs for entering a time
DateTimeField A combination of DateField and TimeField
TimezoneField An extension of ChoiceField for selecting a timezone
============= ==================

Field Groups
~~~~~~~~~~~~

Field groups allow you to combine multiple fields together. While normal
fields only allow you to edit scalar data types, field groups can be used to
edit whole objects or arrays. Let's add a new class ``Address`` to our model::

    class Address
    {
        public $street;
        public $zipCode;
    }

Now we can add a property ``$address`` to the customer that stores one
``Address`` object::

    class Customer
    {
         // other properties ...

         public $address;
    }

We can use a field group to show fields for the customer and the nested
address at the same time::

    # src/Application/HelloBundle/Controller/HelloController.php

    use Symfony\Component\Form\FieldGroup;

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

With only these little changes you can now edit also the ``Address`` object!
Cool, ey?

Repeated Fields
~~~~~~~~~~~~~~~

The ``RepeatedField`` is an extended field group that allows you to output a
field twice. The repeated field will only validate if the user enters the same
value in both fields::

    use Symfony\Component\Form\RepeatedField;

    $form->add(new RepeatedField(new TextField('email')));

This is a very useful field for querying email addresses or passwords!

Collection Fields
~~~~~~~~~~~~~~~~~

The ``CollectionField`` is a special field group for manipulating arrays or
objects that implements the interface ``Traversable``. To demonstrate this, we
will extend the ``Customer`` class to store three email addresses::

    class Customer
    {
        // other properties ...

        public $emails = array('', '', '');
    }

We will now add a ``CollectionField`` to manipulate these addresses::

    use Symfony\Component\Form\CollectionField;

    $form->add(new CollectionField(new TextField('emails')));

If you set the option "modifiable" to ``true``, you can even add or remove
rows in the collection via JavaScript! The ``CollectionField`` will notice it
and resize the underlying array accordingly.

.. index::
   pair: Forms; Validation

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

Let's create a simple ``Registration`` class for this purpose::

    namespace Application\HelloBundle\Entity;

    class Registration
    {
        /** @validation:Valid */
        public $customer;

        /** @validation:AssertTrue(message="Please accept the terms and conditions") */
        public $termsAccepted = false;

        public function process()
        {
            // save user, send emails etc.
        }
    }

Now we can easily adapt the form in the controller::

    # src/Application/HelloBundle/Controller/HelloController.php

    use Application\HelloBundle\Entity\Registration;
    use Symfony\Component\Form\CheckboxField;

    public function signupAction()
    {
        $registration = new Registration();
        $registration->customer = new Customer();

        $form = new Form('registration', $registration, $this->get('validator'));
        $form->add(new CheckboxField('termsAccepted'));

        $group = new FieldGroup('customer');

        // add customer fields to this group ...

        $form->add($group);

        if ('POST' === $this->get('request')->getMethod()) {
            $form->bind($this->get('request')->request->get('registration'));

            if ($form->isValid()) {
                $registration->process();
            }
        }

        return $this->render('HelloBundle:Hello:signup.php', array('form' => $form));
    }

The big benefit of this refactoring is that we can reuse the ``Registration``
class. Extending the application to allow users to sign up via XML is no
problem at all!

Final Thoughts
--------------

This chapter showed you how the Form component of Symfony2 can help you to
rapidly create forms for your domain objects. The component embraces a strict
separation between business logic and presentation. Many fields are
automatically localized to make your visitors feel comfortable on your website.
And with a flexible architecture, this is just the beginning of many mighty
user-created fields!
