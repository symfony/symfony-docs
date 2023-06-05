Image
=====

The Image constraint works exactly like the :doc:`File </reference/constraints/File>`
constraint, except that its `mimeTypes`_ and `mimeTypesMessage`_ options
are automatically setup to work for image files specifically.

Additionally it has options so you can validate against the width and height
of the image.

See the :doc:`File </reference/constraints/File>` constraint for the bulk
of the documentation on this constraint.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Image`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\ImageValidator`
==========  ===================================================================

Basic Usage
-----------

This constraint is most commonly used on a property that will be rendered
in a form as a :doc:`FileType </reference/forms/types/file>` field. For
example, suppose you're creating an author form where you can upload a
"headshot" image for the author. In your form, the ``headshot`` property
would be a ``file`` type. The ``Author`` class might look as follows::

    // src/Entity/Author.php
    namespace App\Entity;

    use Symfony\Component\HttpFoundation\File\File;

    class Author
    {
        protected File $headshot;

        public function setHeadshot(File $file = null): void
        {
            $this->headshot = $file;
        }

        public function getHeadshot(): File
        {
            return $this->headshot;
        }
    }

To guarantee that the ``headshot`` ``File`` object is a valid image and
that it is between a certain size, add the following:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\HttpFoundation\File\File;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            #[Assert\Image(
                minWidth: 200,
                maxWidth: 400,
                minHeight: 200,
                maxHeight: 400,
            )]
            protected File $headshot;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            properties:
                headshot:
                    - Image:
                        minWidth: 200
                        maxWidth: 400
                        minHeight: 200
                        maxHeight: 400

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <property name="headshot">
                    <constraint name="Image">
                        <option name="minWidth">200</option>
                        <option name="maxWidth">400</option>
                        <option name="minHeight">200</option>
                        <option name="maxHeight">400</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Author
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('headshot', new Assert\Image([
                    'minWidth' => 200,
                    'maxWidth' => 400,
                    'minHeight' => 200,
                    'maxHeight' => 400,
                ]));
            }
        }

The ``headshot`` property is validated to guarantee that it is a real image
and that it is between a certain width and height.

You may also want to guarantee the ``headshot`` image to be square. In this
case you can disable portrait and landscape orientations as shown in the
following code:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\HttpFoundation\File\File;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            #[Assert\Image(
                allowLandscape: false,
                allowPortrait: false,
            )]
            protected File $headshot;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            properties:
                headshot:
                    - Image:
                        allowLandscape: false
                        allowPortrait: false

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <class name="App\Entity\Author">
            <property name="headshot">
                <constraint name="Image">
                    <option name="allowLandscape">false</option>
                    <option name="allowPortrait">false</option>
                </constraint>
            </property>
        </class>

    .. code-block:: php

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Author
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('headshot', new Assert\Image([
                    'allowLandscape' => false,
                    'allowPortrait' => false,
                ]));
            }
        }

You can mix all the constraint options to create powerful validation rules.

Options
-------

This constraint shares all of its options with the :doc:`File </reference/constraints/File>`
constraint. It does, however, modify two of the default option values and
add several other options.

``allowLandscape``
~~~~~~~~~~~~~~~~~~

**type**: ``Boolean`` **default**: ``true``

If this option is false, the image cannot be landscape oriented.

``allowLandscapeMessage``
~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The image is landscape oriented ({{ width }}x{{ height }}px).
Landscape oriented images are not allowed``

The error message if the image is landscape oriented and you set `allowLandscape`_ to ``false``.

You can use the following parameters in this message:

================  =============================================================
Parameter         Description
================  =============================================================
``{{ height }}``  The current height
``{{ width }}``   The current width
================  =============================================================

``allowPortrait``
~~~~~~~~~~~~~~~~~

**type**: ``Boolean`` **default**: ``true``

If this option is false, the image cannot be portrait oriented.

``allowPortraitMessage``
~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The image is portrait oriented ({{ width }}x{{ height }}px).
Portrait oriented images are not allowed``

The error message if the image is portrait oriented and you set `allowPortrait`_ to ``false``.

You can use the following parameters in this message:

================  =============================================================
Parameter         Description
================  =============================================================
``{{ height }}``  The current height
``{{ width }}``   The current width
================  =============================================================

``allowSquare``
~~~~~~~~~~~~~~~

**type**: ``Boolean`` **default**: ``true``

If this option is false, the image cannot be a square. If you want to force
a square image, then leave this option as its default ``true`` value
and set `allowLandscape`_ and `allowPortrait`_ both to ``false``.

``allowSquareMessage``
~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The image is square ({{ width }}x{{ height }}px).
Square images are not allowed``

The error message if the image is square and you set `allowSquare`_ to ``false``.

You can use the following parameters in this message:

================  =============================================================
Parameter         Description
================  =============================================================
``{{ height }}``  The current height
``{{ width }}``   The current width
================  =============================================================

``corruptedMessage``
~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The image file is corrupted.``

The error message when the `detectCorrupted`_ option is enabled and the image
is corrupted.

This message has no parameters.

``detectCorrupted``
~~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

If this option is true, the image contents are validated to ensure that the
image is not corrupted. This validation is done with PHP's :phpfunction:`imagecreatefromstring`
function, which requires the `PHP GD extension`_ to be enabled.

.. include:: /reference/constraints/_groups-option.rst.inc

``maxHeight``
~~~~~~~~~~~~~

**type**: ``integer``

If set, the height of the image file must be less than or equal to this
value in pixels.

