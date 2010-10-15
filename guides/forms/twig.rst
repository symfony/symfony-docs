.. index::
   pair: Forms; Twig

Forms in Twig Templates
=======================

A Symfony2 :doc:`Form </guides/forms/overview>` is made of fields. Fields
describe the form semantic, not its end-user representation; it means that a
form is not necessarily tied to HTML. Instead, it is the responsibility of the
web designer to display each form field the way he wants. So, displaying a
Symfony2 form in a template can easily be done manually. But, Twig eases form
integration and customization by providing a set of filters that can be applied
on the form and field instances.

Displaying a Form "manually"
----------------------------

Before diving into the Twig filters and how they help you display form easily,
securely, and fast, you must know that nothing special happens under the hood.
You can use any HTML you want to display a Symfony2 form:

.. code-block:: html

    <form action="#" method="post">
        <input type="text" name="name" />

        <input type="submit" />
    </form>

If there is a validation error, you should display it and fill the fields with
the submitted values to make it easier to fix the problems fast. Just use the
form dedicated methods:

.. code-block:: jinja

    <form action="#" method="post">
        <ul>
            {% for error in form.name.errors %}
                <li>{{ error.0 }}</li>
            {% endfor %}
        </ul>
        <input type="text" name="name" value="{{ form.name.data }}" />

        <input type="submit" />
    </form>

The Twig filters help you to keep your template short, make your form layout
easily customizable, support internationalization, CSRF protection, file
upload, and more out of the box. The following sections tells you everything
about them.

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
hidden fields; that's the job of the ``render_errors`` and ``render_hidden``
filters respectively:

.. code-block:: jinja

    <form action="#" method="post">
        {{ form|render_errors }}

        <!-- Display the form fields -->

        {{ form|render_hidden }}
        <input type="submit" />
    </form>

.. note::
   By default, the ``render_errors`` generates a ``<ul>`` list, but this can
   be easily customized as you will see later in this document.

Last but not the least, a form containing a file input must contain the
``enctype`` attribute; use the ``render_enctype`` filter to render it:

.. code-block:: jinja

    <form action="#" {{ form|render_enctype }} method="post">

Displaying Fields
-----------------

Accessing form fields is easy as a Symfony2 form acts as an array:

.. code-block:: jinja

    {{ form.title }}

    {# access a field (first_name) nested in a group (user) #}
    {{ form.user.first_name }}

As each field is a Field instance, it cannot be displayed as show above; use
one of the field filters instead.

The ``render_widget`` filter renders the HTML representation of a field:

.. code-block:: jinja

    {{ form.title|render_widget }}

.. note::
   The field's widget is selected based on the field class name (more
   information below).

The ``render_label`` renders the ``<label>`` tag associated with the field:

.. code-block:: jinja

    {{ form.title|render_label }}

By default, Symfony2 "humanizes" the field name, but you can give your own
label:

.. code-block:: jinja

    {{ form.title|render_label('Give me a title') }}

.. note::
   Symfony2 automatically internationalizes all labels and error messages.

The ``render_errors`` filter renders the field errors:

.. code-block:: jinja

    {{ form.title|render_errors }}

.. tip::
   The ``render_errors`` filter can be used on a form or on a field.

You can also get the data associated with the field (the default data or the
data submitted by the user), via the ``render_data`` filter:

.. code-block:: jinja

    {{ form.title|render_data }}

    {{ form.created_at|render_data|date('Y-m-d') }}

Defining the HTML Representation
--------------------------------

All filters rely on Twig template blocks to render HTML. By default, Symfony2
comes bundled with two templates that define all the needed blocks; one for
form instances (``form.twig``), and one for field instances (``widgets.twig``).

Each filter is associated with one template block. For instance, the
``render_errors`` filter looks for an ``errors`` block. The built-in one reads
as follows:

