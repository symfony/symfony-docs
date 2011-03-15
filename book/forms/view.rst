.. index::
   pair: Forms; View

Forms in Templates
==================

A Symfony2 :doc:`Form </book/forms/overview>` is made of fields. Fields
describe the form semantic, not its end-user representation; it means that a
form is not necessarily tied to HTML. Instead, it is the responsibility of the 
web designer to display each form field the way he wants. So, displaying a 
Symfony2 form in a template can easily be done manually. But, Symfony2 eases 
form integration and customization by providing helpers for PHP and Twig
templates.

Displaying a Form "manually"
----------------------------

Before diving into the Symfony2 wrappers and how they help you to display form
easily, securely, and fast, you must know that nothing special happens under
the hood. You can use any HTML you want to display a Symfony2 form:

.. code-block:: html

    <form action="#" method="post">
        <input type="text" name="name" />

        <input type="submit" />
    </form>

If there is a validation error, you should display it and fill the fields with
the submitted values to make it easier to fix the problems fast. Just use the
form dedicated methods:

.. configuration-block::

    .. code-block:: html+jinja

        <form action="#" method="post">
            <ul>
                {% for error in form.name.errors %}
                    <li>{{ error.0 }}</li>
                {% endfor %}
            </ul>
            <input type="text" name="name" value="{{ form.name.data }}" />

            <input type="submit" />
        </form>

    .. code-block:: html+php

        <form action="#" method="post">
            <ul>
                <?php foreach ($form['name']->getErrors() as $error): ?>
                    <li><?php echo $error[0] ?></li>
                <?php endforeach; ?>
            </ul>
            <input type="text" name="name" value="<?php $form['name']->getData() ?>" />

            <input type="submit" />
        </form>

The Symfony2 helpers help you to keep your template short, make your form
layout easily customizable, support internationalization, CSRF protection,
file upload, and more out of the box. The following sections tells you
everything about them.

Displaying a Form
-----------------

As the global structure of a form (the form tag, the submit button, ...) is
not defined by the form instance, you are free to use the HTML code you want.
A simple form template reads as follows:

.. code-block:: html

    <form action="#" method="post">
        <!-- Display the form fields -->

        <input type="submit" />
    </form>

Besides the global form structure, you need a way to display global errors and
hidden fields. Symfony2 provides helpers to fulfill this job. In Twig templates,
these helpers are implemented as global functions that can be applied on forms
and form fields. In PHP templates, the "form" helper offers the same
functionality through public methods that accept the form or form field as
parameter.

.. configuration-block::

    .. code-block:: html+jinja

        <form action="#" method="post">
            {{ form_errors(form) }}

            <!-- Display the form fields -->

            {{ form_hidden(form) }}
            <input type="submit" />
        </form>

    .. code-block:: html+php

        <form action="#" method="post">
            <?php echo $view['form']->errors($form) ?>

            <!-- Display the form fields -->

            <?php echo $view['form']->hidden($form) ?>

            <input type="submit" />
        </form>

.. note::

    As you can see, Twig functions are prefixed with "form\_". Other than the
    methods of the "form" helper, these functions are global and prone to
    naming collisions.

.. tip::

    By default, the ``errors`` helper generates a ``<ul>`` list, but this
    can be easily customized as you will see later in this document.

Last but not the least, a form containing a file input must contain the
``enctype`` attribute; use the ``enctype`` helper to take render it:

.. configuration-block::

    .. code-block:: html+jinja

        <form action="#" {{ form_enctype(form) }} method="post">

    .. code-block:: html+php

        <form action="#" <?php echo $view['form']->enctype($form) ?> method="post">

Displaying Fields
-----------------

Accessing form fields is easy as a Symfony2 form acts as an array:

