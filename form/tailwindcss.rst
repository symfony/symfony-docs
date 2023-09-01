Tailwind CSS Form Theme
=======================

Symfony provides a minimal form theme for `Tailwind CSS`_. Tailwind is a *utility first*
CSS framework and provides *unlimited ways* to customize your forms. Tailwind has
an official `form plugin`_ that provides a basic form reset that standardizes their look
on all browsers. This form theme requires this plugin and adds a few basic tailwind
classes so out of the box, your forms will look decent. Customization is almost always
going to be required so this theme makes that easy.

.. image:: /_images/form/tailwindcss-form.png
    :alt: An HTML form showing a range of form types styled using TailwindCSS.

To use, first be sure you have installed and integrated `Tailwind CSS`_ and the
`form plugin`_. Follow their respective documentation to install both packages.

If you prefer to use the Tailwind theme on a form by form basis, include the
``form_theme`` tag in the templates where those forms are used:

.. code-block:: html+twig

    {# ... #}
    {# this tag only applies to the forms defined in this template #}
    {% form_theme form 'tailwind_2_layout.html.twig' %}

    {% block body %}
        <h1>User Sign Up:</h1>
        {{ form(form) }}
    {% endblock %}

Customization
-------------

Customizing CSS classes is especially important for this theme.

Twig Form Functions
~~~~~~~~~~~~~~~~~~~

You can customize classes of individual fields by setting some class options.

.. code-block:: twig

    {{ form_row(form.title, {
        row_class: 'my row classes',
        label_class: 'my label classes',
        error_item_class: 'my error item classes',
        widget_class: 'my widget classes',
        widget_disabled_class: 'my disabled widget classes',
        widget_errors_class: 'my widget with error classes',
    }) }}

When customizing the classes this way the defaults provided by the theme
are *overridden* opposed to merged as is the case with other themes. This
enables you to take full control of the classes without worrying about
*undoing* the generic defaults the theme provides.

Project Specific Form Layout
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you have a generic Tailwind style for all your forms, you can create
a custom form theme using the Tailwind CSS theme as a base.

.. code-block:: twig

    {% use 'tailwind_2_layout.html.twig' %}

    {%- block form_row -%}
        {%- set row_class = row_class|default('my row classes') -%}
        {{- parent() -}}
    {%- endblock form_row -%}

    {%- block widget_attributes -%}
        {%- set widget_class = widget_class|default('my widget classes') -%}
        {%- set widget_disabled_class = widget_disabled_class|default('my disabled widget classes') -%}
        {%- set widget_errors_class = widget_errors_class|default('my widget with error classes') -%}
        {{- parent() -}}
    {%- endblock widget_attributes -%}

    {%- block form_label -%}
        {%- set label_class = label_class|default('my label classes') -%}
        {{- parent() -}}
    {%- endblock form_label -%}

    {%- block form_help -%}
        {%- set help_class = help_class|default('my label classes') -%}
        {{- parent() -}}
    {%- endblock form_help -%}

    {%- block form_errors -%}
        {%- set error_item_class = error_item_class|default('my error item classes') -%}
        {{- parent() -}}
    {%- endblock form_errors -%}

.. _`Tailwind CSS`: https://tailwindcss.com
.. _`form plugin`: https://github.com/tailwindlabs/tailwindcss-forms
