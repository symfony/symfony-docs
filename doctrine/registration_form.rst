How to Implement a Registration Form
====================================

This article has been removed because it only explained things that are
already explained in other articles. Specifically, to implement a registration
form you must:

#. :ref:`Define a class to represent users <create-user-class>`;
#. :doc:`Create a form </forms>` to ask for the registration information (you can
   generate this with the ``make:registration-form`` command provided by the `MakerBundle`_);
#. Create :doc:`a controller </controller>` to :ref:`process the form <processing-forms>`;
#. :ref:`Protect some parts of your application <security-access-control>` so that
   only registered users can access to them.

.. _`MakerBundle`: https://symfony.com/doc/current/bundles/SymfonyMakerBundle/index.html
