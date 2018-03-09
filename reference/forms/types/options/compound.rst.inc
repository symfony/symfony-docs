compound
~~~~~~~~

**type**: ``boolean`` **default**: ``true``

The compound option determines whether a form is compound.

Compound forms are structural elements in a form tree.
Only a compound form can have children, in other words can be a parent for other forms.

In a view a compound form is rendered as a ``<div>`` container or
a ``<form>`` element (the whole form is obviously a compound form).

Non-compound forms are always leaves in a form tree, they cannot have children.

A non-compound form is rendered as one of the html form elements: ``<input>``
(``TextType``, ``FileType``, ``HiddenType``), ``<textarea>`` (``TextareType``)
or ``<select>`` (``ChoiceType``).

An interesting case is the ``ChoiceType``. With ``expanded=false`` it is a non-compound form
and is rendered as a ``<select>`` tag. With ``expanded=true`` the ``ChoiceType`` becomes a
compound form and is rendered as a set of radios or checkboxes.
