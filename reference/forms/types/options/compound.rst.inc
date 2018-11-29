compound
~~~~~~~~

**type**: ``boolean`` **default**: ``true``

If ``true`` this option creates the form as "compound", meaning that it
can contain children and be a parent of other forms.

Most of the time you won't need to override this option.
You might want to control for it when creating a custom form type
with advanced rendering logic.

In a view a compound form is rendered as a ``<div>`` container or
a ``<form>`` element (the whole form is obviously a compound form).

Non-compound forms are always leaves in a form tree, they cannot have children.

A non-compound form is rendered as one of the html form elements: ``<input>``
(``TextType``, ``FileType``, ``HiddenType``), ``<textarea>`` (``TextareaType``)
or ``<select>`` (``ChoiceType``).

An interesting case is the ``ChoiceType``. With ``expanded=false`` it is a non-compound form
and is rendered as a ``<select>`` tag. With ``expanded=true`` the ``ChoiceType`` becomes a
compound form and is rendered as a set of radios or checkboxes.
