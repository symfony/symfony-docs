How to Customize Form Rendering
===============================

This article explains how to customize individual form fields directly in your
templates. For reusable customizations across your entire application, see
:doc:`/form/form_themes`.

.. _form-rendering-basics:

Rendering Forms
---------------

The simplest way to render a form is with a single function call:

.. code-block:: twig

    {{ form(form) }}

This renders everything: the ``<form>`` tag, all fields, labels, errors, and
the closing tag. For more control, render each part separately:

.. code-block:: html+twig

    {{ form_start(form) }}
        {{ form_errors(form) }}

        <div class="row">
            <div class="col">{{ form_row(form.firstName) }}</div>
            <div class="col">{{ form_row(form.lastName) }}</div>
        </div>

        {{ form_row(form.email) }}
        {{ form_rest(form) }}
    {{ form_end(form) }}

.. warning::

    It is considered a best practice to include ``{{ form_rest(form) }}`` before
    ``form_end()``. This ensures that hidden fields (such as :ref:`CSRF tokens <csrf-protection-forms>`)
    and any fields you may have forgotten are properly rendered.

Rendering Individual Field Parts
--------------------------------

The ``form_row()`` function renders a complete field (label, widget, help,
errors). For finer control, render each part separately:

.. raw:: html

    <object data="../_images/form/form-field-parts.svg" type="image/svg+xml"
        alt="Wireframe showing all form field parts: form_row contains form_label, form_widget, form_help and form_errors."
    ></object>

.. code-block:: html+twig

    <div class="custom-field">
        <i class="fa fa-calendar"></i> {{ form_label(form.dueDate) }}
        {{ form_widget(form.dueDate) }}
        <small>{{ form_help(form.dueDate) }}</small>
        <div class="error">{{ form_errors(form.dueDate) }}</div>
    </div>

Form Themes
-----------

The Twig functions and variables above help you customize individual fields.
To customize all forms consistently (e.g., to match your CSS framework), use a
built-in :doc:`form theme </form/form_themes>` or create your own.

.. _twig-reference-form-variables:
.. _reference-form-twig-variables:
.. _twig-form-variables:

Form Variables
--------------

Each form field exposes variables through its ``vars`` property. You can read
these variables directly or override them when calling rendering functions:

