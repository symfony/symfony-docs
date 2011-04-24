.. index::
   single: Forms; Fields; birthday

``birthday`` Field Type
=======================

A field for users to input their birthday.  

Can be rendered as a single text box, three text boxes (for month, day, and year),
or three select boxes (month, day, and year)

This type is essentially the same as DateType, with a more appropriate default for the ``years`` option.  
The ``years`` option defaults to 120 years ago to the current year.

============  ======
Rendered as   can be various tags (see below)
Options       ``years``, see :class:`Symfony\\Component\\Form\\Type\\DateType`
Parent type   :doc:`date</reference/forms/types/date>`
Class         :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\BirthdayType`
============  ======
