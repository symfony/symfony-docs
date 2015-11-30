.. index::
   single: Forms; Types Reference

Form Types Reference
====================

.. versionadded:: 2.8
    To denote the form type, you have to use the fully qualified class name - like
    ``TextType::class`` in PHP 5.5+ or ``Symfony\Component\Form\Extension\Core\Type\TextType``.
    Before Symfony 2.8, you could use an alias for each type like ``text`` or
    ``date``. The old alias syntax will still work until Symfony 3.0. For more details,
    see the `2.8 UPGRADE Log`_.

.. toctree::
   :maxdepth: 1
   :hidden:

   types/text
   types/textarea
   types/email
   types/integer
   types/money
   types/number
   types/password
   types/percent
   types/search
   types/url
   types/range

   types/choice
   types/entity
   types/country
   types/language
   types/locale
   types/timezone
   types/currency

   types/date
   types/datetime
   types/time
   types/birthday

   types/checkbox
   types/file
   types/radio

   types/collection
   types/repeated

   types/hidden

   types/button
   types/reset
   types/submit

   types/form

A form is composed of *fields*, each of which are built with the help of
a field *type* (e.g. ``TextType``, ``ChoiceType``, etc). Symfony comes
standard with a large list of field types that can be used in your application.

Supported Field Types
---------------------

The following field types are natively available in Symfony:

.. include:: /reference/forms/types/map.rst.inc

.. _`2.8 UPGRADE Log`: https://github.com/symfony/symfony/blob/2.8/UPGRADE-2.8.md#form
