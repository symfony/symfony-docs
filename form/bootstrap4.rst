Bootstrap 4 Form Theme
======================

Symfony provides several ways of integrating Bootstrap into your application. The
most straightforward way is to add the required ``<link>`` and ``<script>``
elements in your templates (usually you only include them in the main layout
template which other templates extend from):

.. code-block:: html+twig

    {# templates/base.html.twig #}

    {# beware that the blocks in your template may be named different #}
    {% block head_css %}
        <!-- Copy CSS from https://getbootstrap.com/docs/4.4/getting-started/introduction/#css -->
    {% endblock %}
    {% block head_js %}
        <!-- Copy JavaScript from https://getbootstrap.com/docs/4.4/getting-started/introduction/#js -->
    {% endblock %}

If your application uses modern front-end practices, it is better to use
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
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/twig
                https://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <twig:config>
                <twig:form-theme>bootstrap_4_layout.html.twig</twig:form-theme>
                <!-- ... -->
            </twig:config>
        </container>

    .. code-block:: php

        // config/packages/twig.php
        use Symfony\Config\TwigConfig;

        return static function (TwigConfig $twig) {
            $twig->formThemes(['bootstrap_4_layout.html.twig']);

            // ...
        };

If you prefer to apply the Bootstrap styles on a form to form basis, include the
``form_theme`` tag in the templates where those forms are used:

.. code-block:: html+twig

    {# ... #}
    {# this tag only applies to the forms defined in this template #}
    {% form_theme form 'bootstrap_4_layout.html.twig' %}

    {% block body %}
        <h1>User Sign Up:</h1>
        {{ form(form) }}
    {% endblock %}

.. _reference-forms-bootstrap4-error-messages:

Error Messages
--------------

Form errors are rendered **inside** the ``<label>`` element to make sure there
is a strong connection between the error and its ``<input>``, as required by the
`WCAG 2.0 standard`_. To achieve this, ``form_errors()`` is called by
``form_label()`` internally. If you call to ``form_errors()`` in your template,
you will get the error messages displayed *twice*.

.. tip::

    Since form errors are rendered *inside* the ``<label>``, you cannot use CSS
    ``:after`` to append an asterisk to the label, because it would be displayed
    after the error message. Use the :ref:`label <reference-form-option-label>`
    or :ref:`label_html <reference-form-option-label-html>` options instead.

Checkboxes and Radios
---------------------

For a checkbox/radio field, calling ``form_label()`` doesn't render anything.
Due to Bootstrap internals, the label is already rendered by ``form_widget()``.

File inputs
-----------

File inputs are rendered using the Bootstrap "custom-file" class, which hides
the name of the selected file. To fix that, use the `bs-custom-file-input`_
JavaScript plugin, as recommended by `Bootstrap Forms documentation`_.

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
Symfony Form ``RadioType`` and ``CheckboxType`` by adding some classes to the label:

* For a `custom radio`_, use ``radio-custom``;
* For a `custom checkbox`_, use ``checkbox-custom``;
* For having a `switch instead of a checkbox`_, use ``switch-custom``.

.. code-block:: twig

    {{ form_row(form.myRadio, {label_attr: {class: 'radio-custom'} }) }}
    {{ form_row(form.myCheckbox, {label_attr: {class: 'checkbox-custom'} }) }}
    {{ form_row(form.myCheckbox, {label_attr: {class: 'switch-custom'} }) }}

.. _`WCAG 2.0 standard`: https://www.w3.org/TR/WCAG20/
.. _`bs-custom-file-input`: https://www.npmjs.com/package/bs-custom-file-input
.. _`Bootstrap Forms documentation`: https://getbootstrap.com/docs/4.4/components/forms/#file-browser
.. _`custom forms`: https://getbootstrap.com/docs/4.4/components/forms/#custom-forms
.. _`custom radio`: https://getbootstrap.com/docs/4.4/components/forms/#radios
.. _`custom checkbox`: https://getbootstrap.com/docs/4.4/components/forms/#checkboxes
.. _`switch instead of a checkbox`: https://getbootstrap.com/docs/4.4/components/forms/#switches
