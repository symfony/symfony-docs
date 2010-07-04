File
====

Validates that a value is the path to an existing file.

    [yml]
    properties:
      filename:
        - File: ~

Options
-------

  * `maxSize`: The maximum allowed file size. Can be provided in bytes, kilobytes
    (with the suffix "k") or megabytes (with the suffix "M")
  * `mimeTypes`: One or more allowed mime types
  * `notFoundMessage`: The error message if the file was not found
  * `notReadableMessage`: The error message if the file could not be read
  * `maxSizeMessage`: The error message if `maxSize` validation fails
  * `mimeTypesMessage`: The error message if `mimeTypes` validation fails
  
Example 1: Validating the file size and mime type (YAML)
--------------------------------------------------------

In this example we use the `File` constraint to verify that the file does
not exceed a maximum size of 128 kilobytes and is a PDF document.

**Listing 1**

    [yml]
    properties:
      filename:
        - File: { maxSize: 128k, mimeTypes: [application/pdf, application/x-pdf] }
        
When you validate the object with a file that doesn't satisfy one of these
constraints, a proper error message is returned by the validator.

    Application\HelloBundle\Author.filename:
        The file is too large (150 kB). Allowed maximum size is 128 kB
        
Example 2: Validating the file size and mime type (XML)
-------------------------------------------------------

This listing shows the example of Listing 1 using XML.

**Listing 2**

    [xml]
    <!-- Application/HelloBundle/Resources/config/validation.xml -->
    <class name="Application\HelloBundle\Author">
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
    
Example 3: Validating the file size and mime type (Docblock Annotations)
------------------------------------------------------------------------

As always it is possible to map Listing 1 with annotations, too.

**Listing 3**

    [php]
    // Application/HelloBundle/Author.php
    class Author
    {
        /**
         * @Validation({ 
         *   @File(maxSize = "128k", mimeTypes = {
         *     "application/pdf",
         *     "application/x-pdf"
         *   })
         * })
         */
        private $filename;
    }
