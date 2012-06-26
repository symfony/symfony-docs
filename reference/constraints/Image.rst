Image
=====

The Image constraint works exactly like the :doc:`File</reference/constraints/File>`
constraint, except that its `mimeTypes`_ and `mimeTypesMessage` options are
automatically setup to work for image files specifically.

Additionally, as of Symfony 2.1, it has options so you can validate against
the width and height of the image.

See the :doc:`File</reference/constraints/File>` constraint for the bulk of
the documentation on this constraint.

+----------------+----------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                |
+----------------+----------------------------------------------------------------------+
| Options        | - `mimeTypes`_                                                       |
|                | - `minWidth`_                                                        |
|                | - `maxWidth`_                                                        |
|                | - `maxHeight`_                                                       |
|                | - `minHeight`_                                                       |
|                | - `mimeTypesMessage`_                                                |
|                | - `sizeNotDetectedMessage`_                                          |
|                | - `maxWidthMessage`_                                                 |
|                | - `minWidthMessage`_                                                 |
|                | - `maxHeightMessage`_                                                |
|                | - `minHeightMessage`_                                                |
|                | - See :doc:`File</reference/constraints/File>` for inherited options |
+----------------+----------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\File`            |
+----------------+----------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\FileValidator`   |
+----------------+----------------------------------------------------------------------+

Basic Usage
-----------

This constraint is most commonly used on a property that will be rendered
in a form as a :doc:`file</reference/forms/types/file>` form type. For example,
suppose you're creating an author form where you can upload a "headshot"
image for the author. In your form, the ``headshot`` property would be a
``file`` type. The ``Author`` class might look as follows::

    // src/Acme/BlogBundle/Entity/Author.php
    namespace Acme\BlogBundle\Entity;

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

To guarantee that the ``headshot`` ``File`` object is a valid image and that
it is between a certain size, add the following:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/BlogBundle/Resources/config/validation.yml
        Acme\BlogBundle\Entity\Author
            properties:
                headshot:
                    - Image:
                        minWidth: 200
                        maxWidth: 400
                        minHeight: 200
                        maxHeight: 400
                        

    .. code-block:: php-annotations

        // src/Acme/BlogBundle/Entity/Author.php
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\File(
             *     minWidth = 200,
             *     maxWidth = 400,
             *     minHeight = 200,
             *     maxHeight = 400,
             * )
             */
            protected $headshot;
        }

    .. code-block:: xml

        <!-- src/Acme/BlogBundle/Resources/config/validation.xml -->
        <class name="Acme\BlogBundle\Entity\Author">
            <property name="headshot">
                <constraint name="File">
                    <option name="minWidth">200</option>
                    <option name="maxWidth">400</option>
                    <option name="minHeight">200</option>
                    <option name="maxHeight">400</option>
                </constraint>
            </property>
        </class>

    .. code-block:: php

        // src/Acme/BlogBundle/Entity/Author.php
        // ...

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints\File;

        class Author
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('headshot', new File(array(
                    'minWidth' => 200,
                    'maxWidth' => 400,
                    'minHeight' => 200,
                    'maxHeight' => 400,
                )));
            }
        }

The ``headshot`` property is validated to guarantee that it is a real image
and that it is between a certain width and height.

Options
-------

This constraint shares all of its options with the :doc:`File</reference/constraints/File>`
constraint. It does, however, modify two of the default option values and
add several other options.

mimeTypes
~~~~~~~~~

**type**: ``array`` or ``string`` **default**: ``image/*``

You can find a list of existing image mime types on the `IANA website`_

mimeTypesMessage
~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This file is not a valid image``

.. versionadded:: 2.1
    All of the min/max width/height options are new to Symfony 2.1.

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

sizeNotDetectedMessage
~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The size of the image could not be detected``

If the system is unable to determine the size of the image, this error will
be displayed. This will only occur when at least one of the four size constraint
options has been set.

maxWidthMessage
~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The image width is too big ({{ width }}px). Allowed maximum width is {{ max_width }}px``

The error message if the width of the image exceeds `maxWidth`_.

minWidthMessage
~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The image width is too small ({{ width }}px). Minimum width expected is {{ min_width }}px``

The error message if the width of the image is less than `minWidth`_.

maxHeightMessage
~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The image height is too big ({{ height }}px). Allowed maximum height is {{ max_height }}px``

The error message if the height of the image exceeds `maxHeight`_.

minHeightMessage
~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The image height is too small ({{ height }}px). Minimum height expected is {{ min_height }}px``

The error message if the height of the image is less than `minHeight`_.

.. _`IANA website`: http://www.iana.org/assignments/media-types/image/index.html
