.. index::
   single: Forms; Theming
   single: Forms; Customizing fields

How to Work with Form Themes
============================

This article explains how to use in your app any of the form themes provided by
Symfony and how to create your own custom form theme.

.. _symfony-builtin-forms:

Symfony Built-In Form Themes
----------------------------

Symfony comes with several **built-in form themes** that make your forms look
great when using some of the most popular CSS frameworks. Each theme is defined
in a single Twig template:

* `form_div_layout.html.twig`_, wraps each form field inside a ``<div>`` element
  and it's the theme used by default in Symfony applications unless you configure
  it as explained later in this article.
* `form_table_layout.html.twig`_, wraps the entire form inside a ``<table>``
  element and each form field inside a ``<tr>`` element.
* `bootstrap_3_layout.html.twig`_, wraps each form field inside a ``<div>``
  element with the appropriate CSS classes to apply the styles used by the
  `Bootstrap 3 CSS framework`_.
* `bootstrap_3_horizontal_layout.html.twig`_, it's similar to the previous
  theme, but the CSS classes applied are the ones used to display the forms
  horizontally (i.e. the label and the widget in the same row).
* `bootstrap_4_layout.html.twig`_, same as ``bootstrap_3_layout.html.twig``, but
  updated for `Bootstrap 4 CSS framework`_ styles.
* `bootstrap_4_horizontal_layout.html.twig`_, same as
  ``bootstrap_3_horizontal_layout.html.twig`` but updated for Bootstrap 4 styles.
* `foundation_5_layout.html.twig`_, wraps each form field inside a ``<div>``
  element with the appropriate CSS classes to apply the default styles of the
  `Foundation CSS framework`_.

.. tip::

    Read the article about the :doc:`Bootstrap 4 Symfony form theme </form/bootstrap4>`
    to learn more about it.

.. _forms-theming-global:
.. _forms-theming-twig:

Applying Themes to all Forms
----------------------------

Symfony forms use by default the ``form_div_layout.html.twig`` theme. If you
want to use another theme for all the forms of your app, configure it in the
``twig.form_themes`` option:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/twig.yaml
        twig:
            form_themes: ['bootstrap_4_horizontal_layout.html.twig']
            # ...

    .. code-block:: xml

        <!-- config/packages/twig.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/twig https://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <twig:config>
                <twig:form-theme>bootstrap_4_horizontal_layout.html.twig</twig:form-theme>
                <!-- ... -->
            </twig:config>
        </container>

    .. code-block:: php

        // config/packages/twig.php
        $container->loadFromExtension('twig', [
            'form_themes' => [
                'bootstrap_4_horizontal_layout.html.twig',
            ],
            // ...
        ]);

You can pass multiple themes to this option because sometimes form themes only
redefine a few elements. This way, if some theme doesn't override some element,
Symfony looks up in the other themes.

The order of the themes in the ``twig.form_themes`` option is important. Each
theme overrides all the previous themes, so you must put the most important
themes at the end of the list.

Applying Themes to Single Forms
-------------------------------

Although most of the times you'll apply form themes globally, you may need to
apply a theme only to some specific form. You can do that with the
:ref:`form_theme Twig tag <reference-twig-tag-form-theme>`:

