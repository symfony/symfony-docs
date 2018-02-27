Bootstrap 4 Form Theme
======================

Symfony provides several ways of integrating Bootstrap into your application. The
most straightforward way is to just add the required ``<link>`` and ``<script>``
elements in your templates (usually you only include them in the main layout
template which other templates extend from):

.. code-block:: html+twig

    {# templates/base.html.twig #}

    {# beware that the blocks in your template may be named different #}
    {% block head_css %}
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    {% endblock %}
    {% block head_js %}
        <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    {% endblock head %}

If your application uses modern front-end practices, it's better to use
:ref:`Webpack Encore </frontend>` and follow :ref:`this tutorial </frontend/encore/bootstrap>`
to import Bootstrap's sources into your SCSS and JavaScript files.

The next step is to configure the Symfony application to use Bootstrap 4 styles
when rendering forms. If you want to apply them to all forms, define this
configuration:

.. code-block:: yaml

    # config/packages/twig.yaml
    twig:
        # ...
        form_themes: ['bootstrap_4_layout.html.twig']

If you prefer to apply the Bootstrap styles on a form to form basis, include the
``form_theme`` tag in the templates where those forms are used:

.. code-block:: twig

    {# ... #}
    {# this tag only applies to the forms defined in this template #}
    {% form_theme form 'bootstrap_4_layout.html.twig' %}

    {% block body %}
        <h1>User Sign Up:</h1>
        {{ form(form) }}
    {% endblock body %}

Accessibility
-------------

The Bootstrap 4 framework has done a good job making in accessible for function
variations like impaired vision and cognitive ability. Symfony has taken this one
step further to make sure the form theme complies with the `WCAG2.0 standard`_.

This does not mean that your entire website automatically complies with the full
standard, but it does mean that you have come far in your work to create a design
for **all** users.

Custom Forms
------------

Bootstrap 4 has a feature called "`custom forms`_". You can enable that on your
Symfony Form ``RadioType`` and ``CheckboxType`` by adding a class called ``radio-custom``
and ``checkbox-custom`` respectively.

.. code-block:: html+twig

    {{ form_row(form.myRadio, {attr: {class: 'radio-custom'} }) }}
    {{ form_row(form.myCheckbox, {attr: {class: 'checkbox-custom'} }) }}

Labels and Errors
-----------------

When you use the Bootstrap form themes and render the fields manually, calling
``form_label()`` for a checkbox/radio field doesn't show anything. Due to Bootstrap
internals, the label is already shown by ``form_widget()``.

You may also note that the form errors is rendered **inside** the ``<label>``. This
is done to make sure there is a strong connection between the error and in the
``<input>``, as required by the `WCAG2.0 standard`_.

.. _`their documentation`: https://getbootstrap.com/docs/4.0/
.. _`WCAG2.0 standard`: https://www.w3.org/TR/WCAG20/
.. _`custom forms`: https://getbootstrap.com/docs/4.0/components/forms/#custom-forms
