.. index::
    single: Forms; Rendering in a template

How to Control the Rendering of a Form
======================================

So far, you've seen how an entire form can be rendered with just one line
of code. Of course, you'll usually need much more flexibility when rendering:

.. code-block:: html+twig

    {# app/Resources/views/default/new.html.twig #}
    {{ form_start(form) }}
        {{ form_errors(form) }}

        {{ form_row(form.task) }}
        {{ form_row(form.dueDate) }}
    {{ form_end(form) }}

You already know the ``form_start()`` and ``form_end()`` functions, but what do
the other functions do?

``form_errors(form)``
    Renders any errors global to the whole form (field-specific errors are displayed
    next to each field).

``form_row(form.dueDate)``
    Renders the label, any errors, and the HTML form widget for the given field
    (e.g. ``dueDate``) inside, by default, a ``div`` element.

The majority of the work is done by the ``form_row()`` helper, which renders
the label, errors and HTML form widget of each field inside a ``div`` tag by
default. In the :doc:`/form/form_themes` section, you'll learn how the ``form_row()``
output can be customized on many different levels.

.. tip::

    You can access the current data of your form via ``form.vars.value``:

.. code-block:: twig

    {{ form.vars.value.task }}

    single: Forms; Rendering each field by hand

Rendering each Field by Hand
----------------------------

The ``form_row()`` helper is great because you can very quickly render each
field of your form (and the markup used for the "row" can be customized as
well). But since life isn't always so simple, you can also render each field
entirely by hand. The end-product of the following is the same as when you
used the ``form_row()`` helper:

.. code-block:: html+twig

    {{ form_start(form) }}
        {{ form_errors(form) }}

        <div>
            {{ form_label(form.task) }}
            {{ form_errors(form.task) }}
            {{ form_widget(form.task) }}
        </div>

        <div>
            {{ form_label(form.dueDate) }}
            {{ form_errors(form.dueDate) }}
            {{ form_widget(form.dueDate) }}
        </div>

        <div>
            {{ form_widget(form.save) }}
        </div>

    {{ form_end(form) }}

If the auto-generated label for a field isn't quite right, you can explicitly
specify it:

.. code-block:: html+twig

    {{ form_label(form.task, 'Task Description') }}

Some field types have additional rendering options that can be passed
to the widget. These options are documented with each type, but one common
option is ``attr``, which allows you to modify attributes on the form element.
The following would add the ``task_field`` class to the rendered input text
field:

.. code-block:: html+twig

    {{ form_widget(form.task, {'attr': {'class': 'task_field'}}) }}

If you need to render form fields "by hand" then you can access individual
values for fields such as the ``id``, ``name`` and ``label``. For example
to get the ``id``:

.. code-block:: html+twig

    {{ form.task.vars.id }}

To get the value used for the form field's name attribute you need to use
the ``full_name`` value:

.. code-block:: html+twig

    {{ form.task.vars.full_name }}

Twig Template Function Reference
--------------------------------

If you're using Twig, a full reference of the form rendering functions is
available in the :doc:`reference manual </reference/forms/twig_reference>`.
Read this to know everything about the helpers available and the options
that can be used with each.
