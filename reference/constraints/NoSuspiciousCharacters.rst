NoSuspiciousCharacters
======================

Validates that the given string does not contain characters used in spoofing
security attacks, such as invisible characters such as zero-width spaces or
characters that are visually similar.

"symfony.com" and "ѕymfony.com" look similar, but their first letter is different
(in the second string, the "s" is actually a `cyrillic small letter dze`_).
This can make a user think they'll navigate to Symfony's website, whereas it
would be somewhere else.

This is a kind of `spoofing attack`_ (called "IDN homograph attack"). It tries
to identify something as something else to exploit the resulting confusion.
This is why it is recommended to check user-submitted, public-facing identifiers
for suspicious characters in order to prevent such attacks.

Because Unicode contains such a large number of characters and incorporates the
varied writing systems of the world, incorrect usage can expose programs or
systems to possible security attacks.

That's why this constraint ensures strings or :phpclass:`Stringable`s do not
include any suspicious characters. As it leverages PHP's :phpclass:`Spoofchecker`,
the intl extension must be enabled to use it.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\NoSuspiciousCharacters`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\NoSuspiciousCharactersValidator`
==========  ===================================================================

Basic Usage
-----------

The following constraint will use different detection mechanisms to ensure that
the username is not spoofed:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/User.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class User
        {
            #[Assert\NoSuspiciousCharacters]
            private string $username;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\User:
            properties:
                username:
                    - NoSuspiciousCharacters: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\User">
                <property name="username">
                    <constraint name="NoSuspiciousCharacters"/>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/User.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class User
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('username', new Assert\NoSuspiciousCharacters());
            }
        }

.. include:: /reference/constraints/_empty-values-are-valid.rst.inc

Options
-------

``checks``
~~~~~~~~~~

**type**: ``integer`` **default**: all

This option is a bitmask of the checks you want to perform on the string:

* ``NoSuspiciousCharacters::CHECK_INVISIBLE`` checks for the presence of invisible
  characters such as zero-width spaces, or character sequences that are likely
  not to display, such as multiple occurrences of the same non-spacing mark.
* ``NoSuspiciousCharacters::CHECK_MIXED_NUMBERS`` (usable with ICU 58 or higher)
  checks for numbers from different numbering systems.
* ``NoSuspiciousCharacters::CHECK_HIDDEN_OVERLAY`` (usable with ICU 62 or higher)
  checks for combining characters hidden in their preceding one.

You can also configure additional requirements using :ref:`locales <locales>` and
:ref:`restrictionLevel <restrictionlevel>`.

``locales``
~~~~~~~~~~~

**type**: ``array`` **default**: :ref:`framework.enabled_locales <reference-enabled-locales>`

Restrict the string's characters to those normally used with the associated languages.

For example, the character "π" would be considered suspicious if you restricted the
locale to "English", because the Greek script is not associated with it.

Passing an empty array, or configuring :ref:`restrictionLevel <restrictionlevel>` to
``NoSuspiciousCharacters::RESTRICTION_LEVEL_NONE`` will disable this requirement.

``restrictionLevel``
~~~~~~~~~~~~~~~~~~~~

**type**: ``integer`` **default**: ``NoSuspiciousCharacters::RESTRICTION_LEVEL_MODERATE`` on ICU >= 58, otherwise ``NoSuspiciousCharacters::RESTRICTION_LEVEL_SINGLE_SCRIPT``

Configures the set of acceptable characters for the validated string through a
specified "level":

* ``NoSuspiciousCharacters::RESTRICTION_LEVEL_MINIMAL`` requires the string's
  characters to match :ref:`the configured locales <locales>`'.
* ``NoSuspiciousCharacters::RESTRICTION_LEVEL_MODERATE`` also requires the string
  to be `covered`_ by Latin and any one other `Recommended`_ or `Limited Use`_
  script, except Cyrillic, Greek, and Cherokee.
* ``NoSuspiciousCharacters::RESTRICTION_LEVEL_HIGH`` (usable with ICU 58 or higher)
  also requires the string to be `covered`_ by any of the following sets of scripts:

  * Latin + Han + Bopomofo (or equivalently: Latn + Hanb)
  * Latin + Han + Hiragana + Katakana (or equivalently: Latn + Jpan)
  * Latin + Han + Hangul (or equivalently: Latn + Kore)

* ``NoSuspiciousCharacters::RESTRICTION_LEVEL_SINGLE_SCRIPT`` also requires the
  string to be `single-script`_.
* ``NoSuspiciousCharacters::RESTRICTION_LEVEL_ASCII`` (usable with ICU 58 or higher)
  also requires the string's characters to be in the ASCII range.

You can accept all characters by setting this option to
``NoSuspiciousCharacters::RESTRICTION_LEVEL_NONE``.

.. include:: /reference/constraints/_groups-option.rst.inc

.. include:: /reference/constraints/_payload-option.rst.inc

.. _`cyrillic small letter dze`: https://graphemica.com/%D1%95
.. _`spoofing attack`: https://en.wikipedia.org/wiki/Spoofing_attack
.. _`single-script`: https://unicode.org/reports/tr39/#def-single-script
.. _`covered`: https://unicode.org/reports/tr39/#def-cover
.. _`Recommended`: https://www.unicode.org/reports/tr31/#Table_Recommended_Scripts
.. _`Limited Use`: https://www.unicode.org/reports/tr31/#Table_Limited_Use_Scripts
