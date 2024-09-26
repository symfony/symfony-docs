sanitize_html
~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

When ``true``, the text input will be sanitized using the
:doc:`Symfony HTML Sanitizer component </html_sanitizer>` after the form is
submitted. This protects the form input against :ref:`XSS <xss-attacks>`, clickjacking and CSS
injection.

.. note::

    You must :ref:`install the HTML sanitizer component <html-sanitizer-installation>`
    to use this option.
