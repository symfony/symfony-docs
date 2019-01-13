help_attr
~~~~~~~~~

**type**: ``array`` **default**: ``[]``

Sets the HTML attributes for the element used to display the help message of the
form field. Its value is an associative array with HTML attribute names as keys.
These attributes can also be set in the template:

.. code-block:: twig

    {{ form_help(form.name, 'Your name', {
        'help_attr': {'class': 'CUSTOM_LABEL_CLASS'}
    }) }}
