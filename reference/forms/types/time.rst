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
|                      | - `with_minutes`_                                                           |
|                      | - `with_seconds`_                                                           |
|                      | - `hours`_                                                                  |
|                      | - `minutes`_                                                                |
|                      | - `seconds`_                                                                |
|                      | - `model_timezone`_                                                         |
|                      | - `view_timezone`_                                                          |
|                      | - `empty_value`_                                                            |
+----------------------+-----------------------------------------------------------------------------+
| Overridden Options   | - `by_reference`_                                                           |
|                      | - `error_bubbling`_                                                         |
+----------------------+-----------------------------------------------------------------------------+
| Inherited            | - `invalid_message`_                                                        |
| options              | - `invalid_message_parameters`_                                             |
|                      | - `data`_                                                                   |
|                      | - `read_only`_                                                              |
|                      | - `disabled`_                                                               |
|                      | - `mapped`_                                                                 |
|                      | - `inherit_data`_                                                           |
|                      | - `error_mapping`_                                                          |
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
field as two different choice fields:

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

* ``choice``: renders one, two (default) or three select inputs (hour, minute,
  second), depending on the `with_minutes`_ and `with_seconds`_ options.

* ``text``: renders one, two (default) or three text inputs (hour, minute,
  second), depending on the `with_minutes`_ and `with_seconds`_ options.

* ``single_text``: renders a single input of type ``time``. User's input will
  be validated against the form ``hh:mm`` (or ``hh:mm:ss`` if using seconds).

.. caution::

    Combining the widget type ``single_text`` and the `with_minutes`_ option
    set to ``false`` can cause unexpected behavior in the client as the input
    type ``time`` might not support selecting an hour only.

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

.. include:: /reference/forms/types/options/with_minutes.rst.inc

.. include:: /reference/forms/types/options/with_seconds.rst.inc

.. include:: /reference/forms/types/options/hours.rst.inc

.. include:: /reference/forms/types/options/minutes.rst.inc

.. include:: /reference/forms/types/options/seconds.rst.inc

.. include:: /reference/forms/types/options/model_timezone.rst.inc

.. include:: /reference/forms/types/options/view_timezone.rst.inc

.. include:: /reference/forms/types/options/empty_value.rst.inc

Overridden Options
------------------

by_reference
~~~~~~~~~~~~

**default**: ``false``

The ``DateTime`` classes are treated as immutable objects.

error_bubbling
~~~~~~~~~~~~~~

**default**: ``false``

Inherited Options
-----------------

These options inherit from the :doc:`form </reference/forms/types/form>` type:

.. include:: /reference/forms/types/options/invalid_message.rst.inc

.. include:: /reference/forms/types/options/invalid_message_parameters.rst.inc

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/inherit_data.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

Form Variables
--------------

+--------------+-------------+----------------------------------------------------------------------+
| Variable     | Type        | Usage                                                                |
+==============+=============+======================================================================+
| widget       | ``mixed``   | The value of the `widget`_ option.                                   |
+--------------+-------------+----------------------------------------------------------------------+
| with_minutes | ``Boolean`` | The value of the `with_minutes`_ option.                             |
+--------------+-------------+----------------------------------------------------------------------+
| with_seconds | ``Boolean`` | The value of the `with_seconds`_ option.                             |
+--------------+-------------+----------------------------------------------------------------------+
| type         | ``string``  | Only present when widget is ``single_text`` and HTML5 is activated,  |
|              |             | contains the input type to use (``datetime``, ``date`` or ``time``). |
+--------------+-------------+----------------------------------------------------------------------+
