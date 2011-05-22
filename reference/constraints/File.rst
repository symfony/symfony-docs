File
====

Validates that a value is the path to an existing file.

.. code-block:: yaml

    properties:
        filename:
            - File: ~

Options
-------

* ``maxSize``: The maximum allowed file size. Can be provided in bytes, kilobytes
  (with the suffix "k") or megabytes (with the suffix "M")
* ``mimeTypes``: One or more allowed mime types
* ``notFoundMessage``: The error message if the file was not found
* ``notReadableMessage``: The error message if the file could not be read
* ``maxSizeMessage``: The error message if ``maxSize`` validation fails
* ``mimeTypesMessage``: The error message if ``mimeTypes`` validation fails

Example: Validating the file size and mime type
-----------------------------------------------

In this example we use the ``File`` constraint to verify that the file does
not exceed a maximum size of 128 kilobytes and is a PDF document.

.. configuration-block::

    .. code-block:: yaml

        properties:
            filename:
                - File: { maxSize: 128k, mimeTypes: [application/pdf, application/x-pdf] }

    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/validation.xml -->
        <class name="Acme\HelloBundle\Author">
            <property name="filename">
                <constraint name="File">
                    <option name="maxSize">128k</option>
                    <option name="mimeTypes">
                        <value>application/pdf</value>
                        <value>application/x-pdf</value>
                    </option>
                </constraint>
            </property>
        </class>

    .. code-block:: php-annotations

        // src/Acme/HelloBundle/Author.php
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\File(maxSize = "128k", mimeTypes = {
             *   "application/pdf",
             *   "application/x-pdf"
             * })
             */
            private $filename;
        }

    .. code-block:: php

        // src/Acme/HelloBundle/Author.php
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints\File;
        
        class Author
        {
            private $filename;
            
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('filename', new File(array(
                    'maxSize' => '128k',
                    'mimeTypes' => array(
                        'application/pdf',
                        'application/x-pdf',
                    ),
                )));
            }
        }

When you validate the object with a file that doesn't satisfy one of these
constraints, a proper error message is returned by the validator:

.. code-block:: text

    Acme\HelloBundle\Author.filename:
        The file is too large (150 kB). Allowed maximum size is 128 kB
