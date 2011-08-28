.. index::
   single: Forms; Fields; time

time Field Type
===============

A field to capture time input.

This can be rendered as a text field, a series of text fields (e.g. hour,
minute, second) or a series of select fields. The underlying data can be stored
as a ``DateTime`` object, a string, a timestamp or an array.

+----------------------+-----------------------------------------------------------------------------+
| Underlying Data Type | can be ``DateTime``, string, timestamp, or array (see the ``input`` option) |
+----------------------+-----------------------------------------------------------------------------+
| Rendered as          | can be various tags (see below)                                             |
+----------------------+-----------------------------------------------------------------------------+
| Options              | - `widget`_                                                                 |
|                      | - `input`_                                                                  |
|                      | - `with_seconds`_                                                           |
|                      | - `hours`_                                                                  |
|                      | - `minutes`_                                                                |
|                      | - `seconds`_                                                                |
|                      | - `data_timezone`_                                                          |
|                      | - `user_timezone`_                                                          |
+----------------------+-----------------------------------------------------------------------------+
| Parent type          | form                                                                        |
+----------------------+-----------------------------------------------------------------------------+
| Class                | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\TimeType`          |
+----------------------+-----------------------------------------------------------------------------+

Basic Usage
-----------

This field type is highly configurable, but easy to use. The most important
options are ``input`` and ``widget``.

Suppose that you have a ``startTime`` field whose underlying time data is a
``DateTime`` object. The following configures the ``time`` type for that
field as three different choice fields:

.. code-block:: php

    $builder->add('startTime', 'time', array(
        'input'  => 'datetime',
        'widget' => 'choice',
    ));

The ``input`` option *must* be changed to match the type of the underlying
date data. For example, if the ``startTime`` field's data were a unix timestamp,
you'd need to set ``input`` to ``timestamp``:

.. code-block:: php

    $builder->add('startTime', 'time', array(
        'input'  => 'timestamp',
        'widget' => 'choice',
    ));

The field also supports an ``array`` and ``string`` as valid ``input`` option
values.

Field Options
-------------

widget
~~~~~~

**type**: ``string`` **default**: ``choice``

The basic way in which this field should be rendered. Can be one of the following:

* ``choice``: renders two (or three if `with_seconds`_ is true) select inputs.

* ``text``: renders a two or three text inputs (hour, minute, second).

* ``single_text``: renders a single input of type text. User's input will
  be validated against the form ``hh:mm`` (or ``hh:mm:ss`` if using seconds).

input
~~~~~

**type**: ``string`` **default**: ``datetime``

The format of the *input* data - i.e. the format that the date is stored on
your underlying object. Valid values are:

* ``string`` (e.g. ``12:17:26``)
* ``datetime`` (a ``DateTime`` object)
* ``array`` (e.g. ``array('hour' => 12, 'minute' => 17, 'second' => 26)``)
* ``timestamp`` (e.g. ``1307232000``)

The value that comes back from the form will also be normalized back into
this format.

.. include:: /reference/forms/types/options/with_seconds.rst.inc

.. include:: /reference/forms/types/options/hours.rst.inc

.. include:: /reference/forms/types/options/minutes.rst.inc

.. include:: /reference/forms/types/options/seconds.rst.inc

.. include:: /reference/forms/types/options/data_timezone.rst.inc

.. include:: /reference/forms/types/options/user_timezone.rst.inc
