label
~~~~~

**type**: ``string`` **default**: The label is "guessed" from the field name

Sets the label that will be used when rendering the field. Setting to false
will suppress the label. The label can also be directly set inside the template:

.. configuration-block::

    .. code-block:: twig

        {{ form_label(form.name, 'Your name') }}

    .. code-block:: php

        echo $view['form']->label(
            $form['name'],
            'Your name'
        );
