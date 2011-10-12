File
====

Validates that a value is a valid "file", which can be one of the following:

* A string (or object with a ``__toString()`` method) path to an existing file;

* A valid :class:`Symfony\\Component\\HttpFoundation\\File\\File` object
  (including objects of class :class:`Symfony\\Component\\HttpFoundation\\File\\UploadedFile`).

This constraint is commonly used in forms with the :doc:`file</reference/forms/types/file>`
form type.

.. tip::

    If the file you're validating is an image, try the :doc:`Image</reference/constraints/Image>`
    constraint.

+----------------+---------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`               |
+----------------+---------------------------------------------------------------------+
| Options        | - `maxSize`_                                                        |
|                | - `mimeTypes`_                                                      |
|                | - `maxSizeMessage`_                                                 |
|                | - `mimeTypesMessage`_                                               |
|                | - `notFoundMessage`_                                                |
|                | - `notReadableMessage`_                                             |
|                | - `uploadIniSizeErrorMessage`_                                      |
|                | - `uploadFormSizeErrorMessage`_                                     |
|                | - `uploadErrorMessage`_                                             |
+----------------+---------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\File`           |
+----------------+---------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\FileValidator`  |
+----------------+---------------------------------------------------------------------+

Basic Usage
-----------

This constraint is most commonly used on a property that will be rendered
in a form as a :doc:`file</reference/forms/types/file>` form type. For example,
suppose you're creating an author form where you can upload a "bio" PDF for
the author. In your form, the ``bioFile`` property would be a ``file`` type.
The ``Author`` class might look as follows::

    // src/Acme/BlogBundle/Entity/Author.php
    namespace Acme\BlogBundle\Entity;

    use Symfony\Component\HttpFoundation\File\File;

    class Author
    {
        protected $bioFile;

        public function setBioFile(File $file = null)
        {
            $this->bioFile = $file;
        }

        public function getBioFile()
        {
            return $this->bioFile;
        }
    }

To guarantee that the ``bioFile`` ``File`` object is valid, and that it is
below a certain file size and a valid PDF, add the following:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/BlogBundle/Resources/config/validation.yml
        Acme\BlogBundle\Entity\Author
            properties:
                bioFile:
                    - File:
                        maxSize: 1024k
                        mimeTypes: [application/pdf, application/x-pdf]
                        mimeTypesMessage: Please upload a valid PDF
                        

    .. code-block:: php-annotations

        // src/Acme/BlogBundle/Entity/Author.php
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\File(
             *     maxSize = "1024k",
             *     mimeTypes = {"application/pdf", "application/x-pdf"},
             *     mimeTypesMessage = "Please upload a valid PDF"
             * )
             */
            protected $bioFile;
        }

    .. code-block:: xml

        <!-- src/Acme/BlogBundle/Resources/config/validation.xml -->
        <class name="Acme\BlogBundle\Entity\Author">
            <property name="bioFile">
                <constraint name="File">
                    <option name="maxSize">1024k</option>
                    <option name="mimeTypes">
                        <value>application/pdf</value>
                        <value>application/x-pdf</value>
                    </option>
                    <option name="mimeTypesMessage">Please upload a valid PDF</option>
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
                $metadata->addPropertyConstraint('bioFile', new File(array(
                    'maxSize' => '1024k',
                    'mimeTypes' => array(
                        'application/pdf',
                        'application/x-pdf',
                    ),
                    'mimeTypesMessage' => 'Please upload a valid PDF',
                )));
            }
        }

The ``bioFile`` property is validated to guarantee that it is a real file.
Its size and mime type are also validated because the appropriate options
have been specified.

Options
-------

maxSize
~~~~~~~

**type**: ``mixed``

If set, the size of the underlying file must be below this file size in order
to be valid. The size of the file can be given in one of the following formats:

* **bytes**: To specify the ``maxSize`` in bytes, pass a value that is entirely
  numeric (e.g. ``4096``);

* **kilobytes**: To specify the ``maxSize`` in kilobytes, pass a number and
  suffix it with a lowercase "k" (e.g. ``200k``);

* **megabytes**: To specify the ``maxSize`` in megabytes, pass a number and
  suffix it with a capital "M" (e.g. ``4M``).

mimeTypes
~~~~~~~~~

**type**: ``array`` or ``string``

If set, the validator will check that the mime type of the underlying file
is equal to the given mime type (if a string) or exists in the collection
of given mime types (if an array).

maxSizeMessage
~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The file is too large ({{ size }}). Allowed maximum size is {{ limit }}``

The message displayed if the file is larger than the `maxSize`_ option.

mimeTypesMessage
~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The mime type of the file is invalid ({{ type }}). Allowed mime types are {{ types }}``

The message displayed if the mime type of the file is not a valid mime type
per the `mimeTypes`_ option.

notFoundMessage
~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The file could not be found``

The message displayed if no file can be found at the given path. This error
is only likely if the underlying value is a string path, as a ``File`` object
cannot be constructed with an invalid file path.

notReadableMessage
~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The file is not readable``

The message displayed if the file exists, but the PHP ``is_readable`` function
fails when passed the path to the file.

uploadIniSizeErrorMessage
~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The file is too large. Allowed maximum size is {{ limit }}``

The message that is displayed if the uploaded file is larger than the ``upload_max_filesize``
PHP.ini setting.

uploadFormSizeErrorMessage
~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The file is too large``

The message that is displayed if the uploaded file is larger than allowed
by the HTML file input field.

uploadErrorMessage
~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The file could not be uploaded``

The message that is displayed if the uploaded file could not be uploaded
for some unknown reason, such as the file upload failed or it couldn't be written
to disk.