post_max_size_message
~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The uploaded file was too large. Please try to upload a smaller file.``

This is the validation error message that's used if submitted POST form
data exceeds ``php.ini``'s ``post_max_size`` directive. The ``{{ max }}``
placeholder can be used to display the allowed size.

.. note::

    Validating the ``post_max_size`` only happens on the root form.
