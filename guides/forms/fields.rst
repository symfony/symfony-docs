Form Fields
===========

A form consists of one or more form fields. Each field is an object whose
class implements :class:`Symfony\\Component\\Form\\FormFieldInterface`.
Fields convert data between normalized and human representations.

Let's look at the ``DateField`` for example. While your application stores
dates as strings or ``DateTime`` objects, users prefer to choose a date in
drop downs. ``DateField`` handles the rendering and type conversion for you.

Core Field Options
------------------

All built-in fields accept an array of options in their constructor. For 
convenience, these core fields are subclasses of 
:class:`Symfony\\Component\\Form\\Field` which predefines a couple of options.

``data``
~~~~~~~~

When you create a form, each field initially displays the value of the
corresponding property of the form's domain object. If you want to override
this initial value, you can set it in the ``data`` option.

.. code-block:: php

    use Symfony\Component\Form\HiddenField
    
    $field = new HiddenField('token', array(
        'data' => 'abcdef',
    ));
    
    assert('abcdef' === $field->getData());

.. note::

    When you set the ``data`` option, the field will also not write the the
    domain object, because the ``property_path`` option will implicitely be
    ``null``. Read :ref:`form-field-property_path` for more information.

``required``
~~~~~~~~~~~~

By default, each ``Field`` assumes that its value is required, so no empty
value should be submited. This setting affects the behaviour and rendering of 
some fields. The ``ChoiceField``, for example, includes an empty choice if
it is not required.

.. code-block:: php

    use Symfony\Component\Form\ChoiceField
    
    $field = new ChoiceField('status', array(
        'choices' => array('tbd' => 'To be done', 'rdy' => 'Ready'),
        'required' => false,
    ));

``disabled``
~~~~~~~~~~~~

If you don't want a user to modify the value of a field, you can set the
``disabled`` option to ``true``. Any submitted value will be ignored.

.. code-block:: php

    use Symfony\Component\Form\TextField
    
    $field = new TextField('status', array(
        'data' => 'Old data',
        'disabled' => true,
    ));
    $field->submit('New data');
    
    assert('Old data' === $field->getData());

``trim``
~~~~~~~~

Many users accidentally type leading or trailing spaces into input fields.
The form framework automatically removes these spaces. If you want to keep them,
set the ``trim`` option to ``false``.

.. code-block:: php

    use Symfony\Component\Form\TextField
    
    $field = new TextField('status', array(
        'trim' => false,
    ));
    $field->submit('   Data   ');
    
    assert('   Data   ' === $field->getData());

.. _form-field-property_path:

``property_path``
~~~~~~~~~~~~~~~~~

Fields display a property value of the form's domain object by default. When
the form is submitted, the submitted value is written back into the object.

If you want to override the property that a field reads from and writes to,
you can set the ``property_path`` option. Its default value is the field's
name.

.. code-block:: php

    use Symfony\Component\Form\Form
    use Symfony\Component\Form\TextField
    
    $author = new Author();
    $author->setFirstName('Your name...');
    
    $form = new Form('author');
    $form->add(new TextField('name', array(
        'property_path' => 'firstName',
    )));
    $form->bind($request, $author);
    
    assert('Your name...' === $form['name']->getData());

For a property path, the class of the domain object needs to have

1. A matching public property, or
2. A public setter and getter with the prefix "set"/"get", followed by the
   property path.

Property paths can also refer to nested objects by using dots.

.. code-block:: php

    use Symfony\Component\Form\Form
    use Symfony\Component\Form\TextField
    
    $author = new Author();
    $author->getEmail()->setAddress('me@example.com');
    
    $form = new Form('author');
    $form->add(new EmailField('email', array(
        'property_path' => 'email.address',
    )));
    $form->bind($request, $author);
    
    assert('me@example.com' === $form['email']->getData());

You can refer to entries of nested arrays or objects implementing 
``\Traversable`` using squared brackets.

.. code-block:: php

    use Symfony\Component\Form\Form
    use Symfony\Component\Form\TextField
    
    $author = new Author();
    $author->setEmails(array(0 => 'me@example.com'));
    
    $form = new Form('author');
    $form->add(new EmailField('email', array(
        'property_path' => 'emails[0]',
    )));
    $form->bind($request, $author);
    
    assert('me@example.com' === $form['email']->getData());

If the property path is ``null``, the field will neither read from nor write
to the domain object. This is useful if you want to have fields with fixed 
values.

.. code-block:: php

    use Symfony\Component\Form\HiddenField
    
    $field = new HiddenField('token', array(
        'data' => 'abcdef',
        'property_path' => null,
    ));
    
Because this is such a common scenario, ``property_path`` is always ``null``
if you set the ``data`` option. So the last code example can be simplified to:

.. code-block:: php

    use Symfony\Component\Form\HiddenField
    
    $field = new HiddenField('token', array(
        'data' => 'abcdef',
    ));

.. note::

    If you want to set a custom default value but still write to the domain 
    object, you need to pass ``property_path`` manually.

    .. code-block:: php

        use Symfony\Component\Form\TextField
        
        $field = new TextField('name', array(
            'data' => 'Custom default...',
            'property_path' => 'token',
        ));
    
    Usually this is not necessary, because you should rather the default value
    of the ``token`` property in your domain object.

Built-in Fields
---------------

Symfony2 ships with the following fields:

.. toctree::
    :hidden:

    fields/index

* :doc:`BirthdayField <fields/BirthdayField>`
* :doc:`CheckboxField <fields/CheckboxField>`
* :doc:`ChoiceField <fields/ChoiceField>`
* :doc:`CollectionField <fields/CollectionField>`
* :doc:`CountryField <fields/CountryField>`
* :doc:`DateField <fields/DateField>`
* :doc:`DateTimeField <fields/DateTimeField>`
* :doc:`EntityChoiceField <fields/EntityChoiceField>`
* :doc:`FileField <fields/FileField>`
* :doc:`HiddenField <fields/HiddenField>`
* :doc:`IntegerField <fields/IntegerField>`
* :doc:`LanguageField <fields/LanguageField>`
* :doc:`LocaleField <fields/LocaleField>`
* :doc:`MoneyField <fields/MoneyField>`
* :doc:`NumberField <fields/NumberField>`
* :doc:`PasswordField <fields/PasswordField>`
* :doc:`PercentField <fields/PercentField>`
* :doc:`RepeatedField <fields/RepeatedField>`
* :doc:`TextareaField <fields/TextareaField>`
* :doc:`TextField <fields/TextField>`
* :doc:`TimeField <fields/TimeField>`
* :doc:`TimezoneField <fields/TimezoneField>`
* :doc:`UrlField <fields/UrlField>`