``maxHeightMessage``
~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The image height is too big ({{ height }}px).
Allowed maximum height is {{ max_height }}px.``

The error message if the height of the image exceeds `maxHeight`_.

You can use the following parameters in this message:

====================  =========================================================
Parameter             Description
====================  =========================================================
``{{ height }}``      The current (invalid) height
``{{ max_height }}``  The maximum allowed height
====================  =========================================================

``maxPixels``
~~~~~~~~~~~~~

**type**: ``integer``

If set, the amount of pixels of the image file must be less than or equal to this
value.

``maxPixelsMessage``
~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The image has too many pixels ({{ pixels }} pixels).
Maximum amount expected is {{ max_pixels }} pixels.``

The error message if the amount of pixels of the image exceeds `maxPixels`_.

You can use the following parameters in this message:

====================  =========================================================
Parameter             Description
====================  =========================================================
``{{ height }}``      The current image height
``{{ max_pixels }}``  The maximum allowed amount of pixels
``{{ pixels }}``      The current amount of pixels
``{{ width }}``       The current image width
====================  =========================================================

``maxRatio``
~~~~~~~~~~~~

**type**: ``float``

If set, the aspect ratio (``width / height``) of the image file must be less
than or equal to this value.

``maxRatioMessage``
~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The image ratio is too big ({{ ratio }}).
Allowed maximum ratio is {{ max_ratio }}``

The error message if the aspect ratio of the image exceeds `maxRatio`_.

You can use the following parameters in this message:

===================  ==========================================================
Parameter            Description
===================  ==========================================================
``{{ max_ratio }}``  The maximum required ratio
``{{ ratio }}``      The current (invalid) ratio
===================  ==========================================================

``maxWidth``
~~~~~~~~~~~~

**type**: ``integer``

If set, the width of the image file must be less than or equal to this
value in pixels.

``maxWidthMessage``
~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The image width is too big ({{ width }}px).
Allowed maximum width is {{ max_width }}px.``

The error message if the width of the image exceeds `maxWidth`_.

You can use the following parameters in this message:

===================  ==========================================================
Parameter            Description
===================  ==========================================================
``{{ max_width }}``  The maximum allowed width
``{{ width }}``      The current (invalid) width
===================  ==========================================================

``mimeTypes``
~~~~~~~~~~~~~

**type**: ``array`` or ``string`` **default**: ``image/*``

You can find a list of existing image mime types on the `IANA website`_.

``mimeTypesMessage``
~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This file is not a valid image.``

If all the values of the `mimeTypes`_ option are a subset of ``image/*``, the
error message will be instead: ``The mime type of the file is invalid ({{ type }}). Allowed mime types are {{ types }}.``

.. include:: /reference/constraints/_parameters-mime-types-message-option.rst.inc

``minHeight``
~~~~~~~~~~~~~

**type**: ``integer``

If set, the height of the image file must be greater than or equal to this
value in pixels.

``minHeightMessage``
~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The image height is too small ({{ height }}px).
Minimum height expected is {{ min_height }}px.``

The error message if the height of the image is less than `minHeight`_.

You can use the following parameters in this message:

====================  =========================================================
Parameter             Description
====================  =========================================================
``{{ height }}``      The current (invalid) height
``{{ min_height }}``  The minimum required height
====================  =========================================================

``minPixels``
~~~~~~~~~~~~~

**type**: ``integer``

If set, the amount of pixels of the image file must be greater than or equal to this
value.

``minPixelsMessage``
~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The image has too few pixels ({{ pixels }} pixels).
Minimum amount expected is {{ min_pixels }} pixels.``

The error message if the amount of pixels of the image is less than `minPixels`_.

You can use the following parameters in this message:

====================  =========================================================
Parameter             Description
====================  =========================================================
``{{ height }}``      The current image height
``{{ min_pixels }}``  The minimum required amount of pixels
``{{ pixels }}``      The current amount of pixels
``{{ width }}``       The current image width
====================  =========================================================

``minRatio``
~~~~~~~~~~~~

**type**: ``float``

If set, the aspect ratio (``width / height``) of the image file must be greater
than or equal to this value.

``minRatioMessage``
~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The image ratio is too small ({{ ratio }}).
Minimum ratio expected is {{ min_ratio }}``

The error message if the aspect ratio of the image is less than `minRatio`_.

You can use the following parameters in this message:

===================  ==========================================================
Parameter            Description
===================  ==========================================================
``{{ min_ratio }}``  The minimum required ratio
``{{ ratio }}``      The current (invalid) ratio
===================  ==========================================================

``minWidth``
~~~~~~~~~~~~

**type**: ``integer``

If set, the width of the image file must be greater than or equal to this
value in pixels.

``minWidthMessage``
~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The image width is too small ({{ width }}px).
Minimum width expected is {{ min_width }}px.``

The error message if the width of the image is less than `minWidth`_.

You can use the following parameters in this message:

===================  ==========================================================
Parameter            Description
===================  ==========================================================
``{{ min_width }}``  The minimum required width
``{{ width }}``      The current (invalid) width
===================  ==========================================================

``sizeNotDetectedMessage``
~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The size of the image could not be detected.``

If the system is unable to determine the size of the image, this error will
be displayed. This will only occur when at least one of the size constraint
options has been set.

This message has no parameters.

.. _`IANA website`: https://www.iana.org/assignments/media-types/media-types.xhtml
.. _`PHP GD extension`: https://www.php.net/manual/en/book.image.php
