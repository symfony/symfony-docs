FileType Field
==============

The ``FileType`` represents a file input in your form.

+---------------------------+--------------------------------------------------------------------+
| Rendered as               | ``input`` ``file`` field                                           |
+---------------------------+--------------------------------------------------------------------+
| Default invalid message   | Please select a valid file.                                        |
+---------------------------+--------------------------------------------------------------------+
| Parent type               | :doc:`FormType </reference/forms/types/form>`                      |
+---------------------------+--------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\FileType` |
+---------------------------+--------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Basic Usage
-----------

Say you have this form definition::

    use Symfony\Component\Form\Extension\Core\Type\FileType;
    // ...

    $builder->add('attachment', FileType::class);

When the form is submitted, the ``attachment`` field will be an instance
of :class:`Symfony\\Component\\HttpFoundation\\File\\UploadedFile`. It can
be used to move the ``attachment`` file to a permanent location::

    use Symfony\Component\HttpFoundation\File\UploadedFile;

    public function upload(): Response
    {
        // ...

        if ($form->isSubmitted() && $form->isValid()) {
            $someNewFilename = ...

            $file = $form['attachment']->getData();
            $file->move($directory, $someNewFilename);

            // ...
        }

        // ...
    }

The ``move()`` method takes a directory and a file name as its arguments.
You might calculate the filename in one of the following ways::

    // use the original file name
    $file->move($directory, $file->getClientOriginalName());

    // when "webkitdirectory" upload was used
    // otherwise the value will be the same as getClientOriginalName
    // $file->move($directory, $file->getClientOriginalPath());

    // compute a random name and try to guess the extension (more secure)
    $extension = $file->guessExtension();
    if (!$extension) {
        // extension cannot be guessed
        $extension = 'bin';
    }
    $file->move($directory, rand(1, 99999).'.'.$extension);

Using the original name via ``getClientOriginalName()`` or ``getClientOriginalPath``
is not safe as it could have been manipulated by the end-user. Moreover, it can contain
characters that are not allowed in file names. You should sanitize the value
before using it directly.

Read :doc:`/controller/upload_file` for an example of how to manage a file
upload associated with a Doctrine entity.

Field Options
-------------

``multiple``
~~~~~~~~~~~~

**type**: ``Boolean`` **default**: ``false``

When set to true, the user will be able to upload multiple files at the same time.

Overridden Options
------------------

.. include:: /reference/forms/types/options/compound_type.rst.inc

``data_class``
~~~~~~~~~~~~~~

**type**: ``string`` **default**: :class:`Symfony\\Component\\HttpFoundation\\File\\File`

This option sets the appropriate file-related data mapper to be used by the type.

``empty_data``
~~~~~~~~~~~~~~

**type**: ``mixed`` **default**: ``null``

This option determines what value the field will return when the submitted
value is empty.

Overridden Options
------------------

.. include:: /reference/forms/types/options/invalid_message.rst.inc

Inherited Options
-----------------

These options inherit from the :doc:`FormType </reference/forms/types/form>`:

.. include:: /reference/forms/types/options/attr.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/help.rst.inc

.. include:: /reference/forms/types/options/help_attr.rst.inc

.. include:: /reference/forms/types/options/help_html.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/label_html.rst.inc

.. include:: /reference/forms/types/options/label_format.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc

Form Variables
--------------

========  ==========  ===============================================================================
Variable  Type        Usage
========  ==========  ===============================================================================
``type``  ``string``  The type variable is set to ``file``, in order to render as a file input field.
========  ==========  ===============================================================================
