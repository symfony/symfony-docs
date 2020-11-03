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

.. note::

    When the root form has no ID, ``form_attr`` is required to be a string identifier to be used as the form ID.

.. _`"form" attribute`: https://html.spec.whatwg.org/multipage/form-control-infrastructure.html#attr-fae-form
