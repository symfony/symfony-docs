How to customize Form Rendering
===============================

Symfony gives you a wide variety of ways to customize the form rendering. In this
guide, you'll learn how to customize every possible part of your form with as
little effort as possible whether you use Twig or PHP as the templating engine.

Form Rendering Basics
---------------------

Recall that the label, error and HTML widget of a form field can easily
be rendered by using the ``form_row`` Twig function or the ``row`` helper
method:

.. code-block:: jinja

    {{ form_row(form.age) }}

.. code-block:: php

    <?php echo $view['form']->row($form['age']) }} ?>

You can also render each of the three parts of the field individually:

.. code-block:: jinja

    <div>
        {{ form_label(form.age) }}
        {{ form_errors(form.age) }}
        {{ form_widget(form.age) }}
    </div>

.. code-block:: php

    <div>
        <?php echo $view['form']->label($form['age']) }} ?>
        <?php echo $view['form']->errors($form['age']) }} ?>
        <?php echo $view['form']->widget($form['age']) }} ?>
    </div>

In both cases, the form label, errors and HTML widget are rendered by using
a set of markup that ships standard with Symfony. For example, both of the
above templates would render:

.. code-block:: html

    <div>
        <label for="form_age">Age</label>
        <ul>
            <li>This field is required</li>
        </ul>
        <input type="number" id="form_age" name="form[age]" />
    </div>

To quickly prototype and test a form, you can render the entire form with
just one line:

.. code-block:: jinja

    {{ form_widget(form) }}

.. code-block:: php

    <?php echo $view['form']->widget($form) }} ?>

The remainder of this recipe will explain how every part of the form's markup
can be modified at several different levels. For more information about form
rendering in general, see :ref:`form-rendering-template`.

What are Form Themes?
---------------------

When Twig renders any part of a form - field labels, errors, ``input`` text fields,
``select`` tags, etc - Symfony uses the markup from a base Twig template file
that ships with Symfony. This template, `form_div_layout.html.twig`_, contains
Twig blocks that define each and every part of a form that can be rendered.
This template represents the default form "theme" and in the next section,
you'll learn how to import your own set of customized form blocks (i.e. themes).

For example, when the widget of a ``integer`` type field is rendered, an ``input``
``number`` field is generated

.. code-block:: html+jinja

    {{ form_widget(form.age) }}

    <input type="number" id="form_age" name="form[age]" required="required" value="33" />

Internally, Symfony uses the ``integer_widget`` block from the `form_div_layout.html.twig`_
template to render the field. This is because the field type is ``integer`` and
you're rendering its ``widget`` (as opposed to its ``label`` or ``errors``).
The default implementation of the ``integer_widget`` block looks like this:

.. code-block:: jinja

    {% block integer_widget %}
        {% set type = type|default('number') %}
        {{ block('field_widget') }}
    {% endblock integer_widget %}

As you can see, this block itself renders another block - ``field_widget``
that lives in `form_div_layout.html.twig`_:

.. code-block:: html+jinja

    {% block field_widget %}
        {% set type = type|default('text') %}
        <input type="{{ type }}" {{ block('widget_attributes') }} value="{{ value }}" />
    {% endblock field_widget %}

The point is, the blocks inside `form_div_layout.html.twig`_ dictate the HTML
output of each part of a form. To customize form output, you just need to
identify and override the correct block. When any number of these form block
customizations are put into a template, that template is known as a from "theme".
When rendering a form, you can choose which form theme(s) you want to apply.

When using PHP as a templating engine the same concepts apply with some difference.
Themes are folders and each part (``integer_widget``, ``field_widget``, ...) is
an individual template file. The theme is named after the Bundle and the
Controller where it resides. The default theme is ``FrameworkBundle:Form`` so
that the templating engine renders ``FrameworkBundle:Form:integer_widget`` to
generate the markup for an integer widget - the corresponding template file is
``FrameworkBundle/Resources/views/Form/integer_widget.html.php``

.. _cookbook-form-customization-sidebar:

.. sidebar:: Knowing which block to customize

    In this example, the customized block name is ``integer_widget`` because you
    want to override the HTML ``widget`` for all ``integer`` field types. If you
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

