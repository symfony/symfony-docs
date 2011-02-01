Form Fields
===========

As you have learned, a form consists of one or more form fields. A field knows
how to convert data between normalized and human representations.

Let's look at the ``DateField`` for example. While you probably prefer to
store dates as strings or ``DateTime`` objects, users rather like to choose
them from a list of drop downs. ``DateField`` handles the rendering and type
conversion for you.

Basic Fields
------------

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
----------------

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
------------

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

    # src/Sensio/HelloBundle/Controller/HelloController.php

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
---------------

The ``RepeatedField`` is an extended field group that allows you to output a
field twice. The repeated field will only validate if the user enters the same
value in both fields::

    use Symfony\Component\Form\RepeatedField;

    $form->add(new RepeatedField(new TextField('email')));

This is a very useful field for querying email addresses or passwords!

Collection Fields
-----------------

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