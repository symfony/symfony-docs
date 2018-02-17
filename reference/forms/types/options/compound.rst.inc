compound
~~~~~~~~

**type**: ``boolean`` **default**: ``true``

The compound option determines whether a form can have children,
in other words is a parent form for other forms.

In terms of data a compound form usually represents an object or an array.
In terms of view a compound form might be a simple ``<div>`` container or
a ``<form>`` element (the whole form).

A non compound form usually represents an object property or an array value
and is rendered with actual form elements: ``<input>`` (``TextType``, ``HiddenType``),
``<textarea>`` (``TextareType``) and ``<select>`` (``ChoiceType``).

A interesting case is the ``ChoiceType``. With ``expanded=false`` it is rendered as
a ``<select>`` form element and thus it is not a compound form. With ``expanded=true``
the ``ChoiceType`` is rendered as a set of radios or checkboxes and becomes a compound form.
