.. index::
    single: Sessions, cookies

Avoid Starting Sessions for Anonymous Users
===========================================

Sessions in Symfony applications are automatically started when they are necessary.
This includes writing in the user's session, creating a flash message and logging
in users. In order to start the session, Symfony creates a cookie which will be
sent for every request.

However, there are other scenarios when a session is started and therefore, a
cookie will be created even for anonymous users. First, consider the following
code commonly used to display flash messages:

.. code-block:: html+jinja

    {% for flashMessage in app.session.flashbag.get('notice') %}
        <div class="flash-notice">
            {{ flashMessage }}
        </div>
    {% endfor %}

Even if the user is not logged in and even if you haven't created any flash message,
just calling the ``get()`` method of the ``flashbag`` will start a session. This
may hurt your application performance because all users will receive a session
cookie. To avoid this behavior, add a check before trying to access the flash messages:

.. code-block:: html+jinja

    {% if app.session.started %}
        {% for flashMessage in app.session.flashbag.get('notice') %}
            <div class="flash-notice">
                {{ flashMessage }}
            </div>
        {% endfor %}
    {% endif %}

Another scenario where session cookies will be automatically sent is when the
requested URL is covered by a firewall, no matter if anonymous users can access
to that URL:

.. code-block:: yaml

    # app/config/security.yml
    security:
        firewalls:
            main:
                pattern:    ^/
                form_login: ~
                anonymous:  ~

This behavior is caused because in Symfony applications, anonymous users are
technically authenticated,.
