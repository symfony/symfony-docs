Country
=======

Validates that a value is a valid two-letter country code.

+----------------+------------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                  |
+----------------+------------------------------------------------------------------------+
| Options        | - `message`_                                                           |
+----------------+------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Country`           |
+----------------+------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\CountryValidator`  |
+----------------+------------------------------------------------------------------------+

Basic Usage
-----------

.. configuration-block::

    .. code-block:: yaml

        # src/UserBundle/Resources/config/validation.yml
        Acme\UserBundle\Entity\User:
            properties:
                country:
                    - Country:

    .. code-block:: php-annotations

       // src/Acme/UserBundle/Entity/User.php
       namespace Acme\UserBundle\Entity;
       
       use Symfony\Component\Validator\Constraints as Assert;

       class User
       {
           /**
            * @Assert\Country
            */
            protected $country;
       }

Options
-------

message
~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid country``

This message is shown if the string is not a valid country code.
