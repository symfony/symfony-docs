Passing Information from Twig to JavaScript
===========================================

In Symfony applications, you may find that you need to pass some dynamic data
(e.g. user information) from Twig to your JavaScript code. One great way to pass
dynamic configuration is by storing information in ``data-*`` attributes and reading
them later in JavaScript. For example:

.. code-block:: html+twig

    <div class="js-user-rating"
        data-is-authenticated="{{ app.user ? 'true' : 'false' }}"
        data-user="{{ app.user|serialize(format = 'json') }}"
    >
        <!-- ... -->
    </div>

Fetch this in JavaScript:

.. code-block:: javascript

    document.addEventListener('DOMContentLoaded', function() {
        const userRating = document.querySelector('.js-user-rating');
        const isAuthenticated = userRating.getAttribute('data-is-authenticated');
        const user = JSON.parse(userRating.getAttribute('data-user'));
    });

.. note::

    If you prefer to `access data attributes via JavaScript's dataset property`_,
    the attribute names are converted from dash-style to camelCase. For example,
    ``data-number-of-reviews`` becomes ``dataset.numberOfReviews``:

    .. code-block:: javascript

        // ...
        const isAuthenticated = userRating.dataset.isAuthenticated;
        const user = JSON.parse(userRating.dataset.user);

There is no size limit for the value of the ``data-*`` attributes, so you can
store any content. In Twig, use the ``html_attr`` escaping strategy to avoid messing
with HTML attributes. For example, if your ``User`` object has some ``getProfileData()``
method that returns an array, you could do the following:

.. code-block:: html+twig

    <div data-user-profile="{{ app.user ? app.user.profileData|json_encode|e('html_attr') }}">
        <!-- ... -->
    </div>

.. _`access data attributes via JavaScript's dataset property`: https://developer.mozilla.org/en-US/docs/Learn/HTML/Howto/Use_data_attributes
