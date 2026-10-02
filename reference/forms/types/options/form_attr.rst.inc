``form_attr``
~~~~~~~~~~~~~

**type**: ``boolean`` or ``string`` **default**: ``false``

When ``true`` and used on a form element, it adds a `"form" attribute`_ to its HTML field representation with
its HTML form id. By doing this, a form element can be rendered outside the HTML form while still working as expected::

    $builder->add('body', TextareaType::class, [
        'form_attr' => true,
    ]);

This can be useful when you need to solve nested form problems.
You can also set this to ``true`` on a root form to automatically set the "form" attribute on all its children.

When some field uses this option, the built-in form themes render an ``id``
attribute on the ``<form>`` element, so the "form" attribute of those fields
points to it. The value of that ``id`` is:

* the ``id`` defined in the ``attr`` option of the root form, if any;
* otherwise, the value of ``form_attr`` when it's a string. The option can be
  a string on any form of the tree, but the string defined on the root form
  wins over the ones defined on its children;
* otherwise, ``form_`` followed by the id of the root form (e.g.
  ``form_registration`` for a form named ``registration``).

The last case adds the ``form_`` prefix because the id of the root form is
already used by the element that wraps the fields:

.. code-block:: html

    <form name="registration" method="post" id="form_registration">
        <div id="registration">
            <!-- ... -->
        </div>
    </form>

    <!-- rendered outside the form, but submitted with it -->
    <textarea id="registration_body" name="registration[body]"
        form="form_registration"></textarea>

Forms that don't use this option don't change: their ``<form>`` element doesn't
include an ``id`` attribute.

.. versionadded:: 8.2

    Rendering an ``id`` attribute on the ``<form>`` element was introduced in
    Symfony 8.2. In previous versions, the fields used the id of the element
    that wraps them, which isn't a valid value for the "form" attribute.

.. note::

    When the root form has no id (e.g. when its name is an empty string), you
    must define the ``id`` in its ``attr`` option or set ``form_attr`` to a
    string. Otherwise, Symfony throws an exception.

.. _`"form" attribute`: https://html.spec.whatwg.org/multipage/form-control-infrastructure.html#attr-fae-form
