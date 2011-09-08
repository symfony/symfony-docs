Locale
======

Validates that a value is a valid locale.

The "value" for each locale is either the two letter ISO639-1 *language* code
(e.g. ``fr``), or the language code followed by an underscore (``_``), then
the ISO3166 *country* code (e.g. ``fr_FR`` for French/France).

+----------------+------------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                  |
+----------------+------------------------------------------------------------------------+
| Options        | - `message`_                                                           |
+----------------+------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Locale`            |
+----------------+------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\LocaleValidator`   |
+----------------+------------------------------------------------------------------------+

Basic Usage
-----------

.. configuration-block::

    .. code-block:: yaml

        # src/UserBundle/Resources/config/validation.yml
        Acme\UserBundle\Entity\User:
            properties:
                locale:
                    - Locale:

    .. code-block:: php-annotations

       // src/Acme/UserBundle/Entity/User.php
       namespace Acme\UserBundle\Entity;
       
       use Symfony\Component\Validator\Constraints as Assert;

       class User
       {
           /**
            * @Assert\Locale
            */
            protected $locale;
       }

Options
-------

message
~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid locale``

This message is shown if the string is not a valid locale.
