Image
=====

The Image constraint works exactly like the :doc:`File </reference/constraints/File>`
constraint, except that its `mimeTypes`_ and `mimeTypesMessage`_ options
are automatically setup to work for image files specifically.

Additionally it has options so you can validate against the width and height
of the image.

See the :doc:`File </reference/constraints/File>` constraint for the bulk
of the documentation on this constraint.

+----------------+-----------------------------------------------------------------------+
| Applies to     | :ref:`property or method <validation-property-target>`                |
+----------------+-----------------------------------------------------------------------+
| Options        | - `mimeTypes`_                                                        |
|                | - `minWidth`_                                                         |
|                | - `maxWidth`_                                                         |
|                | - `maxHeight`_                                                        |
|                | - `minHeight`_                                                        |
|                | - `maxRatio`_                                                         |
|                | - `minRatio`_                                                         |
|                | - `allowSquare`_                                                      |
|                | - `allowLandscape`_                                                   |
|                | - `allowPortrait`_                                                    |
|                | - `mimeTypesMessage`_                                                 |
|                | - `sizeNotDetectedMessage`_                                           |
|                | - `maxWidthMessage`_                                                  |
|                | - `minWidthMessage`_                                                  |
|                | - `maxHeightMessage`_                                                 |
|                | - `minHeightMessage`_                                                 |
|                | - `maxRatioMessage`_                                                  |
|                | - `minRatioMessage`_                                                  |
|                | - `allowSquareMessage`_                                               |
|                | - `allowLandscapeMessage`_                                            |
|                | - `allowPortraitMessage`_                                             |
|                | - See :doc:`File </reference/constraints/File>` for inherited options |
+----------------+-----------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Image`            |
+----------------+-----------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\ImageValidator`   |
+----------------+-----------------------------------------------------------------------+

Basic Usage
-----------

This constraint is most commonly used on a property that will be rendered
in a form as a :doc:`FileType </reference/forms/types/file>` field. For
example, suppose you're creating an author form where you can upload a
"headshot" image for the author. In your form, the ``headshot`` property
would be a ``file`` type. The ``Author`` class might look as follows::

    // src/AppBundle/Entity/Author.php
    namespace AppBundle\Entity;

    use Symfony\Component\HttpFoundation\File\File;

    class Author
    {
        protected $headshot;

        public function setHeadshot(File $file = null)
        {
            $this->headshot = $file;
        }

        public function getHeadshot()
        {
            return $this->headshot;
        }
    }

To guarantee that the ``headshot`` ``File`` object is a valid image and
that it is between a certain size, add the following:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Author.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Image(
             *     minWidth = 200,
             *     maxWidth = 400,
             *     minHeight = 200,
             *     maxHeight = 400
             * )
             */
            protected $headshot;
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Author
            properties:
                headshot:
                    - Image:
                        minWidth: 200
                        maxWidth: 400
                        minHeight: 200
                        maxHeight: 400

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Author">
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

        // src/AppBundle/Entity/Author.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('headshot', new Assert\Image(array(
                    'minWidth' => 200,
                    'maxWidth' => 400,
                    'minHeight' => 200,
                    'maxHeight' => 400,
                )));
            }
        }

The ``headshot`` property is validated to guarantee that it is a real image
and that it is between a certain width and height.

You may also want to guarantee the ``headshot`` image to be square. In this
case you can disable portrait and landscape orientations as shown in the
following code:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Author.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Image(
             *     allowLandscape = false,
             *     allowPortrait = false
             * )
             */
            protected $headshot;
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Author
            properties:
                headshot:
                    - Image:
                        allowLandscape: false
                        allowPortrait: false

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <class name="AppBundle\Entity\Author">
            <property name="headshot">
                <constraint name="Image">
                    <option name="allowLandscape">false</option>
                    <option name="allowPortrait">false</option>
                </constraint>
            </property>
        </class>

    .. code-block:: php

        // src/AppBundle/Entity/Author.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('headshot', new Assert\Image(array(
                    'allowLandscape'    => false,
                    'allowPortrait'     => false,
                )));
            }
        }

