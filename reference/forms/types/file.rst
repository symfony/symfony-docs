.. index::
   single: Forms; Fields; file

file Field Type
===============

The ``file`` type represents a file input in your form.

+-------------+---------------------------------------------------------------------+
| Rendered as | ``input`` ``file`` field                                            |
+-------------+---------------------------------------------------------------------+
| Options     | none                                                                |
+-------------+---------------------------------------------------------------------+
| Parent type | :doc:`form</reference/forms/types/field>`                           |
+-------------+---------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\FileType`  |
+-------------+---------------------------------------------------------------------+

Basic Usage
-----------

Let's say you have this form definition:

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
            $form['attachment']->move($dir, $file);

            // ...
        }

        // ...
    }

The ``move()`` method takes a directory and a file name as its arguments::

    // use the original file name
    $file->move($dir, $this->getClientOriginalName());

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
