.. index::
   single: Forms; Types Reference

Form Types Reference
====================

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
   types/tel
   types/color

   types/choice
   types/entity
   types/country
   types/language
   types/locale
   types/timezone
   types/currency

   types/date
   types/dateinterval
   types/datetime
   types/time
   types/birthday
   types/week

   types/checkbox
   types/file
   types/radio

   types/uuid
   types/ulid

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
