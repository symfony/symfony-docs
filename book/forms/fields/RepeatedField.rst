RepeatedField
=============

The ``RepeatedField`` is an extended field group that allows you to output a
field twice. The repeated field will only validate if the user enters the same
value in both fields::

    use Symfony\Component\Form\RepeatedField;

    $form->add(new RepeatedField(new TextField('email')));

This is a very useful field for querying email addresses or passwords!