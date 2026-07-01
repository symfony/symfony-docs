Form Theming and Customization
==============================

Symfony forms generate HTML using Twig templates called **form themes**. This
article explains how form rendering works, how to customize the appearance of
your forms, and how to create your own themes.

.. _form-rendering-overview:

How Form Rendering Works
------------------------

Before diving into customization, it helps to understand how Symfony turns a
form object into HTML. This knowledge makes troubleshooting and advanced
customization much easier.

The Rendering Pipeline
~~~~~~~~~~~~~~~~~~~~~~

When you call ``{{ form(form) }}`` in a Twig template, Symfony executes the
following steps for each field:

#. **Determine the block name**: Based on the field type and field id, Symfony
   calculates which Twig block to use (e.g., ``text_widget``, ``_user_email_row``).
#. **Search for the block**: Symfony looks for that block in the active form
   themes, starting with the most specific and falling back to parent types.
#. **Render the block**: The matching block is rendered with variables containing
   field data (value, errors, label, etc.).

For example, rendering a field of type ``EmailType`` named ``contact`` in a form
called ``user`` goes through this lookup chain:

.. code-block:: text

    _user_contact_widget  -->  not found? try parent
    email_widget          -->  not found? try parent
    text_widget           -->  found in form_div_layout.html.twig

This inheritance chain allows you to customize at any level of specificity.

Form Field Parts
~~~~~~~~~~~~~~~~

Each form field is composed of several parts that can be rendered and customized
independently:

.. raw:: html

    <object data="../_images/form/form-field-parts.svg" type="image/svg+xml"
        alt="A wireframe showing all form field parts: form_row (contains form_label, form_widget, form_help and form_errors)."
    ></object>

=================  ================================================================
Part               Description
=================  ================================================================
``row``            The complete field: label + widget + help + errors
``label``          The ``<label>`` element
``widget``         The input element(s): ``<input>``, ``<select>``, ``<textarea>``
``help``           The help text displayed below the field
``errors``         Validation error messages for this field
=================  ================================================================

Understanding these parts is essential for customization. When you want to change
how something renders, you need to identify which part to target.

.. _form-theming-basics:

Using Form Themes
-----------------

A form theme is a Twig template containing blocks that define how form fields
are rendered. Symfony provides several built-in themes and you can create your own.

.. _symfony-builtin-forms:

Built-In Form Themes
~~~~~~~~~~~~~~~~~~~~

Symfony ships with the following built-in form themes:

No CSS framework
    ``form_div_layout.html.twig`` (the **default theme** used by Symfony, wraps
    each field in a ``<div>``) and ``form_table_layout.html.twig`` (renders the
    form as an HTML ``<table>``).

`Bootstrap`_
    ``bootstrap_5_layout.html.twig`` / ``bootstrap_5_horizontal_layout.html.twig``
    for the most recent Bootstrap 5 version. Read more about
    :doc:`how to use the Bootstrap form theme </form/bootstrap5>`.

    There are also form themes for legacy Bootstrap versions: ``bootstrap_4_layout.html.twig``
    / ``bootstrap_4_horizontal_layout.html.twig`` for :doc:`Bootstrap 4 integration </form/bootstrap4>`
    and ``bootstrap_3_layout.html.twig`` / ``bootstrap_3_horizontal_layout.html.twig``
    for Bootstrap 3.

`Foundation CSS framework`_
    ``foundation_5_layout.html.twig`` and ``foundation_6_layout.html.twig``
    (for versions 5 and 6 respectively).

`Tailwind CSS`_
    ``tailwind_2_layout.html.twig`` (minimal styles). Read more about
    :doc:`how to use the TailwindCSS form theme </form/tailwindcss>`.

.. _forms-theming-global:
.. _forms-theming-twig:

Applying Themes Globally
~~~~~~~~~~~~~~~~~~~~~~~~