.. code-block:: twig

    {# this form theme will be applied only to the form of this template #}
    {% form_theme form 'foundation_5_layout.html.twig' %}

    {{ form_start(form) }}
        {# ... #}
    {{ form_end(form) }}

The first argument of the ``form_theme`` tag (``form`` in this example) is the
name of the variable that stores the form view object. The second argument is
the path of the Twig template that defines the form theme.

Applying Multiple Themes to Single Forms
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

A form can also be customized by applying several themes. To do this, pass the
path of all the Twig templates as an array using the ``with`` keyword (their
order is important, because each theme overrides all the previous ones):

.. code-block:: html+twig

    {# apply multiple form themes but only to the form of this template #}
    {% form_theme form with [
        'foundation_5_layout.html.twig',
        'forms/my_custom_theme.html.twig'
    ] %}

    {# ... #}

Applying Different Themes to Child Forms
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can also apply a form theme to a specific child of your form:

.. code-block:: twig

    {% form_theme form.a_child_form 'form/my_custom_theme.html.twig' %}

This is useful when you want to have a custom theme for a nested form that's
different than the one of your main form. Specify both your themes:

.. code-block:: html+twig

    {% form_theme form 'form/my_custom_theme.html.twig' %}
    {% form_theme form.a_child_form 'form/my_other_theme.html.twig' %}

.. _disabling-global-themes-for-single-forms:

Disabling Global Themes for Single Forms
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Global form themes defined in the app are always applied to all forms, even
those which use the ``form_theme`` tag to apply their own themes. You may want
to disable this for example when creating an admin interface for a bundle which
can be installed on different Symfony applications (and so you can't control what
themes are enabled globally). To do that, add the ``only`` keyword after the list
of form themes:

.. code-block:: twig

    {% form_theme form with ['foundation_5_layout.html.twig'] only %}

    {# ... #}

.. caution::

    When using the ``only`` keyword, none of Symfony's built-in form themes
    (``form_div_layout.html.twig``, etc.) will be applied. In order to render
    your forms correctly, you need to either provide a fully-featured form theme
    yourself, or extend one of the built-in form themes with Twig's ``use``
    keyword instead of ``extends`` to re-use the original theme contents.

    .. code-block:: twig

        {# templates/form/common.html.twig #}
        {% use "form_div_layout.html.twig" %}

        {# ... #}

Creating your Own Form Theme
----------------------------

Symfony uses Twig blocks to render each part of a form - field labels, errors,
``<input>`` text fields, ``<select>`` tags, etc. A *theme* is a Twig template
with one or more of those blocks that you want to use when rendering a form.

Consider for example a form field that represents an integer property called
``age``. If you add this to the template:

.. code-block:: twig

    {{ form_widget(form.age) }}

The generated HTML content will be something like this (it will vary depending
upon the form themes enabled in your app):

.. code-block:: html

    <input type="number" id="form_age" name="form[age]" required="required" value="33"/>

Symfony uses a Twig block called ``integer_widget`` to render that field. This
is because the field type is ``integer`` and you're rendering its ``widget`` (as
opposed to its ``label`` or ``errors`` or ``help``). The first step to create a
form theme is to know which Twig block to override, as explained in the
following section.

.. _form-customization-sidebar:
.. _form-fragment-naming:

Form Fragment Naming
~~~~~~~~~~~~~~~~~~~~

The naming of form fragments varies depending on your needs:

* If you want to customize **all fields of the same type** (e.g. all ``<textarea>``)
  use the ``field-type_field-part`` pattern (e.g. ``textarea_widget``).
* If you want to customize **only one specific field** (e.g. the ``<textarea>``
  used for the ``description`` field of the form that edits products) use the
  ``_field-id_field-part`` pattern (e.g. ``_product_description_widget``).

In both cases, the ``field-part`` can be any of these valid form field parts:

.. raw:: html

    <object data="../_images/form/form-field-parts.svg" type="image/svg+xml"></object>

Fragment Naming for All Fields of the Same Type
...............................................

These fragment names follow the ``type_part`` pattern, where the ``type``
corresponds to the field *type* being rendered (e.g. ``textarea``, ``checkbox``,
``date``, etc) and the ``part`` corresponds to *what* is being rendered (e.g.
``label``, ``widget``, etc.)

A few examples of fragment names are:

* ``form_row`` - used by :ref:`form_row() <reference-forms-twig-row>` to render
  most fields;
* ``textarea_widget`` - used by :ref:`form_widget() <reference-forms-twig-widget>`
  to render a ``textarea`` field type;
* ``form_errors`` - used by :ref:`form_errors() <reference-forms-twig-errors>`
  to render errors for a field;

Fragment Naming for Individual Fields
.....................................

These fragment names follow the ``_id_part`` pattern, where the ``id``
corresponds to the field ``id`` attribute (e.g. ``product_description``,
``user_age``, etc) and the ``part`` corresponds to *what* is being rendered
(e.g. ``label``, ``widget``, etc.)

The ``id`` attribute contains both the form name and the field name (e.g.
``product_price``). The form name can be set manually or generated automatically
based on your form type name (e.g. ``ProductType`` equates to ``product``). If
you're not sure what your form name is, look at the HTML code rendered for your
form. You can also define this value explicitly with the ``block_name`` option::

    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\Form\Extension\Core\Type\TextType;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // ...

        $builder->add('name', TextType::class, [
            'block_name' => 'custom_name',
        ]);
    }

In this example, the fragment name will be ``_product_custom_name_widget``
instead of the default ``_product_name_widget``.

.. _form-fragment-custom-naming:

Custom Fragment Naming for Individual Fields
............................................

The ``block_prefix`` option allows form fields to define their own custom
fragment name. This is mostly useful to customize some instances of the same
field without having to :doc:`create a custom form type </form/create_custom_field_type>`::

    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\FormBuilderInterface;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, array(
            'block_prefix' => 'wrapped_text',
        ));
    }

.. versionadded:: 4.3

    The ``block_prefix`` option was introduced in Symfony 4.3.

Now you can use ``wrapped_text_row``, ``wrapped_text_widget``, etc. as the block
names.

.. _form-custom-prototype:

Fragment Naming for Collections
...............................

When using a :doc:`collection of forms </form/form_collections>`, the fragment
of each collection item follows a predefined pattern. For example, consider the
following complex example where a ``TaskManagerType`` has a collection of
``TaskListType`` which in turn has a collection of ``TaskType``::

    class TaskManagerType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options = [])
        {
            // ...
            $builder->add('taskLists', CollectionType::class, [
                'entry_type' => TaskListType::class,
                'block_name' => 'task_lists',
            ]);
        }
    }

    class TaskListType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options = [])
        {
            // ...
            $builder->add('tasks', CollectionType::class, [
                'entry_type' => TaskType::class,
            ]);
        }
    }

    class TaskType
    {
        public function buildForm(FormBuilderInterface $builder, array $options = [])
        {
            $builder->add('name');
            // ...
        }
    }

