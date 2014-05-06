.. index::
    single: Symfony2 Twig extensions

Symfony2 Twig Extensions
========================

Twig is the default template engine for Symfony2. By itself, it already contains
a lot of build-in functions, filters, tags and tests (`http://twig.sensiolabs.org/documentation`_
then scroll to the bottom).

Symfony2 adds more custom extension on top of Twig to integrate some components
into the Twig templates. Below is information about all the custom functions,
filters, tags and tests that are added when using the Symfony2 Core Framework.

There may also be tags in bundles you use that aren't listed here.

Functions
---------

.. versionadded:: 2.4
    The ``expression`` function was introduced in Symfony 2.4.

+-----------------------------------------------------------------------+--------------------------------------------------------------------------------------------+
| Function Syntax                                                       | Usage                                                                                      |
+=======================================================================+============================================================================================+
| ``render(uri, options = {})``                                         | This will render the fragment for the given controller or URL                              |
| ``render(controller('B:C:a', {params}))``                             | For more information, see :ref:`templating-embedding-controller`.                          |
| ``render(path('route', {params}))``                                   |                                                                                            |
| ``render(url('route', {params}))``                                    |                                                                                            |
+-----------------------------------------------------------------------+--------------------------------------------------------------------------------------------+
| ``render_esi(controller('B:C:a', {params}))``                         | This will generate an ESI tag when possible or fallback to the ``render``                  |
| ``render_esi(url('route', {params}))``                                | behavior otherwise. For more information, see :ref:`templating-embedding-controller`.      |
| ``render_esi(path('route', {params}))``                               |                                                                                            |
+-----------------------------------------------------------------------+--------------------------------------------------------------------------------------------+
| ``render_hinclude(controller(...))``                                  | This will generates an Hinclude tag for the given controller or URL.                       |
| ``render_hinclude(url('route', {params}))``                           | For more information, see :ref:`templating-embedding-controller`.                          |
| ``render_hinclude(path('route', {params}))``                          |                                                                                            |
+-----------------------------------------------------------------------+--------------------------------------------------------------------------------------------+
| ``controller(attributes = {}, query = {})``                           | Used along with the ``render`` tag to refer to the controller that you want to render.     |
+-----------------------------------------------------------------------+--------------------------------------------------------------------------------------------+
| ``asset(path, packageName = null, absolute = false, version = null)`` | Get the public path of the asset, more information in                                      |
|                                                                       | ":ref:`book-templating-assets`".                                                           |
+-----------------------------------------------------------------------+--------------------------------------------------------------------------------------------+
| ``asset_version(packageName = null)``                                 | Get the current version of the package, more information in                                |
|                                                                       | ":ref:`book-templating-assets`".                                                           |
+-----------------------------------------------------------------------+--------------------------------------------------------------------------------------------+
| ``form(view, variables = {})``                                        | This will render the HTML of a complete form, more information in                          |
|                                                                       | in :ref:`the Twig Form reference<reference-forms-twig-form>`.                              |
+-----------------------------------------------------------------------+--------------------------------------------------------------------------------------------+
| ``form_start(view, variables = {})``                                  | This will render the HTML start tag of a form, more information in                         |
|                                                                       | in :ref:`the Twig Form reference<reference-forms-twig-start>`.                             |
+-----------------------------------------------------------------------+--------------------------------------------------------------------------------------------+
| ``form_end(view, variables = {})``                                    | This will render the HTML end tag of a form together with all fields that                  |
|                                                                       | have not been rendered yet, more information                                               |
|                                                                       | in :ref:`the Twig Form reference<reference-forms-twig-end>`.                               |
+-----------------------------------------------------------------------+--------------------------------------------------------------------------------------------+
| ``form_enctype(view)``                                                | This will render the required ``enctype="multipart/form-data"`` attribute                  |
|                                                                       | if the form contains at least one file upload field, more information in                   |
|                                                                       | in :ref:`the Twig Form reference <reference-forms-twig-enctype>`.                          |
+-----------------------------------------------------------------------+--------------------------------------------------------------------------------------------+
| ``form_widget(view, variables = {})``                                 | This will render a complete form or a specific HTML widget of a field,                     |
|                                                                       | more information in :ref:`the Twig Form reference <reference-forms-twig-widget>`.          |
+-----------------------------------------------------------------------+--------------------------------------------------------------------------------------------+
| ``form_errors(view)``                                                 | This will render any errors for the given field or the "global" errors,                    |
|                                                                       | more information in :ref:`the Twig Form reference <reference-forms-twig-errors>`.          |
+-----------------------------------------------------------------------+--------------------------------------------------------------------------------------------+
| ``form_label(view, label = null, variables = {})``                    | This will render the label for the given field, more information in                        |
|                                                                       | :ref:`the Twig Form reference <reference-forms-twig-label>`.                               |
+-----------------------------------------------------------------------+--------------------------------------------------------------------------------------------+
| ``form_row(view, variables = {})``                                    | This will render the row (the field's label, errors and widget) of the given               |
|                                                                       | field, more information in :ref:`the Twig Form reference <reference-forms-twig-row>`.      |
+-----------------------------------------------------------------------+--------------------------------------------------------------------------------------------+
| ``form_rest(view, variables = {})``                                   | This will render all fields that have not yet been rendered, more                          |
|                                                                       | information in :ref:`the Twig Form reference <reference-forms-twig-rest>`.                 |
+-----------------------------------------------------------------------+--------------------------------------------------------------------------------------------+
| ``csrf_token(intention)``                                             | This will render a CSRF token. Use this function if you want CSRF protection without       |
|                                                                       | creating a form                                                                            |
+-----------------------------------------------------------------------+--------------------------------------------------------------------------------------------+
| ``is_granted(role, object = null, field = null)``                     | This will return ``true`` if the current user has the required role, more                  |
|                                                                       | information in ":ref:`book-security-template`"                                             |
+-----------------------------------------------------------------------+--------------------------------------------------------------------------------------------+
| ``logout_path(key)``                                                  | This will generate the relative logout URL for the given firewall                          |
+-----------------------------------------------------------------------+--------------------------------------------------------------------------------------------+
| ``logout_url(key)``                                                   | Equal to ``logout_path(...)`` but this will generate an absolute URL                       |
+-----------------------------------------------------------------------+--------------------------------------------------------------------------------------------+
| ``path(name, parameters = {})``                                       | Get a relative URL for the given route, more information in                                |
|                                                                       | ":ref:`book-templating-pages`".                                                            |
+-----------------------------------------------------------------------+--------------------------------------------------------------------------------------------+
| ``url(name, parameters = {})``                                        | Equal to ``path(...)`` but it generates an absolute URL                                    |
+-----------------------------------------------------------------------+--------------------------------------------------------------------------------------------+
| ``expression(expression)``                                            | Creates an :class:`Symfony\\Component\\ExpressionLanguage\\Expression` in Twig. See        |
|                                                                       | ":ref:`Template Expressions <book-security-template-expression>`".                         |
+-----------------------------------------------------------------------+--------------------------------------------------------------------------------------------+

Filters
-------

+---------------------------------------------------------------------------------+-------------------------------------------------------------------+
| Filter Syntax                                                                   | Usage                                                             |
+=================================================================================+===================================================================+
| ``text|humanize``                                                               | Makes a technical name human readable (replaces underscores by    |
|                                                                                 | spaces and capitalizes the string).                               |
+---------------------------------------------------------------------------------+-------------------------------------------------------------------+
| ``text|trans(arguments = {}, domain = 'messages', locale = null)``              | This will translate the text into the current language, more      |
|                                                                                 | information in                                                    |
|                                                                                 | :ref:`Translation Filters <book-translation-filters>`.            |
+---------------------------------------------------------------------------------+-------------------------------------------------------------------+
| ``text|transchoice(count, arguments = {}, domain = 'messages', locale = null)`` | This will translate the text with pluralization, more information |
|                                                                                 | in :ref:`Translation Filters <book-translation-filters>`.         |
+---------------------------------------------------------------------------------+-------------------------------------------------------------------+
| ``variable|yaml_encode(inline = 0)``                                            | This will transform the variable text into a YAML syntax.         |
+---------------------------------------------------------------------------------+-------------------------------------------------------------------+
| ``variable|yaml_dump``                                                          | This will render a YAML syntax with their type.                   |
+---------------------------------------------------------------------------------+-------------------------------------------------------------------+
| ``classname|abbr_class``                                                        | This will render an ``abbr`` element with the short name of a     |
|                                                                                 | PHP class.                                                        |
+---------------------------------------------------------------------------------+-------------------------------------------------------------------+
| ``methodname|abbr_method``                                                      | This will render a PHP method inside a ``abbr`` element           |
|                                                                                 | (e.g. ``Symfony\Component\HttpFoundation\Response::getContent``   |
+---------------------------------------------------------------------------------+-------------------------------------------------------------------+
| ``arguments|format_args``                                                       | This will render a string with the arguments of a function and    |
|                                                                                 | their types.                                                      |
+---------------------------------------------------------------------------------+-------------------------------------------------------------------+
| ``arguments|format_args_as_text``                                               | Equal to ``[...]|format_args``, but it strips the tags.           |
+---------------------------------------------------------------------------------+-------------------------------------------------------------------+
| ``path|file_excerpt(line)``                                                     | This will render an excerpt of a code file around the given line. |
+---------------------------------------------------------------------------------+-------------------------------------------------------------------+
| ``path|format_file(line, text = null)``                                         | This will render a file path in a link.                           |
+---------------------------------------------------------------------------------+-------------------------------------------------------------------+
| ``exceptionMessage|format_file_from_text``                                      | Equal to ``format_file`` except it parsed the default PHP error   |
|                                                                                 | string into a file path (i.e. 'in foo.php on line 45')            |
+---------------------------------------------------------------------------------+-------------------------------------------------------------------+
| ``path|file_link(line)``                                                        | This will render a path to the correct file (and line number)     |
+---------------------------------------------------------------------------------+-------------------------------------------------------------------+

Tags
----

.. versionadded:: 2.4
    The stopwatch tag was introduced in Symfony 2.4.

+---------------------------------------------------+--------------------------------------------------------------------+
| Tag Syntax                                        | Usage                                                              |
+===================================================+====================================================================+
| ``{% form_theme form 'file' %}``                  | This will look inside the given file for overridden form blocks,   |
|                                                   | more information in :doc:`/cookbook/form/form_customization`.      |
+---------------------------------------------------+--------------------------------------------------------------------+
| ``{% trans with {variables} %}...{% endtrans %}`` | This will translate and render the text, more information in       |
|                                                   | :ref:`book-translation-tags`                                       |
+---------------------------------------------------+--------------------------------------------------------------------+
| ``{% transchoice count with {variables} %}``      | This will translate and render the text with pluralization, more   |
| ...                                               | information in :ref:`book-translation-tags`                        |
| ``{% endtranschoice %}``                          |                                                                    |
+---------------------------------------------------+--------------------------------------------------------------------+
| ``{% trans_default_domain language %}``           | This will set the default domain for message catalogues in the     |
|                                                   | current template                                                   |
+---------------------------------------------------+--------------------------------------------------------------------+
| ``{% stopwatch 'name' %}...{% endstopwatch %}``   | This will time the run time of the code inside it and put that on  |
|                                                   | the timeline of the WebProfilerBundle.                             |
+---------------------------------------------------+--------------------------------------------------------------------+

Tests
-----

+---------------------------------------------------+------------------------------------------------------------------------------+
| Test Syntax                                       | Usage                                                                        |
+===================================================+==============================================================================+
| ``selectedchoice(choice, selectedValue)``         | This will return ``true`` if the choice is selected for the given form value |
+---------------------------------------------------+------------------------------------------------------------------------------+

Global Variables
----------------

+-------------------------------------------------------+------------------------------------------------------------------------------------+
| Variable                                              | Usage                                                                              |
+=======================================================+====================================================================================+
| ``app`` *Attributes*: ``app.user``, ``app.request``,  | The ``app`` variable is available everywhere, and gives you quick                  |
| ``app.session``, ``app.environment``, ``app.debug``,  | access to many commonly needed objects. The ``app`` variable is                    |
| ``app.security``                                      | instance of :class:`Symfony\\Bundle\\FrameworkBundle\\Templating\\GlobalVariables` |
+-------------------------------------------------------+------------------------------------------------------------------------------------+

Symfony Standard Edition Extensions
-----------------------------------

The Symfony Standard Edition adds some bundles to the Symfony2 Core Framework.
Those bundles can have other Twig extensions:

* **Twig Extensions** includes some interesting extensions that do not belong to the
  Twig core. You can read more in `the official Twig Extensions documentation`_;
* **Assetic** adds the ``{% stylesheets %}``, ``{% javascripts %}`` and
  ``{% image %}`` tags. You can read more about them in
  :doc:`the Assetic Documentation </cookbook/assetic/asset_management>`.

.. _`the official Twig Extensions documentation`: http://twig.sensiolabs.org/doc/extensions/index.html
.. _`http://twig.sensiolabs.org/documentation`: http://twig.sensiolabs.org/documentation
