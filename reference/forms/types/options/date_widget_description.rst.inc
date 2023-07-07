**type**: ``string`` **default**: ``choice``

The basic way in which this field should be rendered. Can be one of the
following:

* ``choice``: renders three select inputs. The order of the selects is defined
  in the `format`_ option.

* ``text``: renders a three field input of type ``text`` (month, day, year).

* ``single_text``: renders a single input of type ``date``. User's input
  is validated based on the `format`_ option.

.. deprecated:: 6.3

    Not setting a value explicitly for this option is deprecated since Symfony 6.3
    because the default value will change to ``single_text`` in Symfony 7.0.