Then you get all the following customizable blocks (where ``*`` can be replaced
by ``row``, ``widget``, ``label``, or ``help``):

.. code-block:: twig

    {% block _task_manager_task_lists_* %}
        {# the collection field of TaskManager #}
    {% endblock %}

    {% block _task_manager_task_lists_entry_* %}
        {# the inner TaskListType #}
    {% endblock %}

    {% block _task_manager_task_lists_entry_tasks_* %}
        {# the collection field of TaskListType #}
    {% endblock %}

    {% block _task_manager_task_lists_entry_tasks_entry_* %}
        {# the inner TaskType #}
    {% endblock %}

    {% block _task_manager_task_lists_entry_tasks_entry_name_* %}
        {# the field of TaskType #}
    {% endblock %}

Template Fragment Inheritance
.............................

Each field type has a *parent* type (e.g. the parent type of ``textarea`` is
``text``, and the parent type of ``text`` is ``form``) and Symfony uses the
fragment for the parent type if the base fragment doesn't exist.

When Symfony renders for example the errors for a textarea type, it looks first
for a ``textarea_errors`` fragment before falling back to the ``text_errors``
and ``form_errors`` fragments.

.. tip::

    The "parent" type of each field type is available in the
    :doc:`form type reference </reference/forms/types>` for each field type.

Creating a Form Theme in the same Template as the Form
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This is recommended when doing customizations specific to a single form in your
app, such as changing all ``<textarea>`` elements of a form or customizing a
very special form field which will be handled with JavaScript.

You only need to add the special ``{% form_theme form _self %}`` tag to the same
template where the form is rendered. This makes Twig to look inside the template
for any overridden form blocks:

.. code-block:: html+twig

    {% extends 'base.html.twig' %}

    {% form_theme form _self %}

    {# this overrides the widget of any field of type integer, but only in the
       forms rendered inside this template #}
    {% block integer_widget %}
        <div class="...">
            {# ... render the HTML element to display this field ... #}
        </div>
    {% endblock %}

    {# this overrides the entire row of the field whose "id" = "product_stock" (and whose
       "name" = "product[stock]") but only in the forms rendered inside this template #}
    {% block _product_stock_row %}
        <div class="..." id="...">
            {# ... render the entire field contents, including its errors ... #}
        </div>
    {% endblock %}

    {# ... render the form ... #}

The main disadvantage of this method is that it only works if your template
extends another (``'base.html.twig'`` in the previous example). If your template
does not, you must point ``form_theme`` to a separate template, as explained in
the next section.

Another disadvantage is that the customized form blocks can't be reused when
rendering other forms in other templates. If that's what you need, create a form
theme in a separate template as explained in the next section.

Creating a Form Theme in a Separate Template
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This is recommended when creating form themes that are used in your entire app
or even reused in different Symfony applications. You only need to create a Twig
template somewhere and follow the :ref:`form fragment naming <form-fragment-naming>`
rules to know which Twig blocks to define.

For example, if your form theme is simple and you only want to override the
``<input type="integer">`` elements, create this template:

.. code-block:: twig

    {# templates/form/my_theme.html.twig #}
    {% block integer_widget %}

        {# ... add all the HTML, CSS and JavaScript needed to render this field #}

    {% endblock %}

Now you need to tell Symfony to use this form theme instead of (or in addition
to) the default theme. As explained in the previous sections of this article, if
you want to apply the theme globally to all forms, define the
``twig.form_themes`` option:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/twig.yaml
        twig:
            form_themes: ['form/my_theme.html.twig']
            # ...

    .. code-block:: xml

        <!-- config/packages/twig.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/twig https://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <twig:config>
                <twig:form-theme>form/my_theme.html.twig</twig:form-theme>
                <!-- ... -->
            </twig:config>
        </container>

    .. code-block:: php

        // config/packages/twig.php
        $container->loadFromExtension('twig', [
            'form_themes' => [
                'form/my_theme.html.twig',
            ],
            // ...
        ]);

If you only want to apply it to some specific forms, use the ``form_theme`` tag:

.. code-block:: twig

    {% form_theme form 'form/my_theme.html.twig' %}

    {{ form_start(form) }}
        {# ... #}
    {{ form_end(form) }}

.. _referencing-base-form-blocks-twig-specific:

Reusing Parts of a Built-In Form Theme
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Creating a complete form theme takes a lot of work because there are too many
different form field types. Instead of defining all those Twig blocks, you can
define only the blocks you are interested in and then configure multiple form
themes in your app or template. This works because when rendering a block which
is not overridden in your custom theme, Symfony falls back to the other themes.

Another solution is to make your form theme template extend from one of the
built-in themes using the `Twig "use" tag`_ instead of the ``extends`` tag so
you can inherit all its blocks (if you are unsure, extend from the default
``form_div_layout.html.twig`` theme):

.. code-block:: twig

    {# templates/form/my_theme.html.twig #}
    {% use 'form_div_layout.html.twig' %}

    {# ... override only the blocks you are interested in #}

Finally, you can also use the `Twig parent() function`_ to reuse the original
content of the built-in theme. This is useful when you only want to make minor
changes, such as wrapping the generated HTML with some element:

.. code-block:: html+twig

    {# templates/form/my_theme.html.twig #}
    {% use 'form_div_layout.html.twig' %}

    {% block integer_widget %}
        <div class="some-custom-class">
            {{ parent() }}
        </div>
    {% endblock %}

This technique also works when defining the form theme in the same template that
renders the form. However, importing the blocks from the built-in themes is a
bit more complicated:

.. code-block:: html+twig

    {% form_theme form _self %}

    {# import a block from the built-in theme and rename it so it doesn't
       conflict with the same block defined in this template #}
    {% use 'form_div_layout.html.twig' with integer_widget as base_integer_widget %}

    {% block integer_widget %}
        <div class="some-custom-class">
            {{ block('base_integer_widget') }}
        </div>
    {% endblock %}

    {# ... render the form ... #}

Customizing the Form Validation Errors
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you define :doc:`validation rules </validation>` for your objects, you'll see
some validation error messages when the submitted data is not valid. These
messages are displayed with the :ref:`form_errors() <reference-forms-twig-errors>`
function and can be customized with the ``form_errors`` Twig block in any form
theme, as explained in the previous sections.

An important thing to consider is that certain errors are associated to the
entire form instead of a specific field. In order to differentiate between
global and local errors, use one of the
:ref:`variables available in forms <reference-form-twig-variables>` called
``compound``. If it is ``true``, it means that what's being currently rendered
is a collection of fields (e.g. a whole form), and not just an individual field:

.. code-block:: html+twig

    {# templates/form/my_theme.html.twig #}
    {% block form_errors %}
        {% if errors|length > 0 %}
            {% if compound %}
                {# ... display the global form errors #}
                <ul>
                    {% for error in errors %}
                        <li>{{ error.message }}</li>
                    {% endfor %}
                </ul>
            {% else %}
                {# ... display the errors for a single field #}
            {% endif %}
        {% endif %}
    {% endblock form_errors %}

.. _`form_div_layout.html.twig`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bridge/Twig/Resources/views/Form/form_div_layout.html.twig
.. _`Twig Bridge`: https://github.com/symfony/symfony/tree/master/src/Symfony/Bridge/Twig
.. _`view on GitHub`: https://github.com/symfony/symfony/tree/master/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form
.. _`form_div_layout.html.twig`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bridge/Twig/Resources/views/Form/form_div_layout.html.twig
.. _`form_table_layout.html.twig`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bridge/Twig/Resources/views/Form/form_table_layout.html.twig
.. _`bootstrap_3_layout.html.twig`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bridge/Twig/Resources/views/Form/bootstrap_3_layout.html.twig
.. _`bootstrap_3_horizontal_layout.html.twig`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bridge/Twig/Resources/views/Form/bootstrap_3_horizontal_layout.html.twig
.. _`bootstrap_4_layout.html.twig`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bridge/Twig/Resources/views/Form/bootstrap_4_layout.html.twig
.. _`bootstrap_4_horizontal_layout.html.twig`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bridge/Twig/Resources/views/Form/bootstrap_4_horizontal_layout.html.twig
.. _`Bootstrap 3 CSS framework`: https://getbootstrap.com/docs/3.3/
.. _`Bootstrap 4 CSS framework`: https://getbootstrap.com/docs/4.1/
.. _`foundation_5_layout.html.twig`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bridge/Twig/Resources/views/Form/foundation_5_layout.html.twig
.. _`Foundation CSS framework`: http://foundation.zurb.com/
.. _`Twig "use" tag`: https://twig.symfony.com/doc/2.x/tags/use.html
.. _`Twig parent() function`: https://twig.symfony.com/doc/2.x/functions/parent.html