You can mix all the constraint options to create powerful validation rules.

Options
-------

This constraint shares all of its options with the :doc:`File </reference/constraints/File>`
constraint. It does, however, modify two of the default option values and
add several other options.

mimeTypes
~~~~~~~~~

**type**: ``array`` or ``string`` **default**: ``image/*``

You can find a list of existing image mime types on the `IANA website`_.

mimeTypesMessage
~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This file is not a valid image.``

minWidth
~~~~~~~~

**type**: ``integer``

If set, the width of the image file must be greater than or equal to this
value in pixels.

maxWidth
~~~~~~~~

**type**: ``integer``

If set, the width of the image file must be less than or equal to this
value in pixels.

minHeight
~~~~~~~~~

**type**: ``integer``

If set, the height of the image file must be greater than or equal to this
value in pixels.

maxHeight
~~~~~~~~~

**type**: ``integer``

If set, the height of the image file must be less than or equal to this
value in pixels.

maxRatio
~~~~~~~~

**type**: ``float``

If set, the aspect ratio (``width / height``) of the image file must be less
than or equal to this value.

minRatio
~~~~~~~~

**type**: ``float``

If set, the aspect ratio (``width / height``) of the image file must be greater
than or equal to this value.

allowSquare
~~~~~~~~~~~

**type**: ``Boolean`` **default**: ``true``

If this option is false, the image cannot be a square. If you want to force
a square image, then set leave this option as its default ``true`` value
and set `allowLandscape`_ and `allowPortrait`_ both to ``false``.

allowLandscape
~~~~~~~~~~~~~~

**type**: ``Boolean`` **default**: ``true``

If this option is false, the image cannot be landscape oriented.

allowPortrait
~~~~~~~~~~~~~

**type**: ``Boolean`` **default**: ``true``

If this option is false, the image cannot be portrait oriented.

sizeNotDetectedMessage
~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The size of the image could not be detected.``

If the system is unable to determine the size of the image, this error will
be displayed. This will only occur when at least one of the size constraint
options has been set.

maxWidthMessage
~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The image width is too big ({{ width }}px).
Allowed maximum width is {{ max_width }}px.``

The error message if the width of the image exceeds `maxWidth`_.

minWidthMessage
~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The image width is too small ({{ width }}px).
Minimum width expected is {{ min_width }}px.``

The error message if the width of the image is less than `minWidth`_.

maxHeightMessage
~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The image height is too big ({{ height }}px).
Allowed maximum height is {{ max_height }}px.``

The error message if the height of the image exceeds `maxHeight`_.

minHeightMessage
~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The image height is too small ({{ height }}px).
Minimum height expected is {{ min_height }}px.``

The error message if the height of the image is less than `minHeight`_.

maxRatioMessage
~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The image ratio is too big ({{ ratio }}).
Allowed maximum ratio is {{ max_ratio }}``

The error message if the aspect ratio of the image exceeds `maxRatio`_.

minRatioMessage
~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The image ratio is too small ({{ ratio }}).
Minimum ratio expected is {{ min_ratio }}``

The error message if the aspect ratio of the image is less than `minRatio`_.

allowSquareMessage
~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The image is square ({{ width }}x{{ height }}px).
Square images are not allowed``

The error message if the image is square and you set `allowSquare`_ to ``false``.

allowLandscapeMessage
~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The image is landscape oriented ({{ width }}x{{ height }}px).
Landscape oriented images are not allowed``

The error message if the image is landscape oriented and you set `allowLandscape`_ to ``false``.

allowPortraitMessage
~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The image is portrait oriented ({{ width }}x{{ height }}px).
Portrait oriented images are not allowed``

The error message if the image is portrait oriented and you set `allowPortrait`_ to ``false``.

.. _`IANA website`: http://www.iana.org/assignments/media-types/image/index.html
