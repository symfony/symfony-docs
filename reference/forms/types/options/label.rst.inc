* ``label`` [type: string]
    Sets the label that will be used when rendering the field. If blank,
    the label will be auto-generated based on the name of the field. The label
    can also be directly set inside the template:
    
    .. code-block:: jinja
    
        {{ render_label(form.name, 'Name') }}