.. index::
    single: Sessions, cookies

Avoid Starting Sessions for Anonymous Users
===========================================

Sessions are automatically started whenever you read, write or even check for the
existence of data in the session. This means that if you need to avoid creating
a session cookie for some users, it can be difficult: you must *completely* avoid
accessing the session.

For example, one common problem in this situation involves checking for flash
messages, which are stored in the session. The following code would guarantee
that a session is *always* started:

.. code-block:: html+twig

    {% for flashMessage in app.session.flashbag.get('notice') %}
        <div class="flash-notice">
            {{ flashMessage }}
        </div>
    {% endfor %}

Even if the user is not logged in and even if you haven't created any flash messages,
just calling the ``get()`` (or even ``has()``) method of the ``flashbag`` will
start a session. This may hurt your application performance because all users will
receive a session cookie. To avoid this behavior, add a check before trying to
access the flash messages:

.. code-block:: html+twig

    {% if app.request.hasPreviousSession %}
        {% for flashMessage in app.session.flashbag.get('notice') %}
            <div class="flash-notice">
                {{ flashMessage }}
            </div>
        {% endfor %}
    {% endif %}