.. code-block:: jinja

    {# TwigBundle::form.twig #}

    {% block errors %}
        {% if errors %}
        <ul>
            {% for error in errors %}
                <li>{% trans error.0 with error.1 from validators %}</li>
            {% endfor %}
        </ul>
        {% endif %}
    {% endblock errors %}

Here is the full list of filters and their associated block names:

================= ==================
Filter             Block Name
================= ==================
``render_errors`` ``errors``
``render_hidden`` ``hidden``
``render_label``  ``label``
``render``        ``group`` or ``field`` (see below)
================= ==================

The ``render_widget`` filter is a bit different as it selects the block to
render based on the underscore version of the field class name. For instance,
it looks for an ``input_field`` block when rendering an ``InputField``
instance:

.. code-block:: jinja

    {# TwigBundle::widgets.twig #}

    {% block input_field %}
        {% tag "input" with attributes %}
    {% endblock input_field %}

If the block does not exist, the filter looks for a block for one of the field
parent classes. That's why there is no default ``password_field`` block as its
representation is exactly the same as its parent class (``input_field``).

Customizing Field Representation
--------------------------------

The easiest way to customize a widget is by passing custom HTML attributes as
an argument to ``render_widget``:

.. code-block:: jinja

    {{ form.title|render_widget(['class': 'important']) }}

If you want to completely override the HTML representation of a widget, pass a
Twig template that defines the needed template block:

.. code-block:: jinja

    {{ form.title|render_widget([], 'HelloBundle::widgets.twig') }}

The ``HelloBundle::widgets.twig`` is a regular Twig template containing blocks
defining the HTML representation for widgets you want to override:

.. code-block:: jinja

    {# HelloBundle/Resources/views/widgets.twig #}

    {% block input_field %}
        <div class="input_field">
            {% tag "input" with attributes %}
        </div>
    {% endblock input_field %}

In this example, the ``input_field`` block is redefined. Instead of changing
the default representation, you can also extend the default one by using the
Twig native inheritance feature:

.. code-block:: jinja

    {# HelloBundle/Resources/views/widgets.twig #}

    {% extends 'TwigBundle::widgets.twig' %}

    {% block date_time_field %}
        <div class="important_date_field">
            {% parent %}
        </div>
    {% endblock date_time_field %}

If you want to customize all fields of a given form, use the ``form_theme`` tag:

.. code-block:: jinja

    {% form_theme form 'HelloBundle::widgets.twig' %}

Whenever you call the ``render_widget`` filter on the ``form`` after this call,
Symfony2 will look for a representation in your template before falling back to
the default one.

If the widget blocks are defined in several templates, add them as an ordered
array:

.. code-block:: jinja

    {% form_theme form ['HelloBundle::form.twig', 'HelloBundle::widgets.twig', 'HelloBundle::hello_widgets.twig'] %}

A theme can be attached to a whole form (as above) or just for a field group:

.. code-block:: jinja

    {% form_theme form.user 'HelloBundle::widgets.twig' %}

Finally, customizing the representation of all forms of an application is
possible via configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        twig.config:
            form:
                resources: [BlogBundle::widgets.twig]

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <twig:config>
            <twig:form>
                <twig:resource>BlogBundle::widgets.twig</twig:resource>
            </twig:form>
        </twig:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('twig', 'config', array('form' => array(
            'resources' => array('BlogBundle::widgets.twig'),
        )));

Prototyping
-----------

When prototyping a form, you can use the ``render`` filter instead of manually
rendering all fields:

.. code-block:: jinja

    <form action="#" {{ form|render_enctype }} method="post">
        {{ form|render }}
        <input type="submit" />
    </form>

The ``render`` filter can also be used to render a field "row":

.. code-block:: jinja

    <form action="#" {{ form|render_enctype }} method="post">
        {{ form|render_errors }}
        <table>
            {{ form.first_name|render }}
            {{ form.last_name|render }}
        </table>
        {{ form|render_hidden }}
        <input type="submit" />
    </form>

The ``render`` filter uses the ``group`` and ``field`` blocks for rendering:

.. code-block:: jinja

    {# TwigBundle::form.twig #}

    {% block group %}
        {{ group|render_errors }}
        <table>
            {% for field in group %}
                {% if not field.ishidden %}
                    {{ field|render }}
                {% endif %}
            {% endfor %}
        </table>
        {{ group|render_hidden }}
    {% endblock group %}

    {% block field %}
        <tr>
            <th>{{ field|render_label }}</th>
            <td>
                {{ field|render_errors }}
                {{ field|render_widget }}
            </td>
        </tr>
    {% endblock field %}

As for any other filter, ``render`` accepts a template as an argument to
override the default representation:

.. code-block:: jinja

    {{ form|render("HelloBundle::form.twig") }}

.. caution::
    The ``render`` filter is not very flexible and should only be used to
    build prototypes.

.. _branch: http://github.com/fabpot/symfony/tree/fields_as_templates
