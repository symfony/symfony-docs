*   ``widget`` [type: string, default: ``choice``]
    Type of widget used for this form type.  Can be ``text`` or ``single_text`` or ``choice``.

      * ``text``: renders a three field input of type text (month, day, year).

      * ``single_text``: renders a single input of type text. User's input is validated
        based on the ``format`` option.

      * ``choice``: renders three select inputs.  The order of the selects
        is defined in the ``pattern`` option.
