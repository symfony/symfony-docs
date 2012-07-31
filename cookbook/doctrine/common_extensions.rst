.. index::
   single: Doctrine; Common extensions

How to use Doctrine Extensions: Timestampable, Sluggable, Translatable, etc.
============================================================================

Doctrine2 is very flexible, and the community has already created a series
of useful Doctrine extensions to help you with common entity-related tasks.

One library in particular - the `DoctrineExtensions`_ library - provides integration
functionality for `Sluggable`_, `Translatable`_, `Timestampable`_, `Loggable`_,
`Tree`_ and `Sortable`_ behaviors.

The usage for each of these extensions is explained in that repository.

However, to install/activate each extension you must register and activate an
:doc:`Event Listener</cookbook/doctrine/event_listeners_subscribers>`.
To do this, you have two options:

#. Use the `StofDoctrineExtensionsBundle`_, which integrates the above library.

#. Implement this services directly by following the documentation for integration
   with Symfony2: `Install Gedmo Doctrine2 extensions in Symfony2`_

.. _`DoctrineExtensions`: https://github.com/l3pp4rd/DoctrineExtensions
.. _`StofDoctrineExtensionsBundle`: https://github.com/stof/StofDoctrineExtensionsBundle
.. _`Sluggable`: https://github.com/l3pp4rd/DoctrineExtensions/blob/master/doc/sluggable.md
.. _`Translatable`: https://github.com/l3pp4rd/DoctrineExtensions/blob/master/doc/translatable.md
.. _`Timestampable`: https://github.com/l3pp4rd/DoctrineExtensions/blob/master/doc/timestampable.md
.. _`Loggable`: https://github.com/l3pp4rd/DoctrineExtensions/blob/master/doc/loggable.md
.. _`Tree`: https://github.com/l3pp4rd/DoctrineExtensions/blob/master/doc/tree.md
.. _`Sortable`: https://github.com/l3pp4rd/DoctrineExtensions/blob/master/doc/sortable.md
.. _`Install Gedmo Doctrine2 extensions in Symfony2`: https://github.com/l3pp4rd/DoctrineExtensions/blob/master/doc/symfony2.md