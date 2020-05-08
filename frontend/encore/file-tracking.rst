Rendering Multiple Templates in a Request: Emails, PDFs
=======================================================

When you render your script or link tags with the Twig helper functions
(e.g. ``encore_entry_link_tags()``), WebpackEncoreBundle is smart enough
not to repeat the same JavaScript or CSS file within the same request.
This prevents you from having duplicate ``<link>`` or ``<script>`` tags
if you render multiple entries that rely on the same file.

But if you're purposely rendering multiple templates in the same
request - e.g. rendering a template for a PDF or to send an email -
then this can cause problems: the later templates won't include any
``<link>`` or ``<script>`` tags that were rendered in an earlier template.

The easiest solution is to render the raw CSS and JavaScript using
a special function that *always* returns the full source, even for files
that were already rendered.

This works especially well in emails thanks to the
`inline_css`_ filter:

.. code-block:: html+twig

    {% apply inline_css(encore_entry_css_source('my_entry')) %}
        <div>
            Hi! The CSS from my_entry will be converted into
            inline styles on any HTML elements inside.
        </div>
    {% endapply %}

Or you can just render the source directly.

.. code-block:: html+twig

    <style>
        {{ encore_entry_css_source('my_entry')|raw }}
    </style>

    <script>
        {{ encore_entry_js_source('my_entry')|raw }}
    </script>

If you can't use these `encore_entry_*_source` functions, you can instead
manually disable and enable "file tracking":

.. code-block:: html+twig

    {% do encore_disable_file_tracking() %}
        {{ encore_entry_link_tags('entry1') }}
        {{ encore_entry_script_tags('entry1') }}
    {% do encore_enable_file_tracking() %}

With this, *all* JS and CSS files for `entry1` will be rendered and
this won't affect any other Twig templates rendered in the request.

Resetting the File Tracking
---------------------------

If using ``encore_disable_file_tracking()`` won't work for you for some
reason, you can also "reset" EncoreBundle's internal cache so that the
bundle re-renders CSS or JS files that it previously rendered. For
example, in a controller::


    // src/Controller/SomeController.php

    use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;

    class SomeController
    {
        public function index(EntrypointLookupInterface $entrypointLookup)
        {
            $entrypointLookup->reset();
            // render a template

            $entrypointLookup->reset();
            // render another template

            // ...
        }
    }

If you have multiple builds, you can also autowire
``Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollectionInterface``
and use it to get the ``EntrypointLookupInterface`` object for any build.

.. _`inline_css`: https://github.com/twigphp/cssinliner-extra
