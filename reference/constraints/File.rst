File
====

Validates that a value is a valid "file", which can be one of the following:

* A string (or object with a ``__toString()`` method) path to an existing
  file;
* A valid :class:`Symfony\\Component\\HttpFoundation\\File\\File` object
  (including objects of class :class:`Symfony\\Component\\HttpFoundation\\File\\UploadedFile`).

This constraint is commonly used in forms with the :doc:`FileType </reference/forms/types/file>`
form field.

.. tip::

    If the file you're validating is an image, try the :doc:`Image </reference/constraints/Image>`
    constraint.

+----------------+---------------------------------------------------------------------+
| Applies to     | :ref:`property or method <validation-property-target>`              |
+----------------+---------------------------------------------------------------------+
| Options        | - `maxSize`_                                                        |
|                | - `binaryFormat`_                                                   |
|                | - `mimeTypes`_                                                      |
|                | - `maxSizeMessage`_                                                 |
|                | - `mimeTypesMessage`_                                               |
|                | - `disallowEmptyMessage`_                                           |
|                | - `notFoundMessage`_                                                |
|                | - `notReadableMessage`_                                             |
|                | - `uploadIniSizeErrorMessage`_                                      |
|                | - `uploadFormSizeErrorMessage`_                                     |
|                | - `uploadErrorMessage`_                                             |
|                | - `payload`_                                                        |
+----------------+---------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\File`           |
+----------------+---------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\FileValidator`  |
+----------------+---------------------------------------------------------------------+

Basic Usage
-----------

This constraint is most commonly used on a property that will be rendered
in a form as a :doc:`FileType </reference/forms/types/file>` field. For
example, suppose you're creating an author form where you can upload a "bio"
PDF for the author. In your form, the ``bioFile`` property would be a ``file``
type. The ``Author`` class might look as follows::

    // src/AppBundle/Entity/Author.php
    namespace AppBundle\Entity;

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

To guarantee that the ``bioFile`` ``File`` object is valid and that it is
below a certain file size and a valid PDF, add the following:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Author.php
        namespace AppBundle\Entity;

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

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Author:
            properties:
                bioFile:
                    - File:
                        maxSize: 1024k
                        mimeTypes: [application/pdf, application/x-pdf]
                        mimeTypesMessage: Please upload a valid PDF

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Author">
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
                $metadata->addPropertyConstraint('bioFile', new Assert\File(array(
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

If set, the size of the underlying file must be below this file size in
order to be valid. The size of the file can be given in one of the following
formats:

+--------+-----------+-----------------+------+
| Suffix | Unit Name |      value      | e.g. |
+========+===========+=================+======+
|        | byte      |          1 byte | 4096 |
+--------+-----------+-----------------+------+
| k      | kilobyte  |     1,000 bytes | 200k |
+--------+-----------+-----------------+------+
| M      | megabyte  | 1,000,000 bytes |   2M |
+--------+-----------+-----------------+------+
| Ki     | kibibyte  |     1,024 bytes | 32Ki |
+--------+-----------+-----------------+------+
| Mi     | mebibyte  | 1,048,576 bytes |  8Mi |
+--------+-----------+-----------------+------+

For more information about the difference between binary and SI prefixes,
see `Wikipedia: Binary prefix`_.

binaryFormat
~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``null``

When ``true``, the sizes will be displayed in messages with binary-prefixed
units (KiB, MiB). When ``false``, the sizes will be displayed with SI-prefixed
units (kB, MB). When ``null``, then the binaryFormat will be guessed from
the value defined in the ``maxSize`` option.

For more information about the difference between binary and SI prefixes,
see `Wikipedia: Binary prefix`_.

mimeTypes
~~~~~~~~~

**type**: ``array`` or ``string``

If set, the validator will check that the mime type of the underlying file
is equal to the given mime type (if a string) or exists in the collection
of given mime types (if an array).

You can find a list of existing mime types on the `IANA website`_.

maxSizeMessage
~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The file is too large ({{ size }} {{ suffix }}). Allowed maximum size is {{ limit }} {{ suffix }}.``

The message displayed if the file is larger than the `maxSize`_ option.

mimeTypesMessage
~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The mime type of the file is invalid ({{ type }}). Allowed mime types are {{ types }}.``

The message displayed if the mime type of the file is not a valid mime type
per the `mimeTypes`_ option.

disallowEmptyMessage
~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``An empty file is not allowed.``

This constraint checks if the uploaded file is empty (i.e. 0 bytes). If it is,
this message is displayed.

notFoundMessage
~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The file could not be found.``

The message displayed if no file can be found at the given path. This error
is only likely if the underlying value is a string path, as a ``File`` object
cannot be constructed with an invalid file path.

notReadableMessage
~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The file is not readable.``

The message displayed if the file exists, but the PHP ``is_readable`` function
fails when passed the path to the file.

uploadIniSizeErrorMessage
~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The file is too large. Allowed maximum size is {{ limit }} {{ suffix }}.``

The message that is displayed if the uploaded file is larger than the ``upload_max_filesize``
``php.ini`` setting.

uploadFormSizeErrorMessage
~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The file is too large.``

The message that is displayed if the uploaded file is larger than allowed
by the HTML file input field.

uploadErrorMessage
~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The file could not be uploaded.``

The message that is displayed if the uploaded file could not be uploaded
for some unknown reason, such as the file upload failed or it couldn't be
written to disk.

.. include:: /reference/constraints/_payload-option.rst.inc

.. _`IANA website`: http://www.iana.org/assignments/media-types/index.html
.. _`Wikipedia: Binary prefix`: http://en.wikipedia.org/wiki/Binary_prefix