.. configuration-block::

    .. code-block:: html+jinja

        {{ form.title }}

        {# access a field (first_name) nested in a group (user) #}
        {{ form.user.first_name }}

    .. code-block:: html+php

        <?php $form['title'] ?>

        <!-- access a field (first_name) nested in a group (user) -->
        <?php $form['user']['first_name'] ?>

As each field is a Field instance, it cannot be displayed as shown above; use
one of the helpers instead.

The ``render`` helper renders the HTML representation of a field:

.. configuration-block::

    .. code-block:: jinja

        {{ form_field(form.title) }}

    .. code-block:: html+php

        <?php echo $view['form']->render($form['title']) ?>

.. note::

    The field's template is selected based on the field's class name, as you will
    learn later.

The ``label`` helper renders the ``<label>`` tag associated with the field:

.. configuration-block::

    .. code-block:: jinja

        {{ form_label(form.title) }}

    .. code-block:: html+php

        <?php echo $view['form']->label($form['title']) ?>

By default, Symfony2 "humanizes" the field name, but you can give your own
label:

.. configuration-block::

    .. code-block:: jinja

        {{ form_label(form.title, 'Give me a title') }}

    .. code-block:: html+php

        <?php echo $view['form']->label($form['title'], 'Give me a title') ?>

.. note::

    Symfony2 automatically internationalizes all labels and error messages.

The ``errors`` helper renders the field errors:

.. configuration-block::

    .. code-block:: jinja

        {{ form_errors(form.title) }}

    .. code-block:: html+php

        <?php echo $view['form']->errors($form['title']) ?>

Defining the HTML Representation
--------------------------------

The helpers rely on templates to render HTML. By default, Symfony2 comes bundled
with templates for all built-in fields.

In Twig templates, each helper is associated with one template block. The
``form_errors`` function, for example, looks for an ``errors`` block. The 
built-in one reads as follows:

.. code-block:: html+jinja

    {# TwigBundle::form.html.twig #}

    {% block errors %}
        {% if errors %}
        <ul>
            {% for error in errors %}
                <li>{% trans error.0 with error.1 from validators %}</li>
            {% endfor %}
        </ul>
        {% endif %}
    {% endblock errors %}

In PHP templates, on the other hand, each helper is associated with one PHP
template. The ``errors()`` helper looks for an ``errors.php`` template, which
reads as follows:

.. code-block:: html+php

    {# FrameworkBundle:Form:errors.php #}

    <?php if ($errors): ?>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo $view['translator']->trans($error[0], $error[1], 'validators') ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

Here is the full list of helpers and their associated blocks/templates:

========== ================== ==================
Helper      Twig Block         PHP Template Name
========== ================== ==================
``errors`` ``errors``         ``FrameworkBundle:Form:errors.php``
``hidden`` ``hidden``         ``FrameworkBundle:Form:hidden.php``
``label``  ``label``          ``FrameworkBundle:Form:label.php``
``render`` see below          see below
========== ================== ==================

The ``render`` helper is a bit different as it selects the template to
render based on the underscored version of the field's class name. For instance,
it looks for a ``textarea_field`` block or a ``textarea_field.php`` template when 
rendering a ``TextareaField`` instance:

.. configuration-block::

    .. code-block:: html+jinja

        {# TwigBundle::form.html.twig #}

        {% block textarea_field %}
            <textarea {% display field_attributes %}>{{ field.displayedData }}</textarea>
        {% endblock textarea_field %}

    .. code-block:: html+php

        <!-- FrameworkBundle:Form:textarea_field.php -->
        <textarea id="<?php echo $field->getId() ?>" name="<?php echo $field->getName() ?>" <?php if ($field->isDisabled()): ?>disabled="disabled"<?php endif ?>>
            <?php echo $view->escape($field->getDisplayedData()) ?>
        </textarea>

If the block or template does not exist, the method looks for that of the
field's parent classes. That's why there is no default ``collection_field``
block as its representation is exactly the same as of its parent class
(``field_group``).

Customizing Field Representation
--------------------------------

The easiest way to customize a field is by passing custom HTML attributes as
an argument to the ``render`` helper:

.. configuration-block::

    .. code-block:: jinja

        {{ form_field(form.title, { 'class': 'important' }) }}

    .. code-block:: html+php

        <?php echo $view['form']->render($form['title'], array(
            'class' => 'important'
        )) ?>

Some fields, like ``ChoiceField``, accept parameters to customize the field's
representation. You can pass them in the next argument.

.. configuration-block::

    .. code-block:: jinja

        {{ form_field(form.country, {}, { 'separator': ' -- Other countries -- ' }) }}

    .. code-block:: html+php

        <?php echo $view['form']->render($form['country'], array(), array(
            'separator' => ' -- Other countries -- '
        )) ?>

All helpers accept a template name in the last argument, which allows you to
completely change the HTML output of the helper:

.. configuration-block::

    .. code-block:: jinja

        {{ form_field(form.title, {}, {}, 'HelloBundle::form.html.twig') }}

    .. code-block:: html+php

        <?php echo $view['form']->render($form['title'], array(), array(), 
            'HelloBundle:Form:text_field.php'
        ) ?>

Form Theming (Twig only)
~~~~~~~~~~~~~~~~~~~~~~~~

In the last example, the ``HelloBundle::form.html.twig`` is a regular Twig template 
containing blocks defining the HTML representation for fields you want to 
override:

.. code-block:: html+jinja

    {# HelloBundle/Resources/views/form.html.twig #}

    {% block textarea_field %}
        <div class="textarea_field">
            <textarea {% display field_attributes %}>{{ field.displayedData }}</textarea>
        </div>
    {% endblock textarea_field %}

In this example, the ``textarea_field`` block is redefined. Instead of changing
the default representation, you can also extend the default one by using the
Twig native inheritance feature:

.. code-block:: html+jinja

    {# HelloBundle/Resources/views/form.html.twig #}

    {% extends 'TwigBundle::form.html.twig' %}

    {% block date_field %}
        <div class="important_date_field">
            {{ parent() }}
        </div>
    {% endblock date_field %}

If you want to customize all fields of a given form, use the ``form_theme`` tag:

.. code-block:: jinja

    {% form_theme form 'HelloBundle::form.html.twig' %}

Whenever you call the ``form_field`` function on the ``form`` after this call,
Symfony2 will look for a representation in your template before falling back to
the default one.

If the field blocks are defined in several templates, add them as an ordered
array:

.. code-block:: jinja

    {% form_theme form ['HelloBundle::form.html.twig', 'HelloBundle::form.html.twig', 'HelloBundle::hello_form.html.twig'] %}

A theme can be attached to a whole form (as above) or just for a field group:

.. code-block:: jinja

    {% form_theme form.user 'HelloBundle::form.html.twig' %}

Finally, customizing the representation of all forms of an application is
possible via configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        twig:
            form:
                resources: [BlogBundle::form.html.twig, TwigBundle::form.html.twig]

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <twig:config>
            <twig:form>
                <twig:resource>BlogBundle::form.html.twig</twig:resource>
                <twig:resource>TwigBundle::form.html.twig</twig:resource>
            </twig:form>
        </twig:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('twig', array('form' => array(
            'resources' => array('BlogBundle::form.html.twig', 'TwigBundle::form.html.twig),
        )));

.. tip::

    Whenever a form function or tag takes a template name as an argument, you
    can use ``_self`` instead and define the customization directly in the
    current template:

    .. code-block:: jinja

        {% form_theme form _self %}

        {% block textarea_field %}
            ...
        {% endblock %}

        {{ form_field(form.description, {}, {}, _self) }}

Prototyping
-----------

When prototyping a form, you can use the ``render`` helper on the form instead
of manually rendering all fields:

.. configuration-block::

    .. code-block:: html+jinja

        <form action="#" {{ form_enctype(form) }} method="post">
            {{ form_field(form) }}
            <input type="submit" />
        </form>

    .. code-block:: html+php

        <form action="#" <?php echo $view['form']->enctype($form) ?> method="post">
            <?php echo $view['form']->render($form) ?>

            <input type="submit" />
        </form>

As there is no block/template defined for the ``Form`` class, the one of its
parent class - ``FieldGroup`` - is used instead:

.. configuration-block::

    .. code-block:: html+jinja

        {# TwigBundle::form.html.twig #}

        {% block field_group %}
            {{ form_errors(field) }}
            {% for child in field %}
                {% if not child.ishidden %}
                    <div>
                        {{ form_label(child) }}
                        {{ form_errors(child) }}
                        {{ form_field(child) }}
                    </div>
                {% endif %}
            {% endfor %}
            {{ form_hidden(field) }}
        {% endblock field_group %}

    .. code-block:: html+php

        <!-- FrameworkBundle:Form:group/table/field_group.php -->

        <?php echo $view['form']->errors($field) ?>

        <div>
            <?php foreach ($field->getVisibleFields() as $child): ?>
                <div>
                    <?php echo $view['form']->label($child) ?>
                    <?php echo $view['form']->errors($child) ?>
                    <?php echo $view['form']->render($child) ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php echo $view['form']->hidden($field) ?>

.. caution::

    The ``render`` method is not very flexible and should only be used to
    build prototypes.

Learn more from the Cookbook
----------------------------

* :doc:`/cookbook/templating/PHP`
