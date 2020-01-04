``compound``
~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

A compound form can be either an entire ``<form>`` element or a group of form fields (rendered
for example inside a ``<div>`` or ``<tr>`` container elements). Compound forms use the
DataMapperInterface to initialize their children or to write back their submitted data.

A simple (non-compound) form is rendered as any of these HTML elements: ``<input>``
(``TextType``, ``FileType``, ``HiddenType``), ``<textarea>`` (``TextareaType``)
or ``<select>`` (``ChoiceType``).

Some core types like date related types or the ``ChoiceType`` are simple or compound depending
on other options (such as ``expanded`` or ``widget``). They will either behave as a simple text
field or as a group of text or choice fields.
