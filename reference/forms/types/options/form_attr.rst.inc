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

As soon as one field uses this option, the form themes render an ``id``
attribute on the ``<form>`` element, so that the reference of those fields
points to a valid target. That id is:

* the ``id`` defined in the ``attr`` option of the root form, if there's one;
* the string given to ``form_attr`` when the option is a string, wherever it
  is defined in the form tree (a string defined on the root form wins over a
  string defined on one of its children);
* ``form_`` followed by the id of the root form otherwise (e.g.
  ``form_registration`` for a form named ``registration``).

The last case adds the ``form_`` prefix because the id of the root form is
already rendered on the element wrapping the fields:

.. code-block:: html

    <form name="registration" method="post" id="form_registration">
        <div id="registration">
            <!-- ... -->
        </div>
    </form>

    <!-- rendered outside the form, but submitted with it -->
    <textarea name="registration[body]" id="registration_body" form="form_registration"></textarea>

Forms that don't use this option are not affected: no ``id`` attribute is
rendered on their ``<form>`` element.

.. versionadded:: 8.2

    Rendering an ``id`` attribute on the ``<form>`` element was introduced in
    Symfony 8.2. In previous versions, the fields referenced the id of the
    element wrapping them, which is not a valid target for the "form"
    attribute.

.. note::

    When the root form has no ID, ``form_attr`` is required to be a string identifier to be used as the form ID.

.. _`"form" attribute`: https://html.spec.whatwg.org/multipage/form-control-infrastructure.html#attr-fae-form
