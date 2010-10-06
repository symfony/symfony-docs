.. index::
   pair: Forms; View

Forms in Templates
==================

A Symfony2 :doc:`Form </guides/forms/overview>` is made of fields. Fields
describe the form semantic, not its end-user representation; it means that a
form is not tied to HTML. Instead, it is the responsibility of the web
designer to display each form field the way he wants. So, displaying a
Symfony2 form in a template can easily be done manually. But, Symfony2 eases
form integration and customization by providing a set of wrapper objects.

Displaying a Form "manually"
----------------------------

Before diving into the Symfony2 wrappers and how they help you display form
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

The Symfony2 wrappers help you keep your template short, makes your form
layout easily customizable, supports internationalization, CSRF protection,
file upload, and more out of the box. The following sections tells you
everything about them.

Wrapping the Form for Templates
-------------------------------

To take advantage of the Symfony2 form wrappers, you must pass a special
object to the template, instead of the form instance::

    // src/Application/HelloBundle/Controller/HelloController.php
    public function signupAction()
    {
        $form = ...;

        return $this->render('HelloBundle:Hello:signup.php', array(
            'form' => $this['templating.form']->get($form)
        ));
    }

Instead of passing the form instance directly to the view, we wrap it with an
object that provides methods that help render the form with more flexibility
(``$this['templating.form']->get($form)``).

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
hidden fields; that's the job of the ``errors()`` and ``hidden()`` methods
respectively:

.. code-block:: html+php

    <form action="#" method="post">
        <?php echo $form->errors() ?>

        <!-- Display the form fields -->

        <?php echo $form->hidden() ?>

        <input type="submit" />
    </form>

.. note::
    By default, the ``errors()`` method generates a ``<ul>`` list, but this
    can be easily customized as you will see later in this document.

Last but not the least, a form containing a file input must contain the
``enctype`` attribute; use the ``form()`` method to take care of it:

.. code-block:: html+php

    <?php echo $form->form('#') ?>

Displaying Fields
-----------------

Accessing form fields is easy as a Symfony2 form acts as an array:

.. code-block:: html+php

    <?php $form['title'] ?>

    <!-- access a field (first_name) nested in a group (user) -->
    <?php $form['user']['first_name'] ?>

As each field is a Field instance, it cannot be displayed as show above; use
one of the wrapper method instead.

The ``widget()`` method renders the HTML representation of a field:

.. code-block:: html+php

    <?php echo $form['title']->widget() ?>

.. note::
    The field's widget is selected based on the field class name (more
    information below).

The ``label()`` method renders the ``<label>`` tag associated with the field:

.. code-block:: html+php

    <?php echo $form['title']->label() ?>

By default, Symfony2 "humanizes" the field name, but you can give your own
label:

.. code-block:: html+php

    <?php echo $form['title']->label('Give me a title') ?>

.. note::
    Symfony2 automatically internationalizes all labels and error messages.

The ``errors()`` method renders the field errors:

.. code-block:: html+php

    <?php echo $form['title']->errors() ?>

You can also get the data associated with the field (the default data or the
data submitted by the user), via the ``data`` method:

.. code-block:: html+php

    <?php echo $form['title']->data() ?>

Defining the HTML Representation
--------------------------------

The form wrappers rely on PHP template to render HTML. By default, Symfony2
comes bundled with templates for all built-in fields.

Each method wrapper is associated with one PHP template. For instance, the
``errors()`` method looks for an ``errors.php`` template. The built-in one
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

Here is the full list of methods and their associated template:

========== ==================
Method      Template Name
========== ==================
``errors`` ``FrameworkBundle:Form:errors.php``
``hidden`` ``FrameworkBundle:Form:hidden.php``
``label``  ``FrameworkBundle:Form:label.php``
``render`` ``FrameworkBundle:Form:group/*/field_group.php`` or ``FrameworkBundle:Form:group/*/row.php`` (see below)
========== ==================

The ``widget()`` method is a bit different as it selects the template to
render based on the underscore version of the field class name. For instance,
it looks for an ``input_field.php`` template when rendering an ``InputField``
instance:

.. code-block:: html+php

    <!-- FrameworkBundle:Form:widget/input_field.php -->
    <?php echo $generator->tag('input', $attributes) ?>

If the template does not exist, the method looks for a template for one of the
field parent classes. That's why there is no default ``password_field``
template as its representation is exactly the same as its parent class
(``input_field``).

Customizing Field Representation
--------------------------------

The easiest way to customize a widget is by passing custom HTML attributes as
an argument to ``widget()`` method:

.. code-block:: html+php

    <?php echo $form['title']->widget(array('class' => 'important')) ?>

If you want to completely override the HTML representation of a widget, pass a
PHP template:

.. code-block:: html+php

    <?php echo $form['title']->widget(array(), 'HelloBundle:Form:input_field.php') ?>

Prototyping
-----------

When prototyping a form, you can use the ``render()`` method instead of
manually rendering all fields:

.. code-block:: html+php

    <?php echo $form->form('#') ?>
        <?php echo $form->render() ?>

        <input type="submit" />
    </form>

The field wrappers also have a ``render()`` method to render a field "row":

.. code-block:: jinja

    <?php echo $form->form('#') ?>
        <?php echo $form->errors() ?>
        <table>
            <?php echo $form['first_name']->render() ?>
            <?php echo $form['last_name']->render() ?>
        </table>
        <?php echo $form->hidden() ?>
        <input type="submit" />
    </form>

The ``render()`` method uses the ``field_group.php`` and ``row.php`` templates
for rendering:

.. code-block:: html+php

    <!-- FrameworkBundle:Form:group/table/field_group.php -->

    <?php echo $group->errors() ?>

    <table>
        <?php foreach ($group as $field): ?>
            <?php echo $field->render() ?>
        <?php endforeach; ?>
    </table>

    <?php echo $group->hidden() ?>

    <!-- FrameworkBundle:Form:group/table/row.php -->

    <tr>
        <th>
            <?php echo $field->label() ?>
        </th>
        <td>
            <?php echo $field->errors() ?>
            <?php echo $field->widget() ?>
        </td>
    </tr>

As for any other method, the ``render()`` method accepts a template as an
argument to override the default representation:

.. code-block:: html+php

    <?php echo $form->render('HelloBundle:Form:group/div/field_group.php') ?>

.. caution::
    The ``render()`` method is not very flexible and should only be used to
    build prototypes.

.. _branch: http://github.com/fabpot/symfony/tree/fields_as_templates
