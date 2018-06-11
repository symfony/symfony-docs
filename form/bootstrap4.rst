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
        <!-- Copy CSS from https://getbootstrap.com/docs/4.0/getting-started/introduction/#css -->
    {% endblock %}
    {% block head_js %}
        <!-- Copy JavaScript from https://getbootstrap.com/docs/4.0/getting-started/introduction/#js -->
    {% endblock %}

If your application uses modern front-end practices, it's better to use
:doc:`Webpack Encore </frontend>` and follow :doc:`this tutorial </frontend/encore/bootstrap>`
to import Bootstrap's sources into your SCSS and JavaScript files.

The next step is to configure the Symfony application to use Bootstrap 4 styles
when rendering forms. If you want to apply them to all forms, define this
configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/twig.yaml
        twig:
            form_themes: ['bootstrap_4_layout.html.twig']

    .. code-block:: xml

        <!-- config/packages/twig.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/twig
                http://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <twig:config>
                <twig:form-theme>bootstrap_4_layout.html.twig</twig:form-theme>
                <!-- ... -->
            </twig:config>
        </container>

    .. code-block:: php

        // config/packages/twig.php
        $container->loadFromExtension('twig', array(
            'form_themes' => array(
                'bootstrap_4_layout.html.twig',
            ),

            // ...
        ));

If you prefer to apply the Bootstrap styles on a form to form basis, include the
``form_theme`` tag in the templates where those forms are used:

.. code-block:: twig

    {# ... #}
    {# this tag only applies to the forms defined in this template #}
    {% form_theme form 'bootstrap_4_layout.html.twig' %}

    {% block body %}
        <h1>User Sign Up:</h1>
        {{ form(form) }}
    {% endblock %}

Accessibility
-------------

The Bootstrap 4 framework has done a good job making it accessible for functional
variations like impaired vision and cognitive ability. Symfony has taken this one
step further to make sure the form theme complies with the `WCAG 2.0 standard`_.

This does not mean that your entire website automatically complies with the full
standard, but it does mean that you have come far in your work to create a design
for **all** users.

Custom Forms
------------

Bootstrap 4 has a feature called "`custom forms`_". You can enable that on your
Symfony Form ``RadioType`` and ``CheckboxType`` by adding a class called ``radio-custom``
and ``checkbox-custom`` respectively.

.. code-block:: html+twig

    {{ form_row(form.myRadio, {label_attr: {class: 'radio-custom'} }) }}
    {{ form_row(form.myCheckbox, {label_attr: {class: 'checkbox-custom'} }) }}

Labels and Errors
-----------------

When you use the Bootstrap form themes and render the fields manually, calling
``form_label()`` for a checkbox/radio field doesn't render anything. Due to Bootstrap
internals, the label is already rendered by ``form_widget()``.

Form errors are rendered **inside** the ``<label>`` element to make sure there
is a strong connection between the error and its ``<input>``, as required by the
`WCAG 2.0 standard`_.

.. _`their documentation`: https://getbootstrap.com/docs/4.0/
.. _`WCAG 2.0 standard`: https://www.w3.org/TR/WCAG20/
.. _`custom forms`: https://getbootstrap.com/docs/4.0/components/forms/#custom-forms
