.. index::
   single: Forms; Fields; file

file Field Type
===============

The ``file`` type represents a file input in your form.

+-------------+---------------------------------------------------------------------+
| Rendered as | ``input`` ``file`` field                                            |
+-------------+---------------------------------------------------------------------+
| Options     | - ``type``                                                          |
+-------------+---------------------------------------------------------------------+
| Parent type | :doc:`form</reference/forms/types/form>`                            |
+-------------+---------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\FileType`  |
+-------------+---------------------------------------------------------------------+

Basic Usage
-----------

Files are uploaded to the server and set to a cached path by default.  To
move the file to a specific path, take the configuration example below:

.. code-block:: php

    $builder->add('attachment', 'file', array(
        'type'  => 'object',
    ));

Your entity will now be passed a ``File`` instance when it is bound.  You
can modify your entity setter like this:

.. code-block:: php

    public function setAttachment(File $attachment)
    {
        $attachment->move('/path/to/save/file');
        $this->attachment = basename($attachment->getPath());
    }

See the cookbook for examples of advanced usage.

Options
-------

* ``type`` [type: string, default: ``string``]
  The input will be returned from the widget in this format.  Valid options
  are ``string`` and ``object``.  If set  to ``object``, an instance of
  :class:Symfony\\Component\\HttpFoundation\\File\\File is returned.  If
  set to ``string``, the path of the newly uploaded file is returned.
