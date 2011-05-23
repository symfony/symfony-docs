* ``data`` [type: any, default: the field's initial value]
  When you create a form, each field initially displays the value of the 
  corresponding property of the form's domain object. If you want to override 
  this initial value, you can set it in the data option.
  
  .. code-block:: php

     use Symfony\Component\Form\HiddenField

     $field = new HiddenField('token', array(
              'data' => 'abcdef',
              ));
     assert('abcdef' === $field->getData());


