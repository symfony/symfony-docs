File
====

Validates that a value is a valid "file", which can be one of the following:

* A string (or object with a ``__toString()`` method) path to an existing
  file;
* A valid :class:`Symfony\\Component\\HttpFoundation\\File\\File` object
  (including objects of :class:`Symfony\\Component\\HttpFoundation\\File\\UploadedFile` class).

This constraint is commonly used in forms with the :doc:`FileType </reference/forms/types/file>`
form field.

.. seealso::

    If the file you're validating is an image, try the :doc:`Image </reference/constraints/Image>`
    constraint.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Options     - `binaryFormat`_
            - `disallowEmptyMessage`_
            - `groups`_
            - `maxSize`_
            - `maxSizeMessage`_
            - `mimeTypes`_
            - `mimeTypesMessage`_
            - `notFoundMessage`_
            - `notReadableMessage`_
            - `payload`_
            - `uploadCantWriteErrorMessage`_
            - `uploadErrorMessage`_
            - `uploadExtensionErrorMessage`_
            - `uploadFormSizeErrorMessage`_
            - `uploadIniSizeErrorMessage`_
            - `uploadNoFileErrorMessage`_
            - `uploadNoTmpDirErrorMessage`_
            - `uploadPartialErrorMessage`_
Class       :class:`Symfony\\Component\\Validator\\Constraints\\File`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\FileValidator`
==========  ===================================================================

Basic Usage
-----------

This constraint is most commonly used on a property that will be rendered
in a form as a :doc:`FileType </reference/forms/types/file>` field. For
example, suppose you're creating an author form where you can upload a "bio"
PDF for the author. In your form, the ``bioFile`` property would be a ``file``
type. The ``Author`` class might look as follows::

    // src/Entity/Author.php
    namespace App\Entity;

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

        // src/Entity/Author.php
        namespace App\Entity;

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

        # config/validator/validation.yaml
        App\Entity\Author:
            properties:
                bioFile:
                    - File:
                        maxSize: 1024k
                        mimeTypes: [application/pdf, application/x-pdf]
                        mimeTypesMessage: Please upload a valid PDF

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
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

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Author
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('bioFile', new Assert\File([
                    'maxSize' => '1024k',
                    'mimeTypes' => [
                        'application/pdf',
                        'application/x-pdf',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid PDF',
                ]));
            }
        }

The ``bioFile`` property is validated to guarantee that it is a real file.
Its size and mime type are also validated because the appropriate options
have been specified.

.. include:: /reference/constraints/_empty-values-are-valid.rst.inc

Options
-------

.. include:: /reference/constraints/_file-constraint-options.rst.inc
