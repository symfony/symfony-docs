label
~~~~~

**type**: ``string`` **default**: The label is "guessed" from the field name

Sets the label that will be used when rendering the field. The label can
also be directly set inside the template:
    
.. code-block:: jinja

    {{ render_label(form.name, 'Your name') }}