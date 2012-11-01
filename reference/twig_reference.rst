.. index::
    single: Symfony2 Twig extensions

Symfony2 Twig Extensions
========================

Twig is the default template engine for Symfony2. It contains a lot of build-in
functions, filters and blocks. Symfony2 created some custom extension on
top of Twig to integrate some components into the Twig templates.

Below is information about all the custom functions, filters and blocks
that are defined by the Symfony2 Core Framework. There may also be tags
in bundles that are included within the Symfony Standard Edition, or in
bundles you use, that aren't listed here.

Functions
---------

+---------------------------------------------+---------------------------------------------------------------------------+
| Function Syntax                             | Usage                                                                     |
+=============================================+===========================================================================+
| ``asset(path, packageName)``                | Get the public path of the asset, more information in                     |
|                                             | :ref:`book-templating-assets`.                                            |
+---------------------------------------------+---------------------------------------------------------------------------+
| ``asset_version(packageName)``              | Get the current version of the package, more information in               |
|                                             | :ref:`book-templating-assets`.                                            |
+---------------------------------------------+---------------------------------------------------------------------------+
| ``form_encrypte(form)``                     | This will render the required ``enctype="multipart/form-data"`` attribute |
|                                             | if the form contains at least one file upload field, more information in  |
|                                             | :ref:`reference-forms-twig-enctype`.                                      |
+---------------------------------------------+---------------------------------------------------------------------------+
| ``form_widget(form.name, variables)``       | This will render a complete form or a specific HTML widget of a field,    |
|                                             | more information in :ref:`reference-forms-twig-widget`.                   |
+---------------------------------------------+---------------------------------------------------------------------------+
| ``form_errors(form.name)``                  | This will render any errors for the given field or the "global" errors,   |
|                                             | more information in :ref:`reference-forms-twig-errors`.                   |
+---------------------------------------------+---------------------------------------------------------------------------+
| ``form_label(form.name, label, variables)`` | This will render the label for the given field, more information in       |
|                                             | :ref:`reference-forms-twig-label`.                                        |
+---------------------------------------------+---------------------------------------------------------------------------+
| ``form_row(form.name, variables)``          | This will render the row (the field's label, errors and widget) of the    |
|                                             | given field, more information in :ref:`reference-forms-twig-row`.         |
+---------------------------------------------+---------------------------------------------------------------------------+
| ``form_rest(form, variables)``              | This will render all fields that have not yet been rendered, more         |
|                                             | information in :ref:`reference-forms-twig-rest`.                          |
+---------------------------------------------+---------------------------------------------------------------------------+
| ``_form_is_choice_group(label)``            | This will return ``true`` if the label is a choice group.                 |
+---------------------------------------------+---------------------------------------------------------------------------+
| ``_form_is_choice_selected(form, label)``   | This will return ``true`` if the given choice is selected.                |
+---------------------------------------------+---------------------------------------------------------------------------+
| ``is_granted(role)``                        | This will return ``true`` if the current user has the required role, more |
|                                             | information in :ref:`book-security-template`                              |
+---------------------------------------------+---------------------------------------------------------------------------+
| ``path(name, parameters)``                  | Get a relative url for the given route, more information in               |
|                                             | :ref:`book-templating-pages`.                                             |
+---------------------------------------------+---------------------------------------------------------------------------+
| ``url(name, parameters)``                   | Equal to ``path(...)`` but it generates an absolute url                   |
+---------------------------------------------+---------------------------------------------------------------------------+