.. _cookbook-form-theming-methods:

Form Theming
------------

To see the power of form theming, suppose you want to wrap every input ``number``
field with a ``div`` tag. The key to doing this is to customize the
``integer_widget`` block.

When customizing the form field block in Twig, you have two options on *where*
the customized form block can live:

+--------------------------------------+-----------------------------------+-------------------------------------------+
| Method                               | Pros                              | Cons                                      |
+======================================+===================================+===========================================+
| Inside the same template as the form | Quick and easy                    | Can't be reused in other templates        |
+--------------------------------------+-----------------------------------+-------------------------------------------+
| Inside a separate template           | Can be reused by many templates   | Requires an extra template to be created  |
+--------------------------------------+-----------------------------------+-------------------------------------------+

Both methods have the same effect but are better in different situations.

When using PHP as a templating engine you must always create a new template file
to customize a block.

The next section covers the details.

.. _cookbook-form-twig-theming-self:

Twig Method 1: Inside the same Template as the Form
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The easiest way to customize the ``integer_widget`` block is to customize it
directly in the template that's actually rendering the form.

.. code-block:: html+jinja

    {% extends '::base.html.twig' %}

    {% form_theme form _self %}

    {% block integer_widget %}
        <div class="integer_widget">
            {% set type = type|default('number') %}
            {{ block('field_widget') }}
        </div>
    {% endblock %}

    {% block content %}
        {# render the form #}

        {{ form_row(form.age) }}
    {% endblock %}

By using the special ``{% form_theme form _self %}`` tag, Twig looks inside
the same template for any overridden form blocks. Assuming the ``form.age``
field is an ``integer`` type field, when its widget is rendered, the customized
``integer_widget`` block will be used.

The disadvantage of this method is that the customized form block can't be
reused when rendering other forms in other templates. In other words, this method
is most useful when making form customizations that are specific to a single
form in your application. If you want to reuse a form customization across
several (or all) forms in your application, read on to the next section.

.. _cookbook-form-twig-separate-template:

Twig Method 2: Inside a Separate Template
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can also choose to put the customized ``integer_widget`` form block in a
separate template entirely. The code and end-result are the same, but you
can now re-use the form customization across many templates:

.. code-block:: html+jinja

    {# src/Acme/DemoBundle/Resources/views/Form/fields.html.twig #}

    {% block integer_widget %}
        <div class="integer_widget">
            {% set type = type|default('number') %}
            {{ block('field_widget') }}
        </div>
    {% endblock %}

Now that you've created the customized form block, you need to tell Symfony
to use it. Inside the template where you're actually rendering your form,
tell Symfony to use the template via the ``form_theme`` tag:

.. _cookbook-form-twig-theme-import-template:

.. code-block:: html+jinja

    {% form_theme form 'AcmeDemoBundle:Form:fields.html.twig' %}

    {{ form_widget(form.age) }}

When the ``form.age`` widget is rendered, Symfony will use the ``integer_widget``
block from the new template and the ``input`` tag will be wrapped in the
``div`` element specified in the customized block.

.. _cookbook-form-php-theming:

PHP Method
~~~~~~~~~~

In PHP there is only one method which is to create a new template file - this is
similar to the second method used by Twigs.

The template must be named after the part to theme:

.. code-block:: html+php

    <!-- src/Acme/DemoBundle/Resources/views/Form/integer_widget.html.php -->

    <div class="integer_widget">
        <?php echo $view['form']->renderBlock('field_widget', array('type' => isset($type) ? $type : "number")) ?>
    </div>

Now that you've created the customized form template, you need to tell Symfony
to use it. Inside the template where you're actually rendering your form,
tell Symfony to use the template via the ``setTheme`` helper method:

.. _cookbook-form-php-theme-import-template:

.. code-block:: php

    <?php $view['form']->setTheme($form, array('AcmeDemoBundle:Form')) ;?>

    <?php $view['form']->widget($form['age']) ?>

When the ``form.age`` widget is rendered, Symfony will use the customized
``integer_widget.html.php`` template and the ``input`` tag will be wrapped in
the ``div`` element.

.. _cookbook-form-twig-import-base-blocks:

Referencing Base Form Blocks (Twig specific)
--------------------------------------------

So far, to override a particular form block, the best method is to copy
the default block from `form_div_layout.html.twig`_, paste it into a different template,
and the customize it. In many cases, you can avoid doing this by referencing
the base block when customizing it.

This is easy to do, but varies slightly depending on if your form block customizations
are in the same template as the form or a separate template.

Referencing Blocks from inside the same Template as the Form
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Import the blocks by adding a ``use`` tag in the template where you're rendering
the form:

.. code-block:: jinja

    {% use 'form_div_layout.html.twig' with integer_widget as base_integer_widget %}

Now, when the blocks from `form_div_layout.html.twig`_ are imported, the
``integer_widget`` block is called ``base_integer_widget``. This means that when
you redefine the ``integer_widget`` block, you can reference the default markup
via ``base_integer_widget``:

.. code-block:: html+jinja

    {% block integer_widget %}
        <div class="integer_widget">
            {{ block('base_integer_widget') }}
        </div>
    {% endblock %}

Referencing Base Blocks from an External Template
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If your form customizations live inside an external template, you can reference
the base block by using the ``parent()`` Twig function:

.. code-block:: html+jinja

    {# src/Acme/DemoBundle/Resources/views/Form/fields.html.twig #}

    {% extends 'form_div_layout.html.twig' %}

    {% block integer_widget %}
        <div class="integer_widget">
            {{ parent() }}
        </div>
    {% endblock %}

.. note::

    It is not possible to reference the base block when using PHP as the
    templating engine. You have to manually copy the content from the base block
    to your new template file.

.. _cookbook-form-global-theming:

Making Application-wide Customizations
--------------------------------------

If you'd like a certain form customization to be global to your application,
you can accomplish this by making the form customizations to an external
template and then importing it inside your application configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml

        # Twig
        twig:
            form:
                resources:
                    - 'form_div_layout.html.twig'
                    - 'AcmeDemoBundle:Form:fields.html.twig'
            # ...

        # PHP
        framework:
            templating:
                form:
                    resources:
                        - 'FrameworkBundle:Form'
                        - 'AcmeDemoBundle:Form'
            # ...


    .. code-block:: xml

        <!-- app/config/config.xml -->

        <!-- Twig -->
        <twig:config ...>
                <twig:form>
                    <resource>form_div_layout.html.twig</resource>
                    <resource>AcmeDemoBundle:Form:fields.html.twig</resource>
                </twig:form>
                <!-- ... -->
        </twig:config>

        <!-- PHP -->
        <framework:config ...>
            <framework:templating>
                <framework:form>
                    <resource>FrameworkBundle:Form</resource>
                    <resource>AcmeDemoBundle:Form</resource>
                </framework:form>
            </framework:templating>
            <!-- ... -->
        </framework:config>


    .. code-block:: php

        // app/config/config.php

        // Twig
        $container->loadFromExtension('twig', array(
            'form' => array('resources' => array(
                'form_div_layout.html.twig',
                'AcmeDemoBundle:Form:fields.html.twig',
             ))
            // ...
        ));

        // PHP
        $container->loadFromExtension('framework', array(
            'templating' => array('form' =>
                array('resources' => array(
                    'FrameworkBundle:Form',
                    'AcmeDemoBundle:Form',
             )))
            // ...
        ));

Any customized form blocks inside the ``AcmeDemoBundle:Form:fields.html.twig``
template (Twig) or ``src/Acme/DemoBundle/Resources/views/Form`` folder (PHP)
 will be used globally when form elements are rendered.

By default, twig uses a *div* layout when rendering forms. Some people, however,
may prefer to render forms in a *table* layout. Use the ``form_table_layout.html.twig``
resource (Twig) or ``FrameworkBundle:FormTable`` resource (PHP) to use such a
layout:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml

        # Twig
        twig:
            form:
                resources: ['form_table_layout.html.twig']
            # ...

        # PHP
        framework:
            templating:
                form:
                    resources:
                        - 'FrameworkBundle:FormTable'

    .. code-block:: xml

        <!-- app/config/config.xml -->

        <!-- Twig -->
        <twig:config ...>
                <twig:form>
                    <resource>form_table_layout.html.twig</resource>
                </twig:form>
                <!-- ... -->
        </twig:config>

        <!-- PHP -->
        <framework:config ...>
            <framework:templating>
                <framework:form>
                    <resource>FrameworkBundle:FormTable</resource>
                </framework:form>
            </framework:templating>
            <!-- ... -->
        </framework:config>

    .. code-block:: php

        // app/config/config.php

        // Twig
        $container->loadFromExtension('twig', array(
            'form' => array('resources' => array(
                'form_table_layout.html.twig',
             ))
            // ...
        ));

        // PHP
        $container->loadFromExtension('framework', array(
            'templating' => array('form' =>
                array('resources' => array(
                    'FrameworkBundle:FormTable',
             )))
            // ...
        ));

If you only want to make the change in one template, do the following:

.. code-block:: html+jinja

	{% form_theme form 'form_table_layout.html.twig' %}

.. code-block:: html+php

	<?php $view['form']->setTheme($form, array('FrameworkBundle:FormTable')); ?>

Note that the ``form`` variable in the above code is the form view variable
that you passed to your template.

How to customize an Individual field
------------------------------------

So far, you've seen the different ways you can customize the widget output
of all text field types. You can also customize individual fields. For example,
suppose you have two ``text`` fields - ``first_name`` and ``last_name`` - but
you only want to customize one of the fields. This can be accomplished by
customizing a block whose name is a combination of the field id attribute and
which part of the field is being customized. For example:

.. code-block:: html+jinja

    {% form_theme form _self %}

    {% block _product_name_widget %}
        <div class="text_widget">
            {{ block('field_widget' }}
        </div>
    {% endblock %}

    {{ form_widget(form.name) }}

.. code-block:: html+php

    <!-- Main template -->

    <?php echo $view['form']->setTheme($form, array('AcmeDemoBundle:Form')); ?>

    <?php echo $view['form']->widget($form['name']); ?>

    <!-- src/Acme/DemoBundle/Resources/views/Form/_product_name_widget.html.php -->

    <div class="text_widget">
          echo $view['form']->renderBlock('field_widget') ?>
    </div>

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

    {% block _product_name_row %}
        <div class="name_row">
            {{ form_label(form) }}
            {{ form_errors(form) }}
            {{ form_widget(form) }}
        </div>
    {% endblock %}

.. code-block:: html+php

    <!-- _product_name_row.html.php -->

    <div class="name_row">
        <?php echo $view['form']->label($form) ?>
        <?php echo $view['form']->errors($form) ?>
        <?php echo $view['form']->widget($form) ?>
    </div>

Other Common Customizations
---------------------------

So far, this recipe has shown you several different ways to customize a single
piece of how a form is rendered. The key is to customize a specific block that
corresponds to the portion of the form you want to control (see
:ref:`naming form blocks<cookbook-form-customization-sidebar>`).

In the next sections, you'll see how you can make several common form customizations.
To apply these customizations, use one of the methods described in the
:ref:`cookbook-form-theming-methods` section.

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

    {{ form_errors(form.age) }}

.. code-block:: php

    <?php echo $view['form']->render($form['age']); ?>

By default, the errors are rendered inside an unordered list:

.. code-block:: html

    <ul>
        <li>This field is required</li>
    </ul>

To override how errors are rendered for *all* fields, simply copy, paste
and customize the ``field_errors`` block (Twig) or create a new file named
``field_errors.html.php`` (PHP):

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

.. code-block:: html+php

    <!-- fields_errors.html.php -->

    <?php if ($errors): ?>
        <ul class="error_list">
            <?php foreach ($errors as $error): ?>
                <li><?php echo $view['translator']->trans(
                    $error->getMessageTemplate(),
                    $error->getMessageParameters(),
                    'validators'
                ) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif ?>

.. tip::
    See :ref:`cookbook-form-theming-methods` for how to apply this customization.

You can also customize the error output for just one specific field type.
For example, certain errors that are more global to your form (i.e. not specific
to just one field) are rendered separately, usually at the top of your form:

.. code-block:: jinja

    {{ form_errors(form) }}

.. code-block:: php

    <?php echo $view['form']->render($form); ?>

To customize *only* the markup used for these errors, follow the same directions
as above, but now call the block ``form_errors`` (Twig) / the file ``form_errors.html.php``
(PHP). Now, when errors for the ``form`` type are rendered, your customized block
will be used instead of the default ``field_errors`` block.

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

.. code-block:: html+php

    <!-- field_row.html.php -->

    <div class="form_row">
        <?php echo $view['form']->label($form) ?>
        <?php echo $view['form']->errors($form) ?>
        <?php echo $view['form']->widget($form) ?>
    </div>

.. tip::
    See :ref:`cookbook-form-theming-methods` for how to apply this customization.

Adding a "Required" Asterisk to Field Labels
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you want to denote all of your required fields with a required asterisk (``*``),
you can do this by customizing the ``field_label`` block.

If you're making the form customization inside the same template as your
form, modify the ``use`` tag and add the following:

.. code-block:: html+jinja

    {% use 'form_div_layout.html.twig' with field_label as base_field_label %}

    {% block field_label %}
        {{ block('base_field_label') }}

        {% if required %}
            <span class="required" title="This field is required">*</span>
        {% endif %}
    {% endblock %}

If you're making the form customization inside a separate template, use the
following:

.. code-block:: html+jinja

    {% extends 'form_div_layout.html.twig' %}

    {% block field_label %}
        {{ parent() }}

        {% if required %}
            <span class="required" title="This field is required">*</span>
        {% endif %}
    {% endblock %}

When using PHP as a templating engine you have to copy the content from the
original template:

.. code-block:: html+php

    <!-- field_label.html.php -->

    <!-- original content -->
    <label for="<?php echo $view->escape($id) ?>" <?php foreach($attr as $k => $v) { printf('%s="%s" ', $view->escape($k), $view->escape($v)); } ?>><?php echo $view->escape($view['translator']->trans($label)) ?></label>

    <!-- customization -->
    <?php if ($required) : ?>
        <span class="required" title="This field is required">*</span>
    <?php endif ?>

.. tip::
    See :ref:`cookbook-form-theming-methods` for how to apply this customization.

Adding "help" messages
~~~~~~~~~~~~~~~~~~~~~~

You can also customize your form widgets to have an optional "help" message.

If you're making the form customization inside the same template as your
form, modify the ``use`` tag and add the following:

.. code-block:: html+jinja

    {% use 'form_div_layout.html.twig' with field_widget as base_field_widget %}

    {% block field_widget %}
        {{ block('base_field_widget') }}

        {% if help is defined %}
            <span class="help">{{ help }}</div>
        {% endif %}
    {% endblock %}

If you're making the form customization inside a separate template, use the
following:

.. code-block:: html+jinja

    {% extends 'form_div_layout.html.twig' %}

    {% block field_widget %}
        {{ parent() }}

        {% if help is defined %}
            <span class="help">{{ help }}</div>
        {% endif %}
    {% endblock %}

When using PHP as a templating engine you have to copy the content from the
original template:

.. code-block:: html+php

    <!-- field_widget.html.php -->

    <!-- Original content -->
    <input
        type="<?php echo isset($type) ? $view->escape($type) : "text" ?>"
        value="<?php echo $view->escape($value) ?>"
        <?php echo $view['form']->renderBlock('attributes') ?>
    />

    <!-- Customization -->
    <?php if (isset($help)) : ?>
        <span class="help"><?php echo $view->escape($help) ?></div>
    <?php endif ?>

To render a help message below a field, pass in a ``help`` variable:

.. code-block:: jinja

    {{ form_widget(form.title, { 'help': 'foobar' }) }}

.. code-block:: php

    <?php echo $view['form']->widget($form['title'], array('help' => 'foobar')) ?>

.. tip::
    See :ref:`cookbook-form-theming-methods` for how to apply this customization.

.. _`form_div_layout.html.twig`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bridge/Twig/Resources/views/Form/form_div_layout.html.twig