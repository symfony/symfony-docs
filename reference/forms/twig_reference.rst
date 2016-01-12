.. index::
   single: Forms; Twig form function reference

Twig Template Form Function and Variable Reference
==================================================

When working with forms in a template, there are two powerful things at
your disposal:

* :ref:`Functions <reference-form-twig-functions>` for rendering each part
  of a form;
* :ref:`Variables <twig-reference-form-variables>` for getting *any* information
  about any field.

You'll use functions often to render your fields. Variables, on the other
hand, are less commonly-used, but infinitely powerful since you can access
a fields label, id attribute, errors and anything else about the field.

.. _reference-form-twig-functions:

Form Rendering Functions
------------------------

This reference manual covers all the possible Twig functions available for
rendering forms. There are several different functions available and
each is responsible for rendering a different part of a form (e.g. labels,
errors, widgets, etc).

.. _reference-forms-twig-form:

form(view, variables)
---------------------

Renders the HTML of a complete form.

.. code-block:: twig

    {# render the form and change the submission method #}
    {{ form(form, {'method': 'GET'}) }}

You will mostly use this helper for prototyping or if you use custom form
themes. If you need more flexibility in rendering the form, you should use
the other helpers to render individual parts of the form instead:

.. code-block:: twig

    {{ form_start(form) }}
        {{ form_errors(form) }}

        {{ form_row(form.name) }}
        {{ form_row(form.dueDate) }}

        {{ form_row(form.submit, { 'label': 'Submit me' }) }}
    {{ form_end(form) }}

.. _reference-forms-twig-start:

form_start(view, variables)
---------------------------

Renders the start tag of a form. This helper takes care of printing the
configured method and target action of the form. It will also include the
correct ``enctype`` property if the form contains upload fields.

.. code-block:: twig

    {# render the start tag and change the submission method #}
    {{ form_start(form, {'method': 'GET'}) }}

.. _reference-forms-twig-end:

form_end(view, variables)
-------------------------

Renders the end tag of a form.

.. code-block:: twig

    {{ form_end(form) }}

This helper also outputs ``form_rest()`` unless you set ``render_rest``
to false:

.. code-block:: twig

    {# don't render unrendered fields #}
    {{ form_end(form, {'render_rest': false}) }}

.. _reference-forms-twig-label:

form_label(view, label, variables)
----------------------------------

Renders the label for the given field. You can optionally pass the specific
label you want to display as the second argument.

.. code-block:: twig

    {{ form_label(form.name) }}

    {# The two following syntaxes are equivalent #}
    {{ form_label(form.name, 'Your Name', {'label_attr': {'class': 'foo'}}) }}

    {{ form_label(form.name, null, {
        'label': 'Your name',
        'label_attr': {'class': 'foo'}
    }) }}

See ":ref:`twig-reference-form-variables`" to learn about the ``variables``
argument.

.. _reference-forms-twig-errors:

form_errors(view)
-----------------

Renders any errors for the given field.

.. code-block:: twig

    {{ form_errors(form.name) }}

    {# render any "global" errors #}
    {{ form_errors(form) }}

.. _reference-forms-twig-widget:

form_widget(view, variables)
----------------------------

Renders the HTML widget of a given field. If you apply this to an entire
form or collection of fields, each underlying form row will be rendered.

.. code-block:: twig

    {# render a widget, but add a "foo" class to it #}
    {{ form_widget(form.name, {'attr': {'class': 'foo'}}) }}

The second argument to ``form_widget`` is an array of variables. The most
common variable is ``attr``, which is an array of HTML attributes to apply
to the HTML widget. In some cases, certain types also have other template-related
options that can be passed. These are discussed on a type-by-type basis.
The ``attributes`` are not applied recursively to child fields if you're
rendering many fields at once (e.g. ``form_widget(form)``).

See ":ref:`twig-reference-form-variables`" to learn more about the ``variables``
argument.

.. _reference-forms-twig-row:

form_row(view, variables)
-------------------------

Renders the "row" of a given field, which is the combination of the field's
label, errors and widget.

.. code-block:: twig

    {# render a field row, but display a label with text "foo" #}
    {{ form_row(form.name, {'label': 'foo'}) }}

The second argument to ``form_row`` is an array of variables. The templates
provided in Symfony only allow to override the label as shown in the example
above.

See ":ref:`twig-reference-form-variables`" to learn about the ``variables``
argument.

.. _reference-forms-twig-rest:

form_rest(view, variables)
--------------------------

This renders all fields that have not yet been rendered for the given form.
It's a good idea to always have this somewhere inside your form as it'll
render hidden fields for you and make any fields you forgot to render more
obvious (since it'll render the field for you).

.. code-block:: twig

    {{ form_rest(form) }}

.. _reference-forms-twig-enctype:

form_enctype(view)
------------------

.. note::

    This helper was deprecated in Symfony 2.3 and will be removed in Symfony
    3.0. You should use ``form_start()`` instead.

If the form contains at least one file upload field, this will render the
required ``enctype="multipart/form-data"`` form attribute. It's always a
good idea to include this in your form tag:

.. code-block:: html+twig

    <form action="{{ path('form_submit') }}" method="post" {{ form_enctype(form) }}>

Form Tests Reference
--------------------

Tests can be executed by using the ``is`` operator in Twig to create a
condition. Read `the Twig documentation`_ for more information.

.. _form-twig-selectedchoice:

selectedchoice(selected_value)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This test will check if the current choice is equal to the ``selected_value``
or if the current choice is in the array (when ``selected_value`` is an
array).

.. code-block:: twig

    <option {% if choice is selectedchoice(value) %} selected="selected"{% endif %} ...>

.. _`twig-reference-form-variables`:

More about Form Variables
-------------------------

.. tip::

    For a full list of variables, see: :ref:`reference-form-twig-variables`.

In almost every Twig function above, the final argument is an array of "variables"
that are used when rendering that one part of the form. For example, the
following would render the "widget" for a field and modify its attributes
to include a special class:

.. code-block:: twig

    {# render a widget, but add a "foo" class to it #}
    {{ form_widget(form.name, { 'attr': {'class': 'foo'} }) }}

The purpose of these variables - what they do & where they come from - may
not be immediately clear, but they're incredibly powerful. Whenever you
render any part of a form, the block that renders it makes use of a number
of variables. By default, these blocks live inside `form_div_layout.html.twig`_.

Look at the ``form_label`` as an example:

.. code-block:: twig

    {% block form_label %}
        {% if not compound %}
            {% set label_attr = label_attr|merge({'for': id}) %}
        {% endif %}

        {% if required %}
            {% set label_attr = label_attr|merge({
                'class': (label_attr.class|default('') ~ ' required')|trim
            }) %}
        {% endif %}

        {% if label is empty %}
            {% set label = name|humanize %}
        {% endif %}

        <label
            {% for attrname, attrvalue in label_attr -%}
                {{ attrname }}="{{ attrvalue }}"
            {%- endfor %}
        >
            {{ label|trans({}, translation_domain) }}
        </label>
    {% endblock form_label %}

This block makes use of several variables: ``compound``, ``label_attr``,
``required``, ``label``, ``name`` and ``translation_domain``. These variables
are made available by the form rendering system. But more importantly, these
are the variables that you can override when calling ``form_label`` (since
in this example, you're rendering the label).

The exact variables available to override depends on which part of the form
you're rendering (e.g. label versus widget) and which field you're rendering
(e.g. a ``choice`` widget has an extra ``expanded`` option). If you get
comfortable with looking through `form_div_layout.html.twig`_, you'll always
be able to see what options you have available.

.. tip::

    Behind the scenes, these variables are made available to the ``FormView``
    object of your form when the Form component calls ``buildView`` and
    ``finishView`` on each "node" of your form tree. To see what "view"
    variables a particular field has, find the source code for the form
    field (and its parent fields) and look at the above two functions.

.. note::

    If you're rendering an entire form at once (or an entire embedded form),
    the ``variables`` argument will only be applied to the form itself and
    not its children. In other words, the following will **not** pass a
    "foo" class attribute to all of the child fields in the form:

    .. code-block:: twig

        {# does **not** work - the variables are not recursive #}
        {{ form_widget(form, { 'attr': {'class': 'foo'} }) }}

.. _reference-form-twig-variables:

Form Variables Reference
~~~~~~~~~~~~~~~~~~~~~~~~

The following variables are common to every field type. Certain field types
may have even more variables and some variables here only really apply to
certain types.

Assuming you have a ``form`` variable in your template and you want to
reference the variables on the ``name`` field, accessing the variables is
done by using a public ``vars`` property on the
:class:`Symfony\\Component\\Form\\FormView` object:

.. configuration-block::

    .. code-block:: html+twig

        <label for="{{ form.name.vars.id }}"
            class="{{ form.name.vars.required ? 'required' : '' }}">
            {{ form.name.vars.label }}
        </label>

    .. code-block:: html+php

        <label for="<?php echo $view['form']->get('name')->vars['id'] ?>"
            class="<?php echo $view['form']->get('name')->vars['required'] ? 'required' : '' ?>">
            <?php echo $view['form']->get('name')->vars['label'] ?>
        </label>

.. versionadded:: 2.3
    The ``method`` and ``action`` variables were introduced in Symfony 2.3.

+------------------------+-------------------------------------------------------------------------------------+
| Variable               | Usage                                                                               |
+========================+=====================================================================================+
| ``form``               | The current ``FormView`` instance.                                                  |
+------------------------+-------------------------------------------------------------------------------------+
| ``id``                 | The ``id`` HTML attribute to be rendered.                                           |
+------------------------+-------------------------------------------------------------------------------------+
| ``name``               | The name of the field (e.g. ``title``) - but not the ``name``                       |
|                        | HTML attribute, which is ``full_name``.                                             |
+------------------------+-------------------------------------------------------------------------------------+
| ``full_name``          | The ``name`` HTML attribute to be rendered.                                         |
+------------------------+-------------------------------------------------------------------------------------+
| ``errors``             | An array of any errors attached to *this* specific field                            |
|                        | (e.g. ``form.title.errors``).                                                       |
|                        | Note that you can't use ``form.errors`` to determine if a form is valid,            |
|                        | since this only returns "global" errors: some individual fields may have errors.    |
|                        | Instead, use the ``valid`` option.                                                  |
+------------------------+-------------------------------------------------------------------------------------+
| ``submitted``          | Returns ``true`` or ``false`` depending on whether the whole form is submitted      |
+------------------------+-------------------------------------------------------------------------------------+
| ``valid``              | Returns ``true`` or ``false`` depending on whether the whole form is valid.         |
+------------------------+-------------------------------------------------------------------------------------+
| ``value``              | The value that will be used when rendering (commonly the ``value`` HTML attribute). |
+------------------------+-------------------------------------------------------------------------------------+
| ``disabled``           | If ``true``, ``disabled="disabled"`` is added to the field.                         |
+------------------------+-------------------------------------------------------------------------------------+
| ``required``           | If ``true``, a ``required`` attribute is added to the field to activate HTML5       |
|                        | validation. Additionally, a ``required`` class is added to the label.               |
+------------------------+-------------------------------------------------------------------------------------+
| ``max_length``         | Adds a ``maxlength`` HTML attribute to the element. (deprecated as of 2.5, to be    |
|                        | removed in 3.0, use ``attr["maxlength"]`` instead)                                  |
+------------------------+-------------------------------------------------------------------------------------+
| ``pattern``            | Adds a ``pattern`` HTML attribute to the element. (deprecated as of 2.5, to be      |
|                        | removed in 3.0, use ``attr["pattern"]`` instead)                                    |
+------------------------+-------------------------------------------------------------------------------------+
| ``label``              | The string label that will be rendered.                                             |
+------------------------+-------------------------------------------------------------------------------------+
| ``multipart``          | If ``true``, ``form_enctype`` will render ``enctype="multipart/form-data"``.        |
|                        | This only applies to the root form element.                                         |
+------------------------+-------------------------------------------------------------------------------------+
| ``attr``               | A key-value array that will be rendered as HTML attributes on the field.            |
+------------------------+-------------------------------------------------------------------------------------+
| ``label_attr``         | A key-value array that will be rendered as HTML attributes on the label.            |
+------------------------+-------------------------------------------------------------------------------------+
| ``compound``           | Whether or not a field is actually a holder for a group of children fields          |
|                        | (for example, a ``choice`` field, which is actually a group of checkboxes.          |
+------------------------+-------------------------------------------------------------------------------------+
| ``block_prefixes``     | An array of all the names of the parent types.                                      |
+------------------------+-------------------------------------------------------------------------------------+
| ``translation_domain`` | The domain of the translations for this form.                                       |
+------------------------+-------------------------------------------------------------------------------------+
| ``cache_key``          | A unique key which is used for caching.                                             |
+------------------------+-------------------------------------------------------------------------------------+
| ``data``               | The normalized data of the type.                                                    |
+------------------------+-------------------------------------------------------------------------------------+
| ``method``             | The method of the current form (POST, GET, etc.).                                   |
+------------------------+-------------------------------------------------------------------------------------+
| ``action``             | The action of the current form.                                                     |
+------------------------+-------------------------------------------------------------------------------------+

.. _`form_div_layout.html.twig`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bridge/Twig/Resources/views/Form/form_div_layout.html.twig
.. _`the Twig documentation`: http://twig.sensiolabs.org/doc/templates.html#test-operator
