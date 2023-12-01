Copying & Referencing Images with Webpack Encore
================================================

Need to reference a static file - like the path to an image for an ``img`` tag?
That can be tricky if you store your assets outside of the public document root.
Fortunately, depending on your situation, there is a solution!

Referencing Images from Inside a Webpacked JavaScript File
----------------------------------------------------------

To reference an image tag from inside a JavaScript file, *require* the file:

.. code-block:: javascript

    // assets/app.js

    // returns the final, public path to this file
    // path is relative to this file - e.g. assets/images/logo.png
    import logoPath from '../images/logo.png';

    let html = `<img src="${logoPath}" alt="ACME logo">`;

When you ``require`` (or ``import``) an image file, Webpack copies it into your
output directory and returns the final, *public* path to that file.

Referencing Image files from a Template
---------------------------------------

To reference an image file from outside of a JavaScript file that's processed by
Webpack - like a template - you can use the ``copyFiles()`` method to copy those
files into your final output directory. First enable it in ``webpack.config.js``:

.. code-block:: diff

      // webpack.config.js

      Encore
          // ...
          .setOutputPath('public/build/')

    +     .copyFiles({
    +         from: './assets/images',
    +
    +         // optional target path, relative to the output dir
    +         to: 'images/[path][name].[ext]',
    +
    +         // if versioning is enabled, add the file hash too
    +         //to: 'images/[path][name].[hash:8].[ext]',
    +
    +         // only copy files matching this pattern
    +         //pattern: /\.(png|jpg|jpeg)$/
    +     })

Then restart Encore. When you do, it will give you a command you can run to
install any missing dependencies. After running that command and restarting
Encore, you're done!

This will copy all files from ``assets/images`` into ``public/build/images``.
If you have :doc:`versioning enabled <versioning>`, the copied files will
include a hash based on their content.

To render inside Twig, use the ``asset()`` function:

.. code-block:: html+twig

    {# assets/images/logo.png was copied to public/build/images/logo.png #}
    <img src="{{ asset('build/images/logo.png') }}" alt="ACME logo">

    {# assets/images/subdir/logo.png was copied to public/build/images/subdir/logo.png #}
    <img src="{{ asset('build/images/subdir/logo.png') }}" alt="ACME logo">

Make sure you've enabled the :ref:`json_manifest_path <load-manifest-files>` option,
which tells the ``asset()`` function to read the final paths from the ``manifest.json``
file. If you're not sure what path argument to pass to the ``asset()`` function,
find the file in ``manifest.json`` and use the *key* as the argument.
