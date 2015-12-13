Web Assets
==========

Web assets are things like CSS, JavaScript and image files that make the
frontend of your site look and work great. Symfony developers have traditionally
stored these assets in the ``Resources/public/`` directory of each bundle.

.. best-practice::

    Store your assets in the ``web/`` directory.

Scattering your web assets across tens of different bundles makes it more
difficult to manage them. Your designers' lives will be much easier if all
the application assets are in one location.

Templates also benefit from centralizing your assets, because the links are
much more concise:

.. code-block:: html+twig

    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/main.css') }}" />

    {# ... #}

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>

.. note::

    Keep in mind that ``web/`` is a public directory and that anything stored
    here will be publicly accessible, including all the original asset files
    (e.g. Sass, LESS and CoffeeScript files).

Using Assetic
-------------

.. include:: /cookbook/assetic/_standard_edition_warning.inc

These days, you probably can't simply create static CSS and JavaScript files
and include them in your template. Instead, you'll probably want to combine
and minify these to improve client-side performance. You may also want to
use LESS or Sass (for example), which means you'll need some way to process
these into CSS files.

A lot of tools exist to solve these problems, including pure-frontend (non-PHP)
tools like GruntJS.

.. best-practice::

    Use Assetic to compile, combine and minimize web assets, unless you're
    comfortable with frontend tools like GruntJS.

:doc:`Assetic </cookbook/assetic/asset_management>` is an asset manager capable
of compiling assets developed with a lot of different frontend technologies
like LESS, Sass and CoffeeScript. Combining all your assets with Assetic is a
matter of wrapping all the assets with a single Twig tag:

.. code-block:: html+twig

    {% stylesheets
        'css/bootstrap.min.css'
        'css/main.css'
        filter='cssrewrite' output='css/compiled/app.css' %}
        <link rel="stylesheet" href="{{ asset_url }}" />
    {% endstylesheets %}

    {# ... #}

    {% javascripts
        'js/jquery.min.js'
        'js/bootstrap.min.js'
        output='js/compiled/app.js' %}
        <script src="{{ asset_url }}"></script>
    {% endjavascripts %}

Frontend-Based Applications
---------------------------

Recently, frontend technologies like AngularJS have become pretty popular
for developing frontend web applications that talk to an API.

If you are developing an application like this, you should use the tools
that are recommended by the technology, such as Bower and GruntJS. You should
develop your frontend application separately from your Symfony backend (even
separating the repositories if you want).

Learn More about Assetic
------------------------

Assetic can also minimize CSS and JavaScript assets
:doc:`using UglifyCSS/UglifyJS </cookbook/assetic/uglifyjs>` to speed up your
websites. You can even :doc:`compress images </cookbook/assetic/jpeg_optimize>`
with Assetic to reduce their size before serving them to the user. Check out
the `official Assetic documentation`_ to learn more about all the available
features.

.. _`official Assetic documentation`: https://github.com/kriswallsmith/assetic
