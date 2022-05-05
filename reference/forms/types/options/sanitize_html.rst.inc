sanitize_html
~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

.. versionadded:: 6.1

    The ``sanitize_html`` option was introduced in Symfony 6.1.

When ``true``, the text input will be sanitized using the
:doc:`Symfony HTML Sanitizer component </html_sanitizer>` after the form is
submitted. This protects the form input against XSS, clickjacking and CSS
injection.

.. note::

    You must :ref:`install the HTML sanitizer component <html-sanitizer-installation>`
    to use this option.
