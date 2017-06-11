Passing Information from Twig to JavaScript
===========================================

In Symfony applications, you may find that you need to pass some dynamic data
(e.g. user information) from Twig to your JavaScript code. One great way to pass
dynamic configuration is by storing information in ``data`` attributes and reading
them later in JavaScript. For example:

.. code-block:: twig

    <div class="js-user-rating" data-is-authenticated="{{ app.user ? 'true' : 'false' }}">
        <!-- ... -->
    </div>

Fetch this in JavaScript:

.. code-block:: javascript

    // jquery isn't required, but makes things simple
    var $ = require('jquery');

    $(document).ready(function() {
        var isAuthenticated = $('.js-user-rating').data('is-authenticated');
    });

There is no size limit for the value of the ``data-`` attributes, so you can
store any content. In Twig, use the ``html_attr`` escaping strategy to avoid messing
with HTML attributes. For example, if your ``User`` object has some ``getProfileData()``
method that returns an array, you could:

.. code-block:: twig

    <div data-user-profile="{{ app.user ? app.user.profileData|json_encode|e('html_attr') : '' }}">
        <!-- ... -->
    </div>