To use a theme for all forms in your application, configure it in
``twig.form_themes``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/twig.yaml
        twig:
            form_themes: ['bootstrap_5_layout.html.twig']

    .. code-block:: xml

        <!-- config/packages/twig.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/twig
                https://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <twig:config>
                <twig:form-theme>bootstrap_5_layout.html.twig</twig:form-theme>
            </twig:config>
        </container>

    .. code-block:: php

        // config/packages/twig.php
        use Symfony\Config\TwigConfig;

        return static function (TwigConfig $twig): void {
            $twig->formThemes(['bootstrap_5_layout.html.twig']);
        };

**Using Multiple Themes**

You can specify multiple themes. Symfony searches them in **reverse order**
(last theme in the list is checked first), falling back through the list until
it finds a matching block:

.. code-block:: yaml

.. configuration-block::

    .. code-block:: yaml

        # config/packages/twig.yaml
        twig:
            form_themes:
                - 'form_div_layout.html.twig'        # last fallback
                - 'bootstrap_5_layout.html.twig'     # checked second
                - 'form/my_customizations.html.twig' # checked first

    .. code-block:: xml

        <!-- config/packages/twig.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/twig
                https://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <twig:config>
                <!-- last fallback -->
                <twig:form-theme>form_div_layout.html.twig</twig:form-theme>
                <!-- checked second -->
                <twig:form-theme>bootstrap_5_layout.html.twig</twig:form-theme>
                <!-- checked first -->
                <twig:form-theme>form/my_customizations.html.twig</twig:form-theme>
            </twig:config>
        </container>

    .. code-block:: php

        // config/packages/twig.php
        use Symfony\Config\TwigConfig;

        return static function (TwigConfig $twig): void {
            $twig->formThemes([
                'form_div_layout.html.twig',        // last fallback
                'bootstrap_5_layout.html.twig',     // checked second
                'form/my_customizations.html.twig', // checked first
            ]);
        };

This layering approach lets you override only the specific blocks you need.
Your custom theme can define a single block, and Symfony handles the rest using
the other themes in the list.

Applying Themes to Single Forms
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Use the ``form_theme`` Twig tag to apply a theme to a specific form:

.. code-block:: twig

    {% form_theme form 'form/my_theme.html.twig' %}

    {{ form_start(form) }}
        {# ... #}
    {{ form_end(form) }}

Your theme only needs to define the blocks you want to customize. Symfony
combines your theme with the globally configured themes: it first looks for
blocks in your theme, then falls back to the global themes for anything not
defined. This means a theme with a single block is perfectly valid.

Multiple themes for one form
............................

When you need to combine several themes, use the ``with`` keyword and an array:

.. code-block:: twig

    {% form_theme form with [
        'bootstrap_5_layout.html.twig',
        'form/my_customizations.html.twig'
    ] %}

Symfony searches these themes in **reverse order** (last to first). In this
example, it first looks in ``my_customizations.html.twig``, then falls back to
``bootstrap_5_layout.html.twig``, and finally to any globally configured themes.
This allows you to layer customizations on top of a base theme.

Themes for child forms
......................

You can apply different themes to embedded forms or collection entries. The
child theme only affects that specific part of the form:

.. code-block:: twig

    {% form_theme form 'form/main_theme.html.twig' %}
    {% form_theme form.address 'form/address_theme.html.twig' %}

In this example, the address sub-form uses ``address_theme.html.twig`` while
the rest of the form uses ``main_theme.html.twig``.

.. _disabling-global-themes-for-single-forms:

Disabling global themes
.......................

By default, the ``form_theme`` tag adds your theme on top of the globally
configured themes. Add the ``only`` keyword to use exclusively the specified
themes, ignoring all global configuration:

.. code-block:: twig

    {% form_theme form with ['form/standalone_theme.html.twig'] only %}

This is useful when building reusable components (like admin bundles) that must
render consistently regardless of the application's global theme configuration.

.. warning::

    When using ``only``, your theme must define all necessary blocks. Either
    provide a complete theme or extend a built-in one using Twig's ``use`` tag:

    .. code-block:: twig

        {# templates/form/standalone_theme.html.twig #}
        {% use 'form_div_layout.html.twig' %}

        {% block text_widget %}
            {# your customization #}
        {% endblock %}

.. _form-customization-sidebar:
.. _form-fragment-naming:

Block Naming Rules
------------------

The key to form customization is understanding how Symfony names the Twig blocks
it looks for. There are two naming patterns depending on your goal.

Customizing by Field Type
~~~~~~~~~~~~~~~~~~~~~~~~~

To customize **all fields of a specific type**, use the pattern:
``{type}_{part}``

======================  ========================================
Block Name              Customizes
======================  ========================================
``text_widget``         The ``<input>`` for all text fields
``textarea_widget``     The ``<textarea>`` for all textarea fields
``choice_widget``       The ``<select>`` or radio/checkbox group
``date_row``            The complete row for all date fields
``form_label``          Labels for all field types
``form_errors``         Error display for all field types
======================  ========================================

The ``{type}`` corresponds to the form type's block prefix. For built-in types,
this is typically the type name in snake_case (``TextType`` is ``text``,
``DateTimeType`` is ``datetime``).

Customizing Individual Fields
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To customize **one specific field**, use the pattern: ``_{form}_{field}_{part}``.
The block name starts with an underscore and includes the form name and field name:

=================================  ==========================================
Block Name                         Customizes
=================================  ==========================================
``_user_email_widget``             The email input in UserType
``_user_email_row``                The complete email row in UserType
``_product_price_label``           The price label in ProductType
``_registration_terms_errors``     Errors for terms checkbox in RegistrationType
=================================  ==========================================

The form name comes from your form type class name in snake_case (``UserType`` is
``user``, ``ProductRegistrationFormType`` is ``product_registration_form``).

.. tip::

    Not sure what the block name should be? Check the Form panel in the
    :doc:`Symfony profiler </profiler>`. It lists all block prefixes used during
    block lookup for each field. Alternatively, inspect the rendered HTML: the field's
    ``id`` attribute (e.g., ``user_email``) gives you the form and field names.

**Overriding the field name in blocks:**

Use the ``block_name`` option to change the field name used in the block::

    $builder->add('name', TextType::class, [
        'block_name' => 'custom_name',
    ]);

Now Symfony looks for ``_product_custom_name_widget`` instead of
``_product_name_widget``. This is useful when you have multiple fields that
should share the same custom rendering.

Customizing Specific Field Instances
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Sometimes you need different rendering for the same field type in different
contexts. Use the ``block_prefix`` option to add a custom prefix to the list
of block names that Symfony searches for when rendering the field::

    $builder->add('biography', TextareaType::class, [
        'block_prefix' => 'book_author',
    ]);

Now you can define ``book_author_widget``, ``book_author_row``, etc.
This is useful when you can't or don't want to create a
:doc:`custom form type </form/create_custom_field_type>`.

Block Inheritance
~~~~~~~~~~~~~~~~~

Every form type :ref:`has a parent type <form-type-hierarchy>`, and Symfony uses
this hierarchy when searching for blocks. For example, ``EmailType`` extends
``TextType``, which extends ``FormType``:

.. code-block:: text

    email_widget  -->  not found
    text_widget   -->  not found
    form_widget   -->  found (renders <input type="text">)

This means customizing ``text_widget`` affects ``EmailType``, ``UrlType``,
``SearchType``, and other text-based types.

.. tip::

    See the :doc:`Form Types Reference </reference/forms/types>` for the parent
    type of each built-in type.

.. _form-custom-prototype:
.. _form-customization-collections:

Customizing Collections
~~~~~~~~~~~~~~~~~~~~~~~

:doc:`Collections </form/form_collections>` have additional block name patterns
for the collection itself (``collection_*``) and its entries (``collection_entry_*``):

.. code-block:: twig

    {% block collection_row %} ... {% endblock %}
    {% block collection_label %} ... {% endblock %}
    {% block collection_widget %} ... {% endblock %}
    {% block collection_help %} ... {% endblock %}
    {% block collection_errors %} ... {% endblock %}

    {# the '_entry' suffix targets individual items within the collection #}
    {% block collection_entry_row %} ... {% endblock %}
    {% block collection_entry_label %} ... {% endblock %}
    {% block collection_entry_widget %} ... {% endblock %}
    {% block collection_entry_help %} ... {% endblock %}
    {% block collection_entry_errors %} ... {% endblock %}

Customizing a Specific Collection
.................................

Consider an ``ArticleType`` form with a ``tags`` field that is a collection of
``TagType`` forms (each ``TagType`` has a ``name`` field):

.. code-block:: twig

    {% block _article_tags_row %}
        {# the entire tags collection (label, widget, help, errors) #}
    {% endblock %}

    {% block _article_tags_entry_row %}
        {# each individual TagType entry in the collection #}
    {% endblock %}

    {% block _article_tags_entry_name_widget %}
        {# the "name" field widget inside each TagType #}
    {% endblock %}

You can chain it for nested collections (e.g., ``_article_tags_entry_colors_entry_row``
for a collection inside each tag).

.. _form-customization-quick-reference:

Quick Reference: What to Customize
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Use this table to find the right block name for common customization tasks:

=======================================  =====================================================
I want to...                             Block to override
=======================================  =====================================================
Change how all text inputs render        ``text_widget``
Add a wrapper around all fields          ``form_row``
Customize all labels                     ``form_label``
Change error message display             ``form_errors``
Customize all help text                  ``form_help``
Customize a specific field's input       ``_{form}_{field}_widget``
Customize a specific field's entire row  ``_{form}_{field}_row``
Customize all date fields                ``date_widget``, ``date_row``
Customize all checkbox fields            ``checkbox_widget``, ``checkbox_row``
Customize collection entries             ``collection_entry_row``, ``collection_entry_widget``
=======================================  =====================================================

.. _create-your-own-form-theme:

Creating Custom Themes
----------------------

You can create themes in two ways: in a separate template file (reusable across
your application) or in the same template as your form (for one-off customizations).

Creating a Theme in a Separate File
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Create a Twig template with the blocks you want to customize:

.. code-block:: html+twig

    {# templates/form/theme.html.twig #}
    {% use 'form_div_layout.html.twig' %}

    {% block form_row %}
        <div class="form-field">
            {{ form_label(form) }}
            {{ form_widget(form) }}
            {{ form_help(form) }}
            {{ form_errors(form) }}
        </div>
    {% endblock %}

    {% block text_widget %}
        <input type="text" {{ block('widget_attributes') }} class="input-text" value="{{ value }}">
    {% endblock %}

The ``{% use %}`` tag imports all blocks from the parent theme, allowing you to
override only what you need. Using ``parent()`` inside a block calls the
original implementation:

.. code-block:: html+twig

    {# templates/form/theme.html.twig #}
    {% use 'form_div_layout.html.twig' %}

    {# ... #}

    {% block text_widget %}
        <div class="input-wrapper">
            {{ parent() }}
        </div>
    {% endblock %}

Creating a Theme in the Same Template
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

For customizations specific to one page, define blocks in the same template:

.. code-block:: html+twig

    {# templates/user/registration.html.twig #}
    {% extends 'base.html.twig' %}

    {% form_theme form _self %}

    {% block _registration_email_row %}
        <div class="email-field-custom">
            {{ form_label(form) }}
            {{ form_widget(form) }}
            <p class="hint">We'll never share your email.</p>
            {{ form_errors(form) }}
        </div>
    {% endblock %}

    {% block body %}
        <h1>Register</h1>
        {{ form(form) }}
    {% endblock %}

.. warning::

    This only works in templates that **extend another template**. If the template
    is rendered on its own (for example, a reusable partial included with
    ``include()``), any theme blocks you define in it will be rendered as regular
    output. This can lead to unexpected markup and errors due to missing variables.

Reusing Built-In Blocks
.......................

When you need to call the original block implementation in a same-template theme,
use ``{% use %}`` with ``parent()``:

.. code-block:: html+twig

    {% form_theme form _self %}
    {% use 'form_div_layout.html.twig' %}

    {% block text_widget %}
        <div class="custom-wrapper">
            {{ parent() }}
        </div>
    {% endblock %}

If you have your own ``text_widget`` block that conflicts with the one you want
to import, use block renaming:

.. code-block:: html+twig

    {% form_theme form _self %}
    {% use 'form_div_layout.html.twig' with text_widget as base_text_widget %}

    {% block text_widget %}
        <div class="custom-wrapper">
            {{ block('base_text_widget') }}
        </div>
    {% endblock %}

Reusing the Parent Form Type Rendering
......................................

When customizing form themes, you may want to reuse the rendering from the
parent type in the type hierarchy. For example, when customizing ``email_widget``,
you might want to wrap the default ``text_widget`` rendering.

To do this, call the same ``form_*()`` function inside the block with the current
``form`` variable. Symfony will automatically resolve to the next candidate in
the block prefix list (following the type hierarchy):

.. code-block:: html+twig

    {% block email_widget %}
        <div class="email-wrapper">
            {# this will render using text_widget (the parent type) #}
            {{ form_widget(form) }}
        </div>
    {% endblock %}

This approach is more maintainable than hardcoding the parent block name, as it
automatically adapts if the type hierarchy changes.

.. _form-theming-examples:

Practical Examples
------------------

This section shows complete examples for common customization scenarios.

Adding a Wrapper to All Fields
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Wrap every field in a custom container:

.. code-block:: html+twig

    {# templates/form/theme.html.twig #}
    {% use 'form_div_layout.html.twig' %}

    {% block form_row %}
        <div class="field-container" data-field-name="{{ name }}">
            {{ form_label(form) }}
            <div class="field-input">
                {{ form_widget(form) }}
            </div>
            {{ form_help(form) }}
            {{ form_errors(form) }}
        </div>
    {% endblock %}

Adding Icons to Input Fields
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Add an icon before or after input widgets:

.. code-block:: html+twig

    {# templates/form/theme.html.twig #}
    {% use 'form_div_layout.html.twig' %}

    {% block email_widget %}
        <div class="input-with-icon">
            <span class="icon">✉️</span>
            {{ parent() }}
        </div>
    {% endblock %}

    {% block search_widget %}
        <div class="input-with-icon">
            <span class="icon">🔍</span>
            {{ parent() }}
        </div>
    {% endblock %}

Customizing Error Display
~~~~~~~~~~~~~~~~~~~~~~~~~

Display errors as a styled alert box:

.. code-block:: html+twig

    {# templates/form/theme.html.twig #}
    {% use 'form_div_layout.html.twig' %}

    {% block form_errors %}
        {% if errors|length > 0 %}
            {% if form is rootform %}
                {# global form errors #}
                <div class="alert alert-danger" role="alert">
                    <strong>Please fix the following errors:</strong>
                    <ul>
                        {% for error in errors %}
                            <li>{{ error.message }}</li>
                        {% endfor %}
                    </ul>
                </div>
            {% else %}
                {# field-level errors #}
                {% for error in errors %}
                    <span class="field-error">{{ error.message }}</span>
                {% endfor %}
            {% endif %}
        {% endif %}
    {% endblock %}

Adding Required Field Indicators
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Display an asterisk next to required field labels:

.. code-block:: html+twig

    {# templates/form/theme.html.twig #}
    {% use 'form_div_layout.html.twig' %}

    {% block form_label %}
        {% if label is not same as(false) %}
            <label{% with {attr: label_attr} %}{{ block('attributes') }}{% endwith %}>
                {{ label }}
                {% if required %}<span class="required-indicator">*</span>{% endif %}
            </label>
        {% endif %}
    {% endblock %}

Learn More
----------

* :doc:`Bootstrap 5 theme with switches, floating labels, input groups </form/bootstrap5>`
* :doc:`Tailwind CSS theme customization </form/tailwindcss>`
* :doc:`Creating reusable custom form types </form/create_custom_field_type>`
* :doc:`Complete reference of all built-in form types </reference/forms/types>`

.. _`Bootstrap`: https://getbootstrap.com
.. _`Foundation CSS framework`: https://get.foundation/
.. _`Tailwind CSS`: https://tailwindcss.com/
