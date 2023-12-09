``label``
~~~~~~~~~

**type**: ``string`` or ``TranslatableMessage`` **default**: The label is "guessed" from the field name

Sets the label that will be displayed on the button. The label can also
be directly set inside the template:

.. configuration-block::

    .. code-block:: twig

        {{ form_widget(form.save, { 'label': 'Click me' }) }}

    .. code-block:: html+php

        <?= $view['form']->widget($form['save'], ['label' => 'Click me']) ?>
