How to customize Form Rendering in a Twig Template
==================================================

If you're using Twig to render your forms, Symfony gives you a wide variety
of ways to customize their exact output. In this guide, you'll learn how
to customize every possible part of your form with as little effort as possible.

Form Rendering Basics
---------------------

Recall that the label, error and HTML widget of a form field can easily
be rendered by using the ``form_row`` Twig function:

.. code-block:: jinja

    {{ form_row(form.name) }}

You can also render each of the three parts of the field individually:

.. code-block:: jinja

    <div>
        {{ form_label(form.name) }}
        {{ form_errors(form.name) }}
        {{ form_widget(form.name) }}
    </div>

In both cases, the form label, errors and HTML widget are rendered by using
a set of markup that ships standard with Symfony. For example, both of the
above templates would render:

.. code-block:: html

    <div>
        <label for="form_name">Name</label>
        <ul>
            <li>This field is required</li>
        </ul>
        <input type="text" id="form_name" name="form[name]" />
    </div>

To quickly prototype and test a form, you can render the entire form with
just one line:

.. code-block:: jinja

    {{ form_widget(form) }}

The remainder of this recipe will explain how every part of the form's markup
can be modified at several different levels. For more information about form
rendering in general, see :ref:`form-rendering-template`.

What are Form Themes?
---------------------

When any part of a form is rendered - field labels, errors, ``input`` text fields,
``select`` tags, etc - Symfony uses the markup from a base Twig template file
that ships with Symfony. This template, `div_layout.html.twig`_, contains
Twig blocks that define each and every part of a form that can be rendered.
This template represents the default form "theme" and in the next section,
you'll learn how to import your own set of customized form blocks (i.e. themes).

For example, when the widget of a ``text`` type field is rendered, an ``input``
``text`` field is generated

.. code-block:: html+jinja

    {{ form_widget(form.name) }}

    <input type="text" id="form_name" name="form[name]" required="required" value="foo" />

Internally, Symfony uses the ``text_widget`` block from the `div_layout.html.twig`_
template to render the field. This is because the field type is ``text`` and
you're rendering its ``widget`` (as opposed to its ``label`` or ``errors``).
The default implementation of the ``text_widget`` block looks like this:

.. code-block:: jinja

    {% block text_widget %}
        {% set type = type|default('text') %}
        {{ block('field_widget') }}
    {% endblock text_widget %}

As you can see, this block itself renders another block - ``field_widget``
that lives in `div_layout.html.twig`_:

.. code-block:: html+jinja

    {% block field_widget %}
        {% set type = type|default('text') %}
        <input type="{{ type }}" {{ block('attributes') }} value="{{ value }}" />
    {% endblock field_widget %}

The point is, the blocks inside `div_layout.html.twig`_ dictate the HTML
output of each part of a form. To customize form output, you just need to
identify and override the correct block. When any number of these form block
customizations are put into a template, that template is known as a from "theme".
When rendering a form, you can choose which form theme(s) you want to apply.

.. _cookbook-form-twig-customization-sidebar:

.. sidebar:: Knowing which block to customize

    In this example, the customized block name is ``text_widget`` because you
    want to override the HTML ``widget`` for all ``text`` field types. If you
    need to customize textarea fields, you would customize ``textarea_widget``.

    As you can see, the block name is a combination of the field type and
    which part of the field is being rendered (e.g. ``widget``, ``label``,
    ``errors``, ``row``). As such, to customize how errors are rendered for
    just input ``text`` fields, you should customize the ``text_errors`` block.

    More commonly, however, you'll want to customize how errors are displayed
    across *all* fields. You can do this by customizing the ``field_errors``
    block. This takes advantage of field type inheritance. Specifically,
    since the ``text`` type extends from the ``field`` type, the form component
    will first look for the type-specific block (e.g. ``text_errors``) before
    falling back to its parent block name if it doesn't exist (e.g. ``field_errors``).

    For more information on this topic, see :ref:`form-template-blocks`.

.. _cookbook-form-twig-two-methods:

Form Theming: The 2 Methods
---------------------------

To see the power of form theming, suppose you want to wrap every input ``text``
field with a ``div`` tag. The key to doing this is to customize the ``text_widget``
block.

When customizing the form field block, you have two options on *where* the
customized form block can live:

