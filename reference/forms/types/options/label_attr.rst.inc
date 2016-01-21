label_attr
~~~~~~~~~~

**type**: ``array`` **default**: ``array()``

Sets the HTML attributes for the ``<label>`` element, which will be used
when rendering the label for the field. It's an associative array with HTML
attribute as a key. This attributes can also be directly set inside the
template:

.. configuration-block::

    .. code-block:: twig

        {{ form_label(form.name, 'Your name', {
               'label_attr': {'class': 'CUSTOM_LABEL_CLASS'}
        }) }}

    .. code-block:: php

        echo $view['form']->label(
            $form['name'],
            'Your name',
            array('label_attr' => array('class' => 'CUSTOM_LABEL_CLASS'))
        );
