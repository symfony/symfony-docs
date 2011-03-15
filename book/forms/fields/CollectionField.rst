CollectionField
===============

``CollectionField`` is a special field group for manipulating arrays or objects
that implements the interface ``Traversable``. To demonstrate this, we will
extend the ``Customer`` class to store three email addresses::

    class Customer
    {
        // other properties ...

        public $emails = array('', '', '');
    }

We will now add a ``CollectionField`` to manipulate these addresses::

    use Symfony\Component\Form\CollectionField;

    $form->add(new CollectionField('emails', array(
        'prototype' => new EmailField(),
    )));

If you set the option "modifiable" to ``true``, you can even add or remove
rows in the collection via JavaScript! The ``CollectionField`` will notice it
and resize the underlying array accordingly.