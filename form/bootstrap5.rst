Bootstrap 5 Form Theme
======================

.. versionadded:: 5.3

    The Bootstrap 5 Form Theme was introduced in Symfony 5.3.

Symfony provides several ways of integrating Bootstrap into your application.
The most straightforward way is to add the required ``<link>`` and ``<script>``
elements in your templates (usually you only include them in the main layout
template which other templates extend from):

.. code-block:: html+twig

    {# templates/base.html.twig #}

    {# beware that the blocks in your template may be named different #}
    {% block stylesheets %}
        <!-- Copy CSS from https://getbootstrap.com/docs/5.0/getting-started/introduction/#css -->
    {% endblock %}
    {% block javascripts %}
        <!-- Copy JavaScript from https://getbootstrap.com/docs/5.0/getting-started/introduction/#js -->
    {% endblock %}

If your application uses modern front-end practices, it is better to use
:doc:`Webpack Encore </frontend>` and follow :doc:`this tutorial </frontend/encore/bootstrap>`
to import Bootstrap's sources into your SCSS and JavaScript files.

The next step is to configure the Symfony application to use Bootstrap 5 styles
when rendering forms. If you want to apply them to all forms, define this
configuration:

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
                <!-- ... -->
            </twig:config>
        </container>

    .. code-block:: php

        // config/packages/twig.php
        use Symfony\Config\TwigConfig;

        return static function(TwigConfig $twig) {
            $twig->formThemes(['bootstrap_5_layout.html.twig']);

            // ...
        };

If you prefer to apply the Bootstrap styles on a form to form basis, include the
``form_theme`` tag in the templates where those forms are used:

.. code-block:: html+twig

    {# ... #}
    {# this tag only applies to the forms defined in this template #}
    {% form_theme form 'bootstrap_5_layout.html.twig' %}

    {% block body %}
        <h1>User Sign Up:</h1>
        {{ form(form) }}
    {% endblock %}

.. note::

    By default, all inputs are rendered with the ``mb-3`` class on their
    container. If you override the ``row_attr`` class option, the ``mb-3`` will
    be overridden too and you will need to explicitly add it.

Error Messages
--------------

Unlike in the :doc:`Bootstrap 4 theme </form/bootstrap4>`, errors are rendered
**after** the ``input`` element. However, this still makes a strong connection
between the error and its ``<input>``, as required by the `WCAG 2.0 standard`_.

Checkboxes and Radios
---------------------

For a checkbox/radio field, calling ``form_label()`` doesn't render anything.
Due to Bootstrap internals, the label is already rendered by ``form_widget()``.

Inline Checkboxes and Radios
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you want to render your checkbox or radio fields `inline`_, you can add
the ``checkbox-inline`` or ``radio-inline`` class (depending on your Symfony
Form type or ``ChoiceType`` configuration) to the label class.

.. configuration-block::

    .. code-block:: php

        $builder
            ->add('myCheckbox', CheckboxType::class, [
                'label_attr' => [
                    'class' => 'checkbox-inline',
                ],
            ])
            ->add('myRadio', RadioType::class, [
                'label_attr' => [
                    'class' => 'radio-inline',
                ],
            ]);

    .. code-block:: twig

        {{ form_row(form.myCheckbox, {
            label_attr: {
                class: 'checkbox-inline'
            }
        }) }}

        {{ form_row(form.myRadio, {
            label_attr: {
                class: 'radio-inline'
            }
        }) }}

Switches
~~~~~~~~

Bootstrap 5 allows to render checkboxes as `switches`_. You can enable this
feature on your Symfony Form ``CheckboxType`` by adding the ``checkbox-switch``
class to the label:

.. configuration-block::

    .. code-block:: php

        $builder->add('myCheckbox', CheckboxType::class, [
            'label_attr' => [
                'class' => 'checkbox-switch',
            ],
        ]);

    .. code-block:: twig

        {{ form_row(form.myCheckbox, {
            label_attr: {
                class: 'checkbox-switch'
            }
        }) }}

.. tip::

    You can also render your switches inline by simply adding the
    ``checkbox-inline`` class on the ``label_attr`` option::

        // ...
        'label_attr' => [
            'class' => 'checkbox-inline checkbox-switch',
        ],
        // ...

.. caution::

    Switches only work with **checkbox**.

Input group
-----------

To create `input group`_ in your Symfony Form, simply add the ``input-group``
class to the ``row_attr`` option.

.. configuration-block::

    .. code-block:: php

        $builder->add('email', EmailType::class, [
            'label' => '@',
            'row_attr' => [
                'class' => 'input-group',
            ],
        ]);

    .. code-block:: twig

        {{ form_row(form.email, {
            label: '@',
            row_attr: {
                class: 'input-group'
            }
        }) }}

.. caution::

    If you fill the ``help`` option of your form, it will also be rendered
    as part of the group.

Floating labels
---------------

To render an input field with a `floating label`_, you must add a ``label``,
a ``placeholder`` and the ``form-floating`` class to the ``row_attr`` option
of your form type.

.. configuration-block::

    .. code-block:: php

        $builder->add('name', TextType::class, [
            'label' => 'Name',
            'attr' => [
                'placeholder' => 'Name',
            ],
            'row_attr' => [
                'class' => 'form-floating',
            ],
        ]);

    .. code-block:: twig

        {{ form_row(form.name, {
            label: 'Name',
            attr: {
                placeholder: 'Name'
            },
            row_attr: {
                class: 'form-floating'
            }
        }) }}

.. caution::

    You **must** provide a ``label`` and a ``placeholder`` to make floating
    labels work properly.

Accessibility
-------------

The Bootstrap 5 framework has done a good job making it accessible for
functional variations like impaired vision and cognitive ability. Symfony has
taken this one step further to make sure the form theme complies with the
`WCAG 2.0 standard`_.

This does not mean that your entire website automatically complies with the full
standard, but it does mean that you have come far in your work to create a
design for **all** users.

.. _`WCAG 2.0 standard`: https://www.w3.org/TR/WCAG20/
.. _`inline`: https://getbootstrap.com/docs/5.0/forms/checks-radios/#inline
.. _`switches`: https://getbootstrap.com/docs/5.0/forms/checks-radios/#switches
.. _`input group`: https://getbootstrap.com/docs/5.0/forms/input-group/
.. _`floating label`: https://getbootstrap.com/docs/5.0/forms/floating-labels/
