* ``required`` [type: Boolean, default: true]
    The ``required`` option can be used to render an `HTML5 required attribute`_.
    Note that this is independent from validation: if you include the required
    attribute on the field type but omit any required validation, the object
    will appear to be valid to your application with a blank value. In other
    words, this is a *nice* feature that will add client-side validation for
    browsers that support HTML5. It's not, however, a replacement for true
    server-side validation.

.. _`HTML5 required attribute`: http://diveintohtml5.org/forms.html