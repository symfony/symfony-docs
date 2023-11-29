Async Code Splitting with Webpack Encore
========================================

When you require/import a JavaScript or CSS module, Webpack compiles that code into
the final JavaScript or CSS file. Usually, that's exactly what you want. But what
if you only need to use a piece of code under certain conditions? For example,
what if you want to use `video.js`_ to play a video, but only once a user has
clicked a link:

.. code-block:: javascript

    // assets/app.js

    import $ from 'jquery';
    // a fictional "large" module (e.g. it imports video.js internally)
    import VideoPlayer from './components/VideoPlayer';

    $('.js-open-video').on('click', function() {
        // use the larger VideoPlayer module
        const player = new VideoPlayer('some-element');
    });

In this example, the VideoPlayer module and everything it imports will be packaged
into the final, built JavaScript file, even though it may not be very common for
someone to actually need it. A better solution is to use `dynamic imports`_: load
the code via AJAX when it's needed:

.. code-block:: javascript

    // assets/app.js

    import $ from 'jquery';

    $('.js-open-video').on('click', function() {
        // you could start a loading animation here

        // use import() as a function - it returns a Promise
        import('./components/VideoPlayer').then(({ default: VideoPlayer }) => {
            // you could stop a loading animation here

            // use the larger VideoPlayer module
            const player = new VideoPlayer('some-element');

        }).catch(error => 'An error occurred while loading the component');
    });

By using ``import()`` like a function, the module will be downloaded async and
the ``.then()`` callback will be executed when it's finished. The ``VideoPlayer``
argument to the callback will be the loaded module. In other words, it works like
normal AJAX calls! Behind the scenes, Webpack will package the ``VideoPlayer`` module
into a separate file (e.g. ``0.js``) so it can be downloaded. All the details are
handled for you.

The ``{ default: VideoPlayer }`` part may look strange. When using the async
import, your ``.then()`` callback is passed an object, where the *actual* module
is on a ``.default`` key. There are reasons why this is done, but it does look
quirky. The ``{ default: VideoPlayer }`` code makes sure that the ``VideoPlayer``
module we want is read from this ``.default`` property.

For more details and configuration options, see `dynamic imports`_ on Webpack's
documentation.

.. _`video.js`: https://videojs.com/
.. _`dynamic imports`: https://webpack.js.org/guides/code-splitting/#dynamic-imports