.. code-block:: html+twig

    {# reading field variables #}
    <label for="{{ form.email.vars.id }}"
           class="{{ form.email.vars.required ? 'required' }}">
        {{ form.email.vars.label }}
    </label>

    {% if form.email.vars.errors|length > 0 %}
        <span class="has-errors">!</span>
    {% endif %}

    {# overriding variables when rendering #}
    {{ form_label(form.email, 'Your Email Address') }}
    {{ form_widget(form.email, {attr: {class: 'email-input', autofocus: true}}) }}
    {{ form_row(form.email, {
        label: 'Contact Email',
        label_attr: {class: 'required'},
        attr: {placeholder: 'user@example.com'}
    }) }}

.. note::

    Variables passed to ``form_widget(form)`` or ``form_row(form)`` are not
    applied recursively to child fields. Render children individually to
    customize them.

These variables are useful when building completely custom HTML without using
:doc:`form themes </form/form_themes>`. This is the full list of variables
available in form theme blocks and via ``form.field.vars``:

======================  ==============================================================================
Variable                Description
======================  ==============================================================================
``action``              The form's action URL
``attr``                HTML attributes for the input element (a key-value array)
``block_prefixes``      Array of block prefixes for this field's :ref:`type hierarchy <form-type-hierarchy>`
``cache_key``           A unique key which is used for caching
``compound``            ``true`` if this field has a group of children (e.g. a ``choice`` field is a group of checkboxes)
``data``                The :ref:`normalized data <form-data-lifecycle>` value
``disabled``            Whether the field is disabled
``errors``              Validation errors for this field. Note: ``form.errors`` only contains
                        global errors, not all field errors. Use ``valid`` to check form validity
``form``                The ``FormView`` instance itself
``full_name``           The ``name`` HTML attribute value (e.g., ``user[email]``)
``help``                Help text displayed with the field
``id``                  The ``id`` HTML attribute value (e.g., ``user_email``)
``label_attr``          HTML attributes for the ``<label>`` element (a key-value array)
``label``               The label text
``method``              The form's HTTP method
``multipart``           Whether the form needs ``multipart/form-data`` encoding (file uploads)
``name``                The field name (e.g., ``email``), not the full HTML name attribute
``required``            Whether the field is required (adds HTML5 validation to it)
``submitted``           Whether the form has been submitted
``translation_domain``  The :ref:`translation domain <translation-domain>` for this form's labels and messages
``valid``               Whether the form/field passed validation
``value``               The field's current value for rendering. Only applies to the root form element
======================  ==============================================================================

.. tip::

    Use ``{{ dump(form.field.vars) }}`` in your template to see all available
    variables for a specific field.

.. tip::

    Variables are set in the ``buildView()`` and ``finishView()`` methods of
    each form type. Check those methods in the source code to see all available
    variables for a specific field type.

.. _reference-forms-twig-functions:

Twig Form Functions
-------------------

.. _reference-forms-twig-form:

form(form_view, variables)
~~~~~~~~~~~~~~~~~~~~~~~~~~

Renders a complete form including the opening and closing tags.

.. code-block:: twig

    {{ form(form, {method: 'GET'}) }}

This is convenient for prototyping, but use the other helpers for more control:

.. code-block:: twig

    {{ form_start(form) }}
        {{ form_errors(form) }}

        {{ form_row(form.name) }}
        {{ form_row(form.dueDate) }}

        {{ form_row(form.submit, {label: 'Submit me'}) }}
    {{ form_end(form) }}

.. _reference-forms-twig-end:

form_end(form_view, variables)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Renders the ``</form>`` closing tag. By default, it also calls ``form_rest()``
to render any remaining fields, but you can disable that:

.. code-block:: twig

    {{ form_end(form) }}

    {# disable automatic form_rest() call #}
    {{ form_end(form, {render_rest: false}) }}

.. _reference-forms-twig-errors:

form_errors(form_view, variables)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Renders validation errors. When called on the form itself, renders global errors:

.. code-block:: twig

    {{ form_errors(form.name) }}  {# field errors #}
    {{ form_errors(form) }}       {# global errors #}

.. note::

    In the Bootstrap 4 and 5 themes, ``form_errors()`` is already included in
    ``form_label()``. See :doc:`Bootstrap 5 theme documentation </form/bootstrap5>`
    for details.

.. _reference-forms-twig-help:

form_help(form_view, variables)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Renders the help text configured via the ``help`` option.

.. code-block:: twig

    {{ form_help(form.name) }}

.. _reference-forms-twig-label:

form_label(form_view, label, variables)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Renders the ``<label>`` element. The second argument overrides the default label:

.. code-block:: twig

    {{ form_label(form.name) }}

    {{ form_label(form.name, 'Your Name', {label_attr: {'class': 'foo'}}) }}

    {# this is equivalent #}
    {{ form_label(form.name, null, {
        label: 'Your name',
        label_attr: {'class': 'foo'}
    }) }}

.. _reference-forms-twig-parent:

form_parent(form_view)
~~~~~~~~~~~~~~~~~~~~~~

Returns the parent form view, or ``null`` for the root form. Prefer this over
``form.parent``, which fails if the form has a field named ``parent``.

When a form field has validation errors, all built-in form themes automatically
add the ``aria-invalid="true"`` attribute to the ``<input>`` element and link
it to its error messages using ``aria-describedby``. If the field also has a
help text, both the help and error IDs are included in the ``aria-describedby``
value. This improves accessibility for screen readers without any extra
configuration.

.. _reference-forms-twig-rest:

form_rest(form_view, variables)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Renders all fields not yet rendered, including hidden fields like CSRF tokens.
Always include this to avoid missing fields:

.. code-block:: twig

    {{ form_rest(form) }}

.. _reference-forms-twig-row:

form_row(form_view, variables)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Renders a complete field row (label, widget, help, errors):

.. code-block:: twig

    {{ form_row(form.name, {'label': 'Custom Label'}) }}

.. _reference-forms-twig-start:

form_start(form_view, variables)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Renders the ``<form>`` opening tag with the configured method, action, and
``enctype`` (when the form has file uploads).

.. code-block:: twig

    {{ form_start(form, {method: 'GET'}) }}

By default, the ``name`` attribute of the ``<form>`` element is rendered with
the form name. It goes through the ``attr`` variable, so you can override it
or remove it like any other attribute:

.. code-block:: twig

    {# override the name attribute #}
    {{ form_start(form, {attr: {name: 'other-name'}}) }}

    {# remove the name attribute entirely #}
    {{ form_start(form, {attr: {name: false}}) }}

.. versionadded:: 8.2

    Rendering the ``name`` attribute through ``attr`` was introduced in
    Symfony 8.2. Previously, the attribute was always rendered with the form
    name and could not be overridden or removed.

.. _reference-forms-twig-widget:

form_widget(form_view, variables)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Renders the input element(s). Pass ``attr`` to add HTML attributes:

.. code-block:: twig

    {{ form_widget(form.name, {attr: {'class': 'foo'}}) }}

HTML attributes are not applied recursively to child fields if you're rendering
many fields at once (e.g. ``form_widget(form)``).

Twig Form Tests
---------------

Twig tests are used with the `is Twig operator`_ to create conditions.

.. _form-twig-selectedchoice:

selectedchoice(selected_value)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Checks if a choice equals the given value, useful for marking options as selected:

.. code-block:: html+twig

    <option {% if choice is selectedchoice(value) %}selected="selected"{% endif %}>

.. _form-twig-rootform:

rootform
~~~~~~~~

Checks if a form view is the root form (has no parent). Prefer this over
checking ``form.parent is null``, which fails if the form has a field named
``parent``:

.. code-block:: twig

    {% if form is rootform %}
        {{ form_errors(form) }}
    {% endif %}

.. _reference-forms-twig-field-helpers:

Form Field Helpers
------------------

The :ref:`Twig form functions <reference-forms-twig-functions>` generate complete
HTML elements based on the active :doc:`form theme </form/form_themes>`. When you
need full control over the HTML (bypassing themes), use the ``field_*()`` helpers
that return raw values:

.. code-block:: html+twig

    <input
        type="text"
        name="{{ field_name(form.username) }}"
        id="{{ form.username.vars.id }}"
        value="{{ field_value(form.username) }}"
        placeholder="{{ field_label(form.username) }}"
    >

    <select name="{{ field_name(form.country) }}">
        <option value="">Choose...</option>
        {% for label, value in field_choices(form.country) %}
            <option value="{{ value }}">{{ label }}</option>
        {% endfor %}
    </select>

.. _reference-forms-twig-field-choices:

field_choices(form_view)
~~~~~~~~~~~~~~~~~~~~~~~~

Returns an iterable of label/value pairs for choice fields.

.. code-block:: html+twig

    {# render a custom dropdown for a choice field #}
    <ul class="dropdown" role="listbox">
        {% for label, value in field_choices(form.country) %}
            <li role="option" data-value="{{ value }}">{{ label }}</li>
        {% endfor %}
    </ul>

.. _reference-forms-twig-field-errors:

field_errors(form_view)
~~~~~~~~~~~~~~~~~~~~~~~

Returns an iterable of error messages for the field.

.. code-block:: html+twig

    {% for error in field_errors(form.email) %}
        <span class="error">{{ error }}</span>
    {% endfor %}

.. _reference-forms-twig-field-help:

field_help(form_view)
~~~~~~~~~~~~~~~~~~~~~

Returns the help text for the field.

.. code-block:: html+twig

    <input type="text" name="{{ field_name(form.slug) }}" aria-describedby="slug-help">
    <small id="slug-help">{{ field_help(form.slug) }}</small>

.. _reference-forms-twig-field-id:

field_id(form_view)
~~~~~~~~~~~~~~~~~~~

Returns the ``id`` HTML attribute value for the field (e.g., ``user_email``).

.. code-block:: html+twig

    <label for="{{ field_id(form.email) }}">Your Email</label>
    <input type="email" id="{{ field_id(form.email) }}" name="{{ field_name(form.email) }}">

.. _reference-forms-twig-field-label:

field_label(form_view)
~~~~~~~~~~~~~~~~~~~~~~

Returns the label text for the field.

.. code-block:: html+twig

    <span class="floating-label">{{ field_label(form.username) }}</span>

.. _reference-forms-twig-field-name:

field_name(form_view)
~~~~~~~~~~~~~~~~~~~~~

Returns the ``name`` HTML attribute value for the field (e.g., ``user[email]``).

.. code-block:: html+twig

    <input type="hidden" name="{{ field_name(form.token) }}" value="...">

.. _reference-forms-twig-field-value:

field_value(form_view)
~~~~~~~~~~~~~~~~~~~~~~

Returns the current value of the field.

.. code-block:: html+twig

    {# pre-fill a custom input with the form field's value #}
    <input type="range" name="{{ field_name(form.rating) }}" value="{{ field_value(form.rating) }}">

Form Flow Functions
-------------------

When using :doc:`multi-step forms </form/form_flow>`, the following functions
give access to the state of the form flow (current step, total steps, etc.):

* ``form_flow_current_step()``
* ``form_flow_steps()``
* ``form_flow_step_index()``
* ``form_flow_total_steps()``
* ``form_flow_first_step()``
* ``form_flow_last_step()``
* ``form_flow_next_step()``
* ``form_flow_previous_step()``
* ``form_flow_is_first_step()``
* ``form_flow_is_last_step()``
* ``form_flow_can_move_back()``
* ``form_flow_can_move_next()``

They are explained in detail in the article about
:ref:`multi-step forms <reference-forms-twig-form-flow>`.

.. _`is Twig operator`: https://twig.symfony.com/doc/3.x/templates.html#test-operator
