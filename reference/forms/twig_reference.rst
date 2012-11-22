.. index::
   single: Forms; Twig form function reference

Twig Template Form Function Reference
=====================================

This reference manual covers all the possible Twig functions available for
rendering forms. There are several different functions available, and each
is responsible for rendering a different part of a form (e.g. labels, errors,
widgets, etc).

.. _reference-forms-twig-label:

form_label(form.name, label, variables)
---------------------------------------

Renders the label for the given field. You can optionally pass the specific
label you want to display as the second argument.

.. code-block:: jinja

    {{ form_label(form.name) }}

    {# The two following syntaxes are equivalent #}
    {{ form_label(form.name, 'Your Name', {'label_attr': {'class': 'foo'}}) }}
    {{ form_label(form.name, null, {'label': 'Your name', 'label_attr': {'class': 'foo'}}) }}

See ":ref:`twig-reference-form-variables`" to learn about the ``variables``
argument.

.. _reference-forms-twig-errors:

form_errors(form.name)
----------------------

Renders any errors for the given field.

.. code-block:: jinja

    {{ form_errors(form.name) }}

    {# render any "global" errors #}
    {{ form_errors(form) }}

.. _reference-forms-twig-widget:

form_widget(form.name, variables)
---------------------------------

Renders the HTML widget of a given field. If you apply this to an entire form
or collection of fields, each underlying form row will be rendered.

.. code-block:: jinja

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

form_row(form.name, variables)
------------------------------

Renders the "row" of a given field, which is the combination of the field's
label, errors and widget.

.. code-block:: jinja

    {# render a field row, but display a label with text "foo" #}
    {{ form_row(form.name, {'label': 'foo'}) }}

The second argument to ``form_row`` is an array of variables. The templates
provided in Symfony only allow to override the label as shown in the example
above.

See ":ref:`twig-reference-form-variables`" to learn about the ``variables``
argument.

.. _reference-forms-twig-rest:

form_rest(form, variables)
--------------------------

This renders all fields that have not yet been rendered for the given form.
It's a good idea to always have this somewhere inside your form as it'll
render hidden fields for you and make any fields you forgot to render more
obvious (since it'll render the field for you).

.. code-block:: jinja

    {{ form_rest(form) }}

.. _reference-forms-twig-enctype:

form_enctype(form)
------------------

If the form contains at least one file upload field, this will render the
required ``enctype="multipart/form-data"`` form attribute. It's always a
good idea to include this in your form tag:

.. code-block:: html+jinja

    <form action="{{ path('form_submit') }}" method="post" {{ form_enctype(form) }}>

.. _`twig-reference-form-variables`:

More about Form "Variables"
---------------------------

In almost every Twig function above, the final argument is an array of "variables"
that are used when rendering that one part of the form. For example, the
following would render the "widget" for a field, and modify its attributes
to include a special class:

.. code-block:: jinja

    {# render a widget, but add a "foo" class to it #}
    {{ form_widget(form.name, { 'attr': {'class': 'foo'} }) }}

The purpose of these variables - what they do & where they come from - may
not be immediately clear, but they're incredibly powerful. Whenever you
render any part of a form, the block that renders it makes use of a number
of variables. By default, these blocks live inside `form_div_layout.html.twig`_.

Look at the ``form_label`` as an example:

.. code-block:: jinja

    {% block form_label %}
        {% if not compound %}
            {% set label_attr = label_attr|merge({'for': id}) %}
        {% endif %}
        {% if required %}
            {% set label_attr = label_attr|merge({'class': (label_attr.class|default('') ~ ' required')|trim}) %}
        {% endif %}
        {% if label is empty %}
            {% set label = name|humanize %}
        {% endif %}
        <label{% for attrname, attrvalue in label_attr %} {{ attrname }}="{{ attrvalue }}"{% endfor %}>{{ label|trans({}, translation_domain) }}</label>
    {% endblock form_label %}

This block makes use of several variables: ``compound``, ``label_attr``, ``required``,
``label``, ``name`` and ``translation_domain``.
These variables are made available by the form rendering system. But more
importantly, these are the variables that you can override when calling ``form_label``
(since in this example, you're rendering the label).

The exact variables available to override depends on which part of the form
you're rendering (e.g. label versus widget) and which field you're rendering
(e.g. a ``choice`` widget has an extra ``expanded`` option). If you get comfortable
with looking through `form_div_layout.html.twig`_, you'll always be able
to see what options you have available.

.. tip::

    Behind the scenes, these variables are made available to the ``FormView``
    object of your form when the form component calls ``buildView`` and ``buildViewBottomUp``
    on each "node" of your form tree. To see what "view" variables a particularly
    field has, find the source code for the form field (and its parent fields)
    and look at the above two functions.

.. note::

    If you're rendering an entire form at once (or an entire embedded form),
    the ``variables`` argument will only be applied to the form itself and
    not its children. In other words, the following will **not** pass a "foo"
    class attribute to all of the child fields in the form:

    .. code-block:: jinja

        {# does **not** work - the variables are not recursive #}
        {{ form_widget(form, { 'attr': {'class': 'foo'} }) }}


.. _`form_div_layout.html.twig`: https://github.com/symfony/symfony/blob/2.1/src/Symfony/Bridge/Twig/Resources/views/Form/form_div_layout.html.twig
