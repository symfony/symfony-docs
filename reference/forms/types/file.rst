.. index::
   single: Forms; Fields; file

file Field Type
===============

The ``file`` type represents a file input in your form.

+-------------+---------------------------------------------------------------------+
| Rendered as | ``input`` ``file`` field                                            |
+-------------+---------------------------------------------------------------------+
| Inherited   | - `required`_                                                       |
| options     | - `label`_                                                          |
|             | - `read_only`_                                                      |
|             | - `disabled`_                                                       |
|             | - `error_bubbling`_                                                 |
+-------------+---------------------------------------------------------------------+
| Parent type | :doc:`form</reference/forms/types/form>`                            |
+-------------+---------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\FileType`  |
+-------------+---------------------------------------------------------------------+

Basic Usage
-----------

Say you have this form definition:

.. code-block:: php

    $builder->add('attachment', 'file');

.. caution::

    Don't forget to add the ``enctype`` attribute in the form tag: ``<form
    action="#" method="post" {{ form_enctype(form) }}>``.

When the form is submitted, the ``attachment`` field will be an instance of
:class:`Symfony\\Component\\HttpFoundation\\File\\UploadedFile`. It can be
used to move the ``attachment`` file to a permanent location:

.. code-block:: php

    use Symfony\Component\HttpFoundation\File\UploadedFile;

    public function uploadAction()
    {
        // ...

        if ($form->isValid()) {
            $someNewFilename = ...

            $form['attachment']->getData()->move($dir, $someNewFilename);

            // ...
        }

        // ...
    }

The ``move()`` method takes a directory and a file name as its arguments.
You might calculate the filename in one of the following ways::

    // use the original file name
    $file->move($dir, $file->getClientOriginalName());

    // compute a random name and try to guess the extension (more secure)
    $extension = $file->guessExtension();
    if (!$extension) {
        // extension cannot be guessed
        $extension = 'bin';
    }
    $file->move($dir, rand(1, 99999).'.'.$extension);

Using the original name via ``getClientOriginalName()`` is not safe as it
could have been manipulated by the end-user. Moreover, it can contain
characters that are not allowed in file names. You should sanitize the name
before using it directly.

Read the :doc:`cookbook </cookbook/doctrine/file_uploads>` for an example of
how to manage a file upload associated with a Doctrine entity.

Inherited options
-----------------

These options inherit from the :doc:`form</reference/forms/types/form>` type:

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc