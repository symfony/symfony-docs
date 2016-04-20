choice_translation_domain
~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 2.7
    The ``choice_translation_domain`` option was introduced in Symfony 2.7.

**type**: ``string``, ``boolean`` or ``null``

This option determines if the choice values should be translated and in which
translation domain.

The values of the ``choice_translation_domain`` option can be ``true`` (reuse the current
translation domain), ``false`` (disable translation), ``null`` (uses the parent translation
domain or the default domain) or a string which represents the exact translation
domain to use.
