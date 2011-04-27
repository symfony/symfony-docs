.. index::
   single: Forms; Fields; language

``language`` Field Type
=======================

The ``language`` type is a subset of the ``ChoiceType`` that allows the user
to select from a large list of languages. As an added bonus, the language names
are displayed in the language of the user.

The "value" for each locale is either the two letter ISO639-1 *language* code
(e.g. ``fr``).

.. note::

   The locale of your user is guessed using `Locale::getDefault()`_

Unlike the ``choice`` type, you don't need to specify a ``choices`` or
``choice_list`` option as the field type automatically uses a large list
of languages. You *can* specify either of these options manually, but then
you should just use the ``choice`` type directly.

============  ======
Rendered as   can be various tags (see :ref:`forms-reference-choice-tags`)
Options       ``multiple``, ``expanded``, ``preferred_choices``, ``required``, ``label``, ``read_only``, ``error_bubbling``
Parent type   :doc:`choice</reference/forms/types/choice>`
Class         :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\LanguageType`
============  ======

Options
-------

.. include:: /reference/forms/types/options/multiple.rst.inc

.. include:: /reference/forms/types/options/expanded.rst.inc

.. include:: /reference/forms/types/options/preferred_choices.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. _`Locale::getDefault()`: http://php.net/manual/en/locale.getdefault.php