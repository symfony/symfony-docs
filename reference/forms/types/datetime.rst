.. index::
   single: Forms; Fields; datetime

``datetime`` Field Type
=======================

A field to capture date and time input.
Can be rendered as text inputs or selects.

============  ======
Rendered as   can be various tags (see below)
Options       ``years`` ``months`` ``days`` ``hours`` ``minutes`` ``seconds``, ``date_widget``, ``date_format`` ``time_widget`` ``with_seconds`` ``data_timezone`` ``user_timezone``
Parent type   can be various tags (see below)
Class         :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\DatetimeType`
============  ======

.. include:: /reference/forms/types/options/years.rst.inc

.. include:: /reference/forms/types/options/months.rst.inc

.. include:: /reference/forms/types/options/days.rst.inc

.. include:: /reference/forms/types/options/hours.rst.inc

.. include:: /reference/forms/types/options/minutes.rst.inc

.. include:: /reference/forms/types/options/seconds.rst.inc

* ``date_widget`` [type: string, default: choice]
    Defines the ``widget`` option for field :class:`Symfony\\Component\\Form\\Type\\DateType`

* ``time_widget`` [type: string, default: choice]
    Defines the ``widget`` option for field :class:`Symfony\\Component\\Form\\Type\\TimeType`

.. include:: /reference/forms/types/options/with_seconds.rst.inc

.. include:: /reference/forms/types/options/data_timezone.rst.inc

.. include:: /reference/forms/types/options/user_timezone.rst.inc