+--------------------------------------+-----------------------------------+-------------------------------------------+
| Method                               | Pros                              | Cons                                      |
+======================================+===================================+===========================================+
| Inside the same template as the form | Quick and easy                    | Can't be reused in other templates        |
+--------------------------------------+-----------------------------------+-------------------------------------------+
| Inside a separate template           | Can be reused by many templates   | Requires an extra template to be created  |
+--------------------------------------+-----------------------------------+-------------------------------------------+

Both methods have the same effect but are better in different situations.
In the next section, you'll learn how to make the same form customization
using both methods.

.. _cookbook-form-theming-self:

Method 1: Inside the same Template as the Form
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The easiest way to customize the ``text_widget`` block is to customize it
directly in the template that's actually rendering the form.

.. code-block:: html+jinja

    {% extends '::base.html.twig' %}

    {% form_theme form _self %}
    {% use 'div_layout.html.twig' %}

    {% block text_widget %}
        <div class="text_widget">
            <input type="text" {{ block('attributes') }} value="{{ value }}" />
        </div>
    {% endblock %}

    {% block content %}
        {# render the form #}

        {{ form_row(form.name) }}
    {% endblock %}

.. caution::
    Note that this **only** works if your template extends a base template
    via the ``extends`` tag. If your template doesn't extend a base template,
    you should put your customized blocks in a separate template (see
    :ref:`cookbook-form-twig-separate-template`).

By using the special ``{% form_theme form _self %}`` tag, Twig looks inside
the same template for any overridden form blocks. Assuming the ``form.name``
field is a ``text`` type field, when its widget is rendered, the customized
``text_widget`` block will be used.

The disadvantage of this method is that the customized form block can't be
reused when rendering other forms in other templates. In other words, this method
is most useful when making form customizations that are specific to a single
form in your application. If you want to reuse a form customization across
several (or all) forms in your application, read on to the next section.

.. note::
    Be sure also to include the ``use`` statement somewhere in your template
    when using this method:

    .. code-block:: jinja

        {% use 'div_layout.html.twig' %}

    This "imports" all of the blocks from the base `div_layout.html.twig`_
    template, which gives you access to the ``attributes`` block. In general,
    the ``use`` tag is helpful when your template *already* extends a base
    template, but you still need to import blocks from a second template.
    Read more about `Horizontal Reuse`_ in the Twig documentation.

.. _cookbook-form-twig-separate-template:

Method 2: Inside a Separate Template
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can also choose to put the customized ``text_widget`` form block in a
separate template entirely. The code and end-result are the same, but you
can now re-use the form customization across many templates:

.. code-block:: html+jinja

    {# src/Acme/DemoBundle/Resources/views/Form/fields.html.twig #}
    {% extends 'div_layout.html.twig' %}

    {% block text_widget %}
        <div class="text_widget">
            <input type="text" {{ block('attributes') }} value="{{ value }}" />
        </div>
    {% endblock %}

.. note::

    The template extends the base template (`div_layout.html.twig`_)
    so that you have access to the ``field_widget`` block defined there. If
    you forget the ``extends`` tag, the HTML input element will be missing
    several HTML attributes (since the ``attributes`` block isn't defined).

Now that you've created the customized form block, you need to tell Symfony
to use it. Inside the template where you're actually rendering your form,
tell Symfony to use the template via the ``form_theme`` tag:

.. _cookbook-form-theme-import-template:

.. code-block:: html+jinja

    {% form_theme form 'AcmeDemoBundle:Form:fields.html.twig' %}

    {{ form_widget(form.name) }}

When the ``form.name`` widget is rendered, Symfony will use the ``text_widget``
block from the new template and the ``input`` tag will be wrapped in the
``div`` element specified in the customized block.

.. _cookbook-form-twig-import-base-blocks:

Referencing Base Form Blocks
----------------------------

So far, to override a particular form block, the best method is to copy
the default block from `div_layout.html.twig`_, paste it into a different template,
and the customize it. In many cases, you can avoid doing this by referencing
the base block when customizing it.

This is easy to do, but varies slightly depending on if your form block customizations
are in the same template as the form or a separate template.

Referencing Blocks from inside the same Template as the Form
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Start by modifying the ``use`` tag in the template where you're rendering
the form:

.. code-block:: jinja

    {% use 'div_layout.html.twig' with text_widget as base_text_widget %}

Now, when the blocks from `div_layout.html.twig`_ are imported, the ``text_widget``
block is called ``base_text_widget``. This means that when you redefine the
``text_widget`` block, you can reference the default markup via ``base_text_widget``:

.. code-block:: html+jinja

    {% block text_widget %}
        <div class="text_widget">
            {{ block('base_text_widget') }}
        </div>
    {% endblock %}

Referencing Base Blocks from an External Template
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If your form customizations live inside an external template, you can reference
the base block by using the ``parent()`` Twig function:

.. code-block:: html+jinja

    {# src/Acme/DemoBundle/Resources/views/Form/fields.html.twig #}
    {% extends 'div_layout.html.twig' %}

    {% block text_widget %}
        <div class="text_widget">
            {{ parent() }}
        </div>
    {% endblock text_widget %}

.. _cookbook-form-global-theming:

Making Application-wide Customizations
--------------------------------------

If you'd like a certain form customization to be global to your application,
you can accomplish this by making the form customizations to an external
template and then importing it inside your application configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        twig:
            form:
                resources: ['AcmeDemoBundle:Form:fields.html.twig']
            # ...

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <twig:config ...>
            <twig:form>
                <twig:resource>AcmeDemoBundle:Form:fields.html.twig</twig:resource>
            </twig:form>
            <!-- ... -->
        </twig:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('twig', array(
            'form' => array('resources' => array('AcmeDemoBundle:Form:fields.html.twig'))
            // ...
        ));

Any customized form blocks inside the ``AcmeDemoBundle:Form:fields.html.twig``
template will be used globally when form elements are rendered.

By default, twig uses a *div* layout when rendering forms. Some people, however,
may prefer to render forms in a *table* layout. The technique to change to
the table layout is the same as shown above, except you will need to change:

``AcmeDemoBundle:Form:fields.html.twig`` to ``table_layout.html.twig``

If you only want to make the change in one template, do the following:

.. code-block:: html+jinja

	{% form_theme form 'table_layout.html.twig' %}

Note that the ``form`` variable in the above code is the form view variable
that you passed to your template.

How to customize an Individual field
------------------------------------

So far, you've seen the different ways you can customize the widget output
of all text field types. You can also customize individual fields. For example,
suppose you have two ``text`` fields - ``first_name`` and ``last_name`` - but
you only want to customize one of the fields. This can be accomplished by
customizing a block whose name is a combination of the field name and which
part of the field is being customized. For example:

.. code-block:: html+jinja

    {% form_theme form _self %}
    {% use 'div_layout.html.twig' %}

    {% block _product_name_widget %}
        <div class="text_widget">
            <input type="text" {{ block('attributes') }} value="{{ value }}" />
        </div>
    {% endblock %}

    {{ form_widget(form.name) }}

Here, the ``_product_name_widget`` defines the template to use for the field
whose *id* is ``product_name`` (name ``product[name]``).

.. tip::
   The ``product`` portion of the field is the form name, which may be set
   manually or generated automatically based on your form type name (e.g.
   ``ProductType`` equates to ``product``). If you're not sure what your
   form name is, just view the source of your generated form.

You can also override the markup for an entire field row using the same method:

.. code-block:: html+jinja

    {% form_theme form _self %}
    {% use 'div_layout.html.twig' %}

    {% block _product_name_row %}
        <div class="name_row">
            {{ form_label(form) }}
            {{ form_errors(form) }}
            {{ form_widget(form) }}
        </div>
    {% endblock %}

    {{ form_row(form.name) }}

Other Common Customizations
---------------------------

So far, this recipe has shown you several different ways to customize a single
piece of how a form is rendered. The key is to customize a specific Twig
block that corresponds to the portion of the form you want to control (see
:ref:`naming form blocks<cookbook-form-twig-customization-sidebar>`).

In the next sections, you'll see how you can make several common form customizations.
To apply these customizations, use one of the two methods described in the
:ref:`cookbook-form-twig-two-methods` section.

Customizing Error Output
~~~~~~~~~~~~~~~~~~~~~~~~

.. note::
   The form component only handles *how* the validation errors are rendered,
   and not the actual validation error messages. The error messages themselves
   are determined by the validation constraints you apply to your objects.
   For more information, see the chapter on :doc:`validation</book/validation>`.

There are many different ways to customize how errors are rendered when a
form is submitted with errors. The error messages for a field are rendered
when you use the ``form_errors`` helper:

.. code-block:: jinja

    {{ form_errors(form.name) }}

By default, the errors are rendered inside an unordered list:

.. code-block:: html

    <ul>
        <li>This field is required</li>
    </ul>

To override how errors are rendered for *all* fields, simply copy, paste
and customize the ``field_errors`` block:

.. code-block:: html+jinja

    {% block field_errors %}
    {% spaceless %}
        {% if errors|length > 0 %}
        <ul class="error_list">
            {% for error in errors %}
                <li>{{ error.messageTemplate|trans(error.messageParameters, 'validators') }}</li>
            {% endfor %}
        </ul>
        {% endif %}
    {% endspaceless %}
    {% endblock field_errors %}

.. tip::
    See :ref:`cookbook-form-twig-two-methods` for how to apply this customization.

You can also customize the error output for just one specific field type.
For example, certain errors that are more global to your form (i.e. not specific
to just one field) are rendered separately, usually at the top of your form:

.. code-block:: jinja

    {{ form_errors(form) }}

To customize *only* the markup used for these errors, follow the same directions
as above, but now call the block ``form_errors``. Now, when errors for the
``form`` type are rendered, the ``form_errors`` block will be used instead
of the default ``field_errors`` block.

Customizing the "Form Row"
~~~~~~~~~~~~~~~~~~~~~~~~~~

When you can manage it, the easiest way to render a form field is via the
``form_row`` function, which renders the label, errors and HTML widget of
a field. To customize the markup used for rendering *all* form field rows,
override the ``field_row`` block. For example, suppose you want to add a
class to the ``div`` element around each row:

.. code-block:: html+jinja

    {% block field_row %}
        <div class="form_row">
            {{ form_label(form) }}
            {{ form_errors(form) }}
            {{ form_widget(form) }}
        </div>
    {% endblock field_row %}

.. tip::
    See :ref:`cookbook-form-twig-two-methods` for how to apply this customization.

Adding a "Required" Asterisk to Field Labels
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you want to denote all of your required fields with a required asterisk (``*``),
you can do this by customizing the ``field_label`` block.

If you're making the form customization inside the same template as your
form, modify the ``use`` tag and add the following:

.. code-block:: html+jinja

    {% use 'div_layout.html.twig' with field_label as base_field_label %}

    {% block field_label %}
        {{ block('base_field_label') }}

        {% if required %}
            <span class="required" title="This field is required">*</span>
        {% endif %}
    {% endblock %}

If you're making the form customization inside a separate template, use the
following:

.. code-block:: html+jinja

    {% block field_label %}
        {{ parent() }}

        {% if required %}
            <span class="required" title="This field is required">*</span>
        {% endif %}
    {% endblock %}

.. tip::
    See :ref:`cookbook-form-twig-two-methods` for how to apply this customization.

Adding "help" messages
~~~~~~~~~~~~~~~~~~~~~~

You can also customize your form widgets to have an optional "help" message.

If you're making the form customization inside the same template as your
form, modify the ``use`` tag and add the following:

.. code-block:: html+jinja

    {% use 'div_layout.html.twig' with field_widget as base_field_widget %}

    {% block field_widget %}
        {{ block('base_field_widget') }}

        {% if help is defined %}
            <span class="help">{{ help }}</div>
        {% endif %}
    {% endblock %}

If you're making the form customization inside a separate template, use the
following:

.. code-block:: html+jinja

    {% block field_widget %}
        {{ parent() }}

        {% if help is defined %}
            <span class="help">{{ help }}</div>
        {% endif %}
    {% endblock %}

To render a help message below a field, pass in a ``help`` variable:

.. code-block:: jinja

    {{ form_widget(form.title, { 'help': 'foobar' }) }}

.. tip::
    See :ref:`cookbook-form-twig-two-methods` for how to apply this customization.

.. _`div_layout.html.twig`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bridge/Twig/Resources/views/Form/div_layout.html.twig
.. _`Horizontal Reuse`: http://www.twig-project.org/doc/templates.html#horizontal-reuse