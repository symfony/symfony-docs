Adding an "empty value"
-----------------------

If you're using the non-expanded version of the type (i.e. a ``select`` tag)
element and you'd like to have a blank entry (e.g. "Choose an option") at
the top of the select box, you can easily do so by doing the following:

* Set the ``multiple`` option to false;

* Set the ``required`` option to false or explicitly pass in the ``empty_value``
  option in the template (as shown below).

If ``required`` is true, a blank choice will display at the top of the select
box. To customize what that entry says (or if you have ``required`` set to
``false``), add the following when rendering the field:

.. configuration-block::

    .. code-block:: jinja

        {{ form_widget(form.foo_choices, { 'empty_value': 'Choose an option' }) }}

    .. code-block:: php
    
        <?php echo $view['form']->widget($form['foo_choices'], array('empty_value' => 'Choose an option')) ?>
