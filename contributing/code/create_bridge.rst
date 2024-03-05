Create a new bridge for Mailer, Translation or Notifier Component
=================================================================

.. admonition:: Screencast
    :class: screencast

    Do you prefer video tutorials? Check out the `Contributing Back To Symfony`_
    screencast series.

Creating a new bridge for a Symfony Component requires a number of changes to the
code and possibly the Symfony documentation. To help the contributors along the way,
we have created this checklist of things to keep in mind.

Update code
-----------

* Add the Transport factory to ``UnsupportedSchemeException`` and 
``UnsupportedSchemeExceptionTest`` of the component;
* Add the Transport factory to the ``FACTORY_CLASSES`` constant of the component's 
``Transport`` class;
* The ``#[\SensitiveParameter]`` attribute should be used for sensitive properties;
* Check if the classes defined in :class:`Symfony\\Bundle\\FrameworkBundle\\DependencyInjection\\FrameworkExtension` is the same as the ones 
declared in component's transports configuration;
* The year in the copyright should be the current year.

Update recipes
--------------

* TBD

Update documentation
--------------------

Bridges are not documented with code documentation, you can use the component's
readme file to add some informations about how to use it. However components
documentation requires some updates in order to stay up to date.

For a Mailer Bridge

* Add the new Bridge in third party transport's list;
* Add the available DSN formats in the relevant list;
* Indicate whether the new Bridge supports tags and metadata;
* If your Bridge supports Webhook add it in its documentation.

For a Notifier Bridge

* Add the new Bridge in the list for the relevant channel;
* If your Bridge supports Webhook add it in its documentation.

For a Translation Bridge

* Add the new Bridge in the translation providers list;
* Add the available DSN formats in the corresponding list.

.. _`Contributing Back To Symfony`: https://symfonycasts.com/screencast/contributing
