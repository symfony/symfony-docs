.. index::
   single: Forms; Theming
   single: Forms; Customizing fields

How to Work with Form Themes
============================

Every part of how a form is rendered can be customized. You're free to change
how each form "row" renders, change the markup used to render errors, or
even customize how a ``textarea`` tag should be rendered. Nothing is off-limits,
and different customizations can be used in different places.

Symfony uses templates to render each and every part of a form, such as
``label`` tags, ``input`` tags, error messages and everything else.

In Twig, each form "fragment" is represented by a Twig block. To customize
any part of how a form renders, you just need to override the appropriate block.

In PHP, each form "fragment" is rendered via an individual template file.
To customize any part of how a form renders, you just need to override the
existing template by creating a new one.

To understand how this works, customize the ``form_row`` fragment and
add a class attribute to the ``div`` element that surrounds each row. To
do this, create a new template file that will store the new markup:

.. code-block:: html+twig

    {# app/Resources/views/form/fields.html.twig #}
    {% block form_row %}
    {% spaceless %}
        <div class="form_row">
            {{ form_label(form) }}
            {{ form_errors(form) }}
            {{ form_widget(form) }}
        </div>
    {% endspaceless %}
    {% endblock form_row %}

The ``form_row`` form fragment is used when rendering most fields via the
``form_row()`` function. To tell the Form component to use your new ``form_row``
fragment defined above, add the following to the top of the template that
renders the form:

.. code-block:: html+twig

    {# app/Resources/views/default/new.html.twig #}
    {% form_theme form 'form/fields.html.twig' %}

    {# or if you want to use multiple themes #}
    {% form_theme form 'form/fields.html.twig' 'form/fields2.html.twig' %}

    {# ... render the form #}

The ``form_theme`` tag (in Twig) "imports" the fragments defined in the given
template and uses them when rendering the form. In other words, when the
``form_row()`` function is called later in this template, it will use the ``form_row``
block from your custom theme (instead of the default ``form_row`` block
that ships with Symfony).

Your custom theme does not have to override all the blocks. When rendering a block
which is not overridden in your custom theme, the theming engine will fall back
to the global theme (defined at the bundle level).

If several custom themes are provided they will be searched in the listed order
before falling back to the global theme.

To customize any portion of a form, you just need to override the appropriate
fragment. Knowing exactly which block or file to override is the subject of
the next section.

For a more extensive discussion, see :doc:`/form/form_customization`.

.. index::
    single: Forms; Template fragment naming

.. _form-template-blocks:

Form Fragment Naming
--------------------

In Symfony, every part of a form that is rendered - HTML form elements, errors,
labels, etc. - is defined in a base theme, which is a collection of blocks
in Twig and a collection of template files in PHP.

In Twig, every block needed is defined in a single template file (e.g.
`form_div_layout.html.twig`_) that lives inside the `Twig Bridge`_. Inside this
file, you can see every block needed to render a form and every default field
type.

In PHP, the fragments are individual template files. By default they are located in
the ``Resources/views/Form`` directory of the FrameworkBundle (`view on GitHub`_).

Each fragment name follows the same basic pattern and is broken up into two pieces,
separated by a single underscore character (``_``). A few examples are:

* ``form_row`` - used by ``form_row()`` to render most fields;
* ``textarea_widget`` - used by ``form_widget()`` to render a ``textarea`` field
  type;
* ``form_errors`` - used by ``form_errors()`` to render errors for a field;

Each fragment follows the same basic pattern: ``type_part``. The ``type`` portion
corresponds to the field *type* being rendered (e.g. ``textarea``, ``checkbox``,
``date``, etc) whereas the ``part`` portion corresponds to *what* is being
rendered (e.g. ``label``, ``widget``, ``errors``, etc). By default, there
are 4 possible *parts* of a form that can be rendered:

+-------------+----------------------------+---------------------------------------------------------+
| ``label``   | (e.g. ``form_label()``)    | renders the field's label                               |
+-------------+----------------------------+---------------------------------------------------------+
| ``widget``  | (e.g. ``form_widget()``)   | renders the field's HTML representation                 |
+-------------+----------------------------+---------------------------------------------------------+
| ``errors``  | (e.g. ``form_errors()``)   | renders the field's errors                              |
+-------------+----------------------------+---------------------------------------------------------+
| ``row``     | (e.g. ``form_row()``)      | renders the field's entire row (label, widget & errors) |
+-------------+----------------------------+---------------------------------------------------------+

.. note::

    There are actually 2 other *parts* - ``rows`` and ``rest`` -
    but you should rarely if ever need to worry about overriding them.

By knowing the field type (e.g. ``textarea``) and which part you want to
customize (e.g. ``widget``), you can construct the fragment name that needs
to be overridden (e.g. ``textarea_widget``).

.. index::
    single: Forms; Template fragment inheritance

Template Fragment Inheritance
-----------------------------

In some cases, the fragment you want to customize will appear to be missing.
For example, there is no ``textarea_errors`` fragment in the default themes
provided with Symfony. So how are the errors for a textarea field rendered?

The answer is: via the ``form_errors`` fragment. When Symfony renders the errors
for a textarea type, it looks first for a ``textarea_errors`` fragment before
falling back to the ``form_errors`` fragment. Each field type has a *parent*
type (the parent type of ``textarea`` is ``text``, its parent is ``form``),
and Symfony uses the fragment for the parent type if the base fragment doesn't
exist.

So, to override the errors for *only* ``textarea`` fields, copy the
``form_errors`` fragment, rename it to ``textarea_errors`` and customize it. To
override the default error rendering for *all* fields, copy and customize the
``form_errors`` fragment directly.

.. tip::

    The "parent" type of each field type is available in the
    :doc:`form type reference </reference/forms/types>` for each field type.

.. index::
    single: Forms; Global Theming

.. _forms-theming-global:

Global Form Theming
-------------------

In the above example, you used the ``form_theme`` helper (in Twig) to "import"
the custom form fragments into *just* that form. You can also tell Symfony
to import form customizations across your entire project.

.. _forms-theming-twig:

Twig
~~~

To automatically include the customized blocks from the ``fields.html.twig``
template created earlier in *all* templates, modify your application configuration
file:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        twig:
            form_themes:
                - 'form/fields.html.twig'
            # ...

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/twig http://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <twig:config>
                <twig:theme>form/fields.html.twig</twig:theme>
                <!-- ... -->
            </twig:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('twig', array(
            'form_themes' => array(
                'form/fields.html.twig',
            ),
            // ...
        ));

Any blocks inside the ``fields.html.twig`` template are now used globally
to define form output.

.. sidebar::  Customizing Form Output all in a Single File with Twig

    In Twig, you can also customize a form block right inside the template
    where that customization is needed:

    .. code-block:: html+twig

        {% extends 'base.html.twig' %}

        {# import "_self" as the form theme #}
        {% form_theme form _self %}

        {# make the form fragment customization #}
        {% block form_row %}
            {# custom field row output #}
        {% endblock form_row %}

        {% block content %}
            {# ... #}

            {{ form_row(form.task) }}
        {% endblock %}

    The ``{% form_theme form _self %}`` tag allows form blocks to be customized
    directly inside the template that will use those customizations. Use
    this method to quickly make form output customizations that will only
    ever be needed in a single template.

    .. caution::

        This ``{% form_theme form _self %}`` functionality will *only* work
        if your template extends another. If your template does not, you
        must point ``form_theme`` to a separate template.

PHP
~~~

To automatically include the customized templates from the ``app/Resources/views/form``
directory created earlier in *all* templates, modify your application configuration
file:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            templating:
                form:
                    resources:
                        - 'form'
        # ...

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:templating>
                    <framework:form>
                        <framework:resource>form</framework:resource>
                    </framework:form>
                </framework:templating>
                <!-- ... -->
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'templating' => array(
                'form' => array(
                    'resources' => array(
                        'form',
                    ),
                ),
            ),
            // ...
        ));

Any fragments inside the ``app/Resources/views/form`` directory are now used
globally to define form output.

.. _`form_div_layout.html.twig`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bridge/Twig/Resources/views/Form/form_div_layout.html.twig
.. _`Twig Bridge`: https://github.com/symfony/symfony/tree/master/src/Symfony/Bridge/Twig
.. _`view on GitHub`: https://github.com/symfony/symfony/tree/master/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form
