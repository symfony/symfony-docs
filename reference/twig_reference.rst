Twig Extensions Defined by Symfony
==================================

:ref:`Twig <twig-language>` is the template engine used in Symfony applications.
There are tens of `default filters and functions defined by Twig`_, but Symfony
also defines some filters, functions and tags to integrate the various Symfony
components with Twig templates. This article explains them all.

.. tip::

    If these extensions provided by Symfony are not enough, you can
    :ref:`create a custom Twig extension <templates-twig-extension>` to define
    even more filters and functions.

.. _reference-twig-functions:

Functions
---------

.. _reference-twig-function-absolute-url:

absolute_url
~~~~~~~~~~~~

.. code-block:: twig

    {{ absolute_url(path) }}

``path``
    **type**: ``string``

Returns the absolute URL (with scheme and host) from the passed relative path. Combine it with the
:ref:`asset() function <reference-twig-function-asset>` to generate absolute URLs
for web assets. Read more about :ref:`Linking to CSS, JavaScript and Image Assets <templates-link-to-assets>`.

access_decision
~~~~~~~~~~~~~~~

.. code-block:: twig

    {{ access_decision(role, object = null) }}

``role``
    **type**: ``string``
``object`` *(optional)*
    **type**: ``object``

Returns an :class:`Symfony\\Component\\Security\\Core\\Authorization\\AccessDecision`
object, which tells whether the current user has the given role (``isGranted``) and
gives the reason of a denial (``message``). More information can be found in
:ref:`security-template`.

access_decision_for_user
~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: twig

    {{ access_decision_for_user(user, attribute, subject = null) }}

``user``
    **type**: ``object``
``attribute``
    **type**: ``string``
``subject`` *(optional)*
    **type**: ``object``

Same as ``access_decision()``, but it checks the authorization of the given user
instead of the current one.

.. _reference-twig-function-asset:

asset
~~~~~

.. code-block:: twig

    {{ asset(path, packageName = null) }}

``path``
    **type**: ``string``
``packageName`` *(optional)*
    **type**: ``string`` | ``null`` **default**: ``null``

.. code-block:: yaml

    # config/packages/framework.yaml
    framework:
        # ...
        assets:
            packages:
                foo_package:
                    base_path: /avatars

.. code-block:: twig

    {# the image lives at "public/avatars/avatar.png" #}
    {{ asset(path = 'avatar.png', packageName = 'foo_package') }}
    {# output: /avatars/avatar.png #}

Returns the public path of the given asset path (which can be a CSS file, a
JavaScript file, an image path, etc.). This function takes into account where
the application is installed (e.g. in case the project is accessed in a host
subdirectory) and the optional asset package base path.

Symfony provides various cache busting implementations via the
:ref:`assets.version <reference-framework-assets-version>`,
:ref:`assets.version_strategy <reference-assets-version-strategy>`,
and :ref:`assets.json_manifest_path <reference-assets-json-manifest-path>`
configuration options.

.. seealso::

    Read more about :ref:`linking to web assets from templates <templates-link-to-assets>`.

asset_version
~~~~~~~~~~~~~

.. code-block:: twig

    {{ asset_version(path, packageName = null) }}

``path``
    **type**: ``string``
``packageName`` *(optional)*
    **type**: ``string`` | ``null`` **default**: ``null``

Returns the current version of the package, more information in
:ref:`templates-link-to-assets`.

controller
~~~~~~~~~~

.. code-block:: twig

    {{ controller(controller, attributes = [], query = []) }}

``controller``
    **type**: ``string``
``attributes`` *(optional)*
    **type**: ``array`` **default**: ``[]``
``query`` *(optional)*
    **type**: ``array`` **default**: ``[]``

Returns an instance of ``ControllerReference`` to be used with functions
like :ref:`render() <reference-twig-function-render>` and
:ref:`render_esi() <reference-twig-function-render-esi>`.

.. code-block:: twig

    {{ render(controller('App\\Controller\\BlogController:latest', {max: 3})) }}
    {# output: the content returned by the controller method; e.g. a rendered Twig template #}

.. _reference-twig-function-csrf-token:

csrf_token
~~~~~~~~~~

.. code-block:: twig

    {{ csrf_token(intention) }}

``intention``
    **type**: ``string`` - an arbitrary string used to identify the token.

Renders a CSRF token. Use this function if you want :doc:`CSRF protection </security/csrf>`
in a regular HTML form not managed by the Symfony Form component.

.. code-block:: twig

    {{ csrf_token('my_form') }}
    {# output: a random alphanumeric string like:
       a.YOosAd0fhT7BEuUCFbROzrvgkW8kpEmBDQ_DKRMUi2o.Va8ZQKt5_2qoa7dLW-02_PLYwDBx9nnWOluUHUFCwC5Zo0VuuVfQCqtngg #}

dns_prefetch
~~~~~~~~~~~~

.. code-block:: twig

    {{ dns_prefetch(uri, attributes = []) }}

``uri``
    **type**: ``string``
``attributes`` *(optional)*
    **type**: ``array`` **default**: ``[]``

Resource hint that indicates an origin (e.g. ``https://foo.cloudfront.net``)
that will be used to fetch required resources, and that the user agent should
resolve as early as possible. Read more about :doc:`/web_link`.

.. _reference-twig-function-dump:

dump
~~~~

.. code-block:: twig

    {{ dump(variable1, variable2, ...) }}

Dumps the contents of the given variables using the
:doc:`VarDumper component </components/var_dumper>` and displays them inside the
web page contents. If you call it without arguments, it dumps all the variables
available in the template:

.. code-block:: twig

    {# dump a single variable #}
    {{ dump(user) }}

    {# use named arguments to display labels next to the dumped contents #}
    {{ dump(blog_posts: articles, user: app.user) }}

    {# dump all the variables available in the template #}
    {{ dump() }}

To avoid leaking sensitive information, this function is only available in the
``dev`` and ``test`` :ref:`configuration environments <configuration-environments>`.
Use the :ref:`dump tag <reference-twig-tag-dump>` instead to send the contents to
the web debug toolbar rather than displaying them inside the web page.

.. seealso::

    Read more about the :ref:`dump Twig utilities <twig-dump-utilities>`.

expression
~~~~~~~~~~

Creates an :class:`Symfony\\Component\\ExpressionLanguage\\Expression` related
to the :doc:`ExpressionLanguage component </expression_language>`.

.. code-block:: twig

    {{ expression(1 + 2) }}
    {# output: 3 #}

Form Related Functions
~~~~~~~~~~~~~~~~~~~~~~

The following functions related to Symfony Forms are also available. They are
explained in the article about :doc:`customizing form rendering </form/form_customization>`:

* :ref:`form() <reference-forms-twig-form>`
* :ref:`form_start() <reference-forms-twig-start>`
* :ref:`form_end() <reference-forms-twig-end>`
* :ref:`form_widget() <reference-forms-twig-widget>`
* :ref:`form_errors() <reference-forms-twig-errors>`
* :ref:`form_label() <reference-forms-twig-label>`
* :ref:`form_help() <reference-forms-twig-help>`
* :ref:`form_row() <reference-forms-twig-row>`
* :ref:`form_rest() <reference-forms-twig-rest>`
* :ref:`field_name() <reference-forms-twig-field-helpers>`
* :ref:`field_id() <reference-forms-twig-field-helpers>`
* :ref:`field_value() <reference-forms-twig-field-helpers>`
* :ref:`field_label() <reference-forms-twig-field-helpers>`
* :ref:`field_help() <reference-forms-twig-field-helpers>`
* :ref:`field_errors() <reference-forms-twig-field-helpers>`
* :ref:`field_choices() <reference-forms-twig-field-helpers>`

fragment_uri
~~~~~~~~~~~~

.. code-block:: twig

    {{ fragment_uri(controller, absolute = false, strict = true, sign = true) }}

``controller``
    **type**: ``ControllerReference``
``absolute`` *(optional)*
    **type**: ``boolean`` **default**: ``false``
``strict`` *(optional)*
    **type**: ``boolean`` **default**: ``true``
``sign`` *(optional)*
    **type**: ``boolean`` **default**: ``true``

Generates the URI of :ref:`a fragment <fragments-path-config>`.

impersonation_exit_form
~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: twig

    {{ impersonation_exit_form(targetUri = null) }}

``targetUri`` *(optional)*
    **type**: ``string``

Returns the ``action`` and the ``fields`` needed to exit
:doc:`user impersonation </security/impersonating_user>` with a form, so the
switching route can be restricted to ``POST``.

.. code-block:: html+twig

    {% set exit = impersonation_exit_form() %}

    {% if exit.action %}
        <form method="post" action="{{ exit.action }}">
            {% for name, value in exit.fields %}
                <input type="hidden" name="{{ name }}" value="{{ value }}">
            {% endfor %}

            <button>Exit impersonation</button>
        </form>
    {% endif %}

If no user is being impersonated, ``action`` is an empty string and ``fields``
is empty. Read more about
:ref:`impersonating with a form <security-impersonation-form>`.

.. versionadded:: 8.2

    The ``impersonation_exit_form()`` function was introduced in Symfony 8.2.

impersonation_exit_path
~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: twig

    {{ impersonation_exit_path(exitTo = null) }}

``exitTo`` *(optional)*
    **type**: ``string``

Generates a URL that you can visit to exit :doc:`user impersonation </security/impersonating_user>`.
After exiting impersonation, the user is redirected to the current URI. If you
prefer to redirect to a different URI, define its value in the ``exitTo`` argument.

If no user is being impersonated, the function returns an empty string.

impersonation_exit_url
~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: twig

    {{ impersonation_exit_url(exitTo = null) }}

``exitTo`` *(optional)*
    **type**: ``string``

It's similar to the `impersonation_exit_path`_ function, but it generates
absolute URLs instead of relative URLs.

impersonation_form
~~~~~~~~~~~~~~~~~~

.. code-block:: twig

    {{ impersonation_form(identifier, targetUri = null) }}

``identifier``
    **type**: ``string``
``targetUri`` *(optional)*
    **type**: ``string``

Returns the ``action`` and the ``fields`` needed to
:doc:`impersonate a user </security/impersonating_user>` with a form, so the
switching route can be restricted to ``POST``. The impersonated user is
identified by the ``identifier`` argument.

.. code-block:: html+twig

    {% set impersonate = impersonation_form(user.userIdentifier) %}

    <form method="post" action="{{ impersonate.action }}">
        {% for name, value in impersonate.fields %}
            <input type="hidden" name="{{ name }}" value="{{ value }}">
        {% endfor %}

        <button>Impersonate {{ user.userIdentifier }}</button>
    </form>

``fields`` contains the target identity and, when the firewall enables CSRF
protection, the CSRF token, each under its configured parameter name. After the
switch, the user is redirected to the current URI, or to ``targetUri`` when
given. Read more about
:ref:`impersonating with a form <security-impersonation-form>`.

.. versionadded:: 8.2

    The ``impersonation_form()`` function was introduced in Symfony 8.2.

impersonation_path
~~~~~~~~~~~~~~~~~~

.. code-block:: twig

    {{ impersonation_path(identifier, targetUri = null) }}

``identifier``
    **type**: ``string``
``targetUri`` *(optional)*
    **type**: ``string``

Generates a URL that you can visit to
:doc:`impersonate a user </security/impersonating_user>`, identified by the
``identifier`` argument. After the switch, the user is redirected to the current
URI. If you prefer to redirect to a different URI, define its value in the
``targetUri`` argument.

.. versionadded:: 8.2

    The ``targetUri`` argument was introduced in Symfony 8.2.

impersonation_url
~~~~~~~~~~~~~~~~~

.. code-block:: twig

    {{ impersonation_url(identifier, targetUri = null) }}

``identifier``
    **type**: ``string``
``targetUri`` *(optional)*
    **type**: ``string``

It's similar to the `impersonation_path`_ function, but it generates
absolute URLs instead of relative URLs.

.. versionadded:: 8.2

    The ``targetUri`` argument was introduced in Symfony 8.2.

importmap
~~~~~~~~~

Outputs the ``importmap`` & a few other items when using
:doc:`the Asset component </frontend/asset_mapper>`.

is_granted
~~~~~~~~~~

.. code-block:: twig

    {{ is_granted(role, object = null) }}

``role``
    **type**: ``string``
``object`` *(optional)*
    **type**: ``object``

Returns ``true`` if the current user has the given role.

Optionally, an object can be passed to be used by the voter. More information
can be found in :ref:`security-template`.

is_granted_for_user
~~~~~~~~~~~~~~~~~~~

.. code-block:: twig

    {{ is_granted_for_user(user, attribute, subject = null) }}

``user``
    **type**: ``object``
``attribute``
    **type**: ``string``
``subject`` *(optional)*
    **type**: ``object``

Returns ``true`` if the user is authorized for the specified attribute.

Optionally, an object can be passed to be used by the voter. More information
can be found in :ref:`security-template`.

link
~~~~

.. code-block:: twig

    {{ link(uri, rel, attributes = []) }}

``uri``
    **type**: ``string``
``rel``
    **type**: ``string``
``attributes`` *(optional)*
    **type**: ``array`` **default**: ``[]``

Adds a ``Link`` HTTP header to the current response for the given link relation
type (``rel``). It can be used for any link implementing the PSR-13 standard.
Read more about :doc:`/web_link`.

logout_form
~~~~~~~~~~~

.. code-block:: twig

    {{ logout_form(key = null) }}

``key`` *(optional)*
    **type**: ``string``

Returns the ``action`` and the ``fields`` needed to log out with a form, so the
logout route can be restricted to ``POST``. If no key is provided, they are
generated for the current firewall the user is logged into.

.. code-block:: html+twig

    {% set logout = logout_form() %}

    <form method="post" action="{{ logout.action }}">
        {% for name, value in logout.fields %}
            <input type="hidden" name="{{ name }}" value="{{ value }}">
        {% endfor %}

        <button>Log out</button>
    </form>

``fields`` contains the CSRF token under its configured parameter name, and is
empty when the firewall does not enable CSRF protection. Read more about
:ref:`logging out with a form <security-logout-form>`.

.. versionadded:: 8.2

    The ``logout_form()`` function was introduced in Symfony 8.2.

logout_path
~~~~~~~~~~~

.. code-block:: twig

    {{ logout_path(key = null) }}

``key`` *(optional)*
    **type**: ``string``

Generates a relative logout URL for the given firewall. If no key is provided,
the URL is generated for the current firewall the user is logged into.

.. code-block:: yaml

    # config/packages/security.yaml
    security:
        # ...

        firewalls:
            main:
                # ...
                logout:
                    path: '/logout'
            othername:
                # ...
                logout:
                    path: '/other/logout'

.. code-block:: twig

    {{ logout_path(key = 'main') }}
    {# output: /logout #}

    {{ logout_path(key = 'othername') }}
    {# output: /other/logout #}

logout_url
~~~~~~~~~~

.. code-block:: twig

    {{ logout_url(key = null) }}

``key`` *(optional)*
    **type**: ``string``

Equal to the `logout_path`_ function, but it'll generate an absolute URL
instead of a relative one.

.. code-block:: yaml

    # config/packages/security.yaml
    security:
        # ...

        firewalls:
            main:
                # ...
                logout:
                    path: '/logout'
            othername:
                # ...
                logout:
                    path: '/other/logout'

.. code-block:: twig

    {{ logout_url(key = 'main') }}
    {# output: http://example.org/logout #}

    {{ logout_url(key = 'othername') }}
    {# output: http://example.org/other/logout #}

path
~~~~

.. code-block:: twig

    {{ path(route_name, route_parameters = [], relative = false) }}

``name``
    **type**: ``string``
``parameters`` *(optional)*
    **type**: ``array`` **default**: ``[]``
``relative`` *(optional)*
    **type**: ``boolean`` **default**: ``false``

Returns the relative URL (without the scheme and host) for the given route.
If ``relative`` is enabled, it'll create a path relative to the current path.

.. code-block:: twig

    {# consider that the app defines an 'app_blog' route with the path '/blog/{page}' #}

    {{ path(name = 'app_blog', parameters = {page: 3}, relative = false) }}
    {# output: /blog/3 #}

    {{ path(name = 'app_blog', parameters = {page: 3}, relative = true) }}
    {# output: blog/3 #}

.. seealso::

    Read more about :doc:`Symfony routing </routing>` and about
    :ref:`creating links in Twig templates <templates-link-to-pages>`.

preconnect
~~~~~~~~~~

.. code-block:: twig

    {{ preconnect(uri, attributes = []) }}

``uri``
    **type**: ``string``
``attributes`` *(optional)*
    **type**: ``array`` **default**: ``[]``

Resource hint that indicates an origin (e.g.
``https://www.google-analytics.com``) that will be used to fetch required
resources, initiating an early connection (DNS lookup, TCP handshake and
optional TLS negotiation) to mask the high latency costs of establishing a
connection. Read more about :doc:`/web_link`.

prefetch
~~~~~~~~

.. code-block:: twig

    {{ prefetch(uri, attributes = []) }}

``uri``
    **type**: ``string``
``attributes`` *(optional)*
    **type**: ``array`` **default**: ``[]``

Resource hint that identifies a resource that might be required by the next
navigation, and that the user agent should fetch so it can deliver a faster
response once the resource is requested in the future. Read more about
:doc:`/web_link`.

preload
~~~~~~~

.. code-block:: twig

    {{ preload(uri, attributes = []) }}

``uri``
    **type**: ``string``
``attributes`` *(optional)*
    **type**: ``array`` **default**: ``[]``

Preloads a resource by adding a ``Link`` HTTP header with the ``preload`` link
relation, telling the browser to download it as soon as possible. Read more
about :doc:`/web_link`.

prerender
~~~~~~~~~

.. code-block:: twig

    {{ prerender(uri, attributes = []) }}

``uri``
    **type**: ``string``
``attributes`` *(optional)*
    **type**: ``array`` **default**: ``[]``

Resource hint that identifies a resource that might be required by the next
navigation, and that the user agent should fetch and execute so it can deliver
a faster response once the resource is requested later. This hint is
**deprecated** and superseded by the Speculation Rules API. Read more about
:doc:`/web_link`.

.. _reference-twig-function-relative-path:

relative_path
~~~~~~~~~~~~~

.. code-block:: twig

    {{ relative_path(path) }}

``path``
    **type**: ``string``

Returns the relative path from the passed absolute URL. For example, assume
you're on the following page in your app:
``http://example.com/products/hover-board``.

.. code-block:: twig

    {{ relative_path('http://example.com/human.txt') }}
    {# ../human.txt #}

    {{ relative_path('http://example.com/products/products_icon.png') }}
    {# products_icon.png #}

.. _reference-twig-function-render:

render
~~~~~~

.. code-block:: twig

    {{ render(uri, options = []) }}

``uri``
    **type**: ``string`` | ``ControllerReference``
``options`` *(optional)*
    **type**: ``array`` **default**: ``[]``

Makes a request to the given internal URI or controller and returns the result.
The render strategy can be specified in the ``strategy`` key of the options.
It's commonly used to :ref:`embed controllers in templates <templates-embed-controllers>`.

.. _reference-twig-function-render-esi:

render_esi
~~~~~~~~~~

.. code-block:: twig

    {{ render_esi(uri, options = []) }}

``uri``
    **type**: ``string`` | ``ControllerReference``
``options`` *(optional)*
    **type**: ``array`` **default**: ``[]``

It's similar to the `render`_ function and defines the same arguments. However,
it generates an ESI tag when :doc:`ESI support </http_cache/esi>` is enabled or
falls back to the behavior of `render`_ otherwise.

.. tip::

    The ``render_esi()`` function is an example of the shortcut functions
    of ``render``. It automatically sets the strategy based on what's given
    in the function name, e.g. ``render_ssi()`` will use the SSI strategy.
    This works for all ``render_*()`` functions.

.. _reference-twig-function-render-ssi:

render_ssi
~~~~~~~~~~

.. code-block:: twig

    {{ render_ssi(uri, options = []) }}

``uri``
    **type**: ``string`` | ``ControllerReference``
``options`` *(optional)*
    **type**: ``array`` **default**: ``[]``

It's similar to the `render`_ function and defines the same arguments. However,
it generates an SSI tag when :doc:`SSI support </http_cache/ssi>` is enabled or
falls back to the behavior of `render`_ otherwise.

.. _reference-twig-function-t:

t
~~~

.. code-block:: twig

    {{ t(message, parameters = [], domain = 'messages')|trans }}

``message``
    **type**: ``string``
``parameters`` *(optional)*
    **type**: ``array`` **default**: ``[]``
``domain`` *(optional)*
    **type**: ``string`` **default**: ``messages``

Creates a ``Translatable`` object that can be passed to the
:ref:`trans filter <reference-twig-filter-trans>`.

.. configuration-block::

    .. code-block:: yaml

        # translations/blog.en.yaml
        message: Hello %name%

    .. code-block:: xml

        <!-- translations/blog.en.xlf -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="message">
                        <source>message</source>
                        <target>Hello %name%</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: php

        // translations/blog.en.php
        return [
            'message' => "Hello %name%",
        ];

Using the filter will be rendered as:

.. code-block:: twig

    {{ t(message = 'message', parameters = {'%name%': 'John'}, domain = 'blog')|trans }}
    {# output: Hello John #}

url
~~~

.. code-block:: twig

    {{ url(route_name, route_parameters = [], schemeRelative = false) }}

``name``
    **type**: ``string``
``parameters`` *(optional)*
    **type**: ``array`` **default**: ``[]``
``schemeRelative`` *(optional)*
    **type**: ``boolean`` **default**: ``false``

Returns the absolute URL (with scheme and host) for the given route. If
``schemeRelative`` is enabled, it'll create a scheme-relative URL.

.. code-block:: twig

    {# consider that the app defines an 'app_blog' route with the path '/blog/{page}' #}

    {{ url(name = 'app_blog', parameters = {page: 3}, schemeRelative = false) }}
    {# output: http://example.org/blog/3 #}

    {{ url(name = 'app_blog', parameters = {page: 3}, schemeRelative = true) }}
    {# output: //example.org/blog/3 #}

.. seealso::

    Read more about :doc:`Symfony routing </routing>` and about
    :ref:`creating links in Twig templates <templates-link-to-pages>`.

workflow_can
~~~~~~~~~~~~

.. code-block:: twig

    {{ workflow_can(subject, transitionName, name = null) }}

``subject``
    **type**: ``object``
``transitionName``
    **type**: ``string``
``name`` *(optional)*
    **type**: ``string`` | ``null`` **default**: ``null``

Returns ``true`` if the given object can apply the given transition in its
current state. Use it to only display the actions that are actually available
to the user:

.. code-block:: html+twig

    {% if workflow_can(post, 'publish') %}
        <a href="...">Publish</a>
    {% endif %}

The ``subject`` argument is the object managed by the workflow (e.g. the blog
post) and ``transitionName`` is the name of one of the transitions defined in
the workflow configuration.

The ``name`` argument is the name of the workflow to use. It's only needed when
the object is associated with more than one workflow. All the ``workflow_*()``
functions accept this same optional argument as their last parameter:

.. code-block:: twig

    {# the object is associated with a single workflow, so the name is optional #}
    {{ workflow_can(post, 'publish') }}

    {# the object is associated with multiple workflows, so the name is required #}
    {{ workflow_can(post, 'publish', 'blog_publishing') }}

.. seealso::

    Read more about :doc:`Symfony Workflows </workflow>`.

workflow_has_marked_place
~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: twig

    {{ workflow_has_marked_place(subject, placeName, name = null) }}

``subject``
    **type**: ``object``
``placeName``
    **type**: ``string``
``name`` *(optional)*
    **type**: ``string`` | ``null`` **default**: ``null``

Returns ``true`` if the given object is currently in the given place. When using
workflows (instead of state machines) an object can be in several places at the
same time, so this only checks if the given place is one of them:

.. code-block:: html+twig

    {% if workflow_has_marked_place(post, 'reviewed') %}
        <p>This post is ready to be published.</p>
    {% endif %}

See `workflow_can`_ for the details of the optional ``name`` argument.

workflow_marked_places
~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: twig

    {{ workflow_marked_places(subject, placesNameOnly = true, name = null) }}

``subject``
    **type**: ``object``
``placesNameOnly`` *(optional)*
    **type**: ``boolean`` **default**: ``true``
``name`` *(optional)*
    **type**: ``string`` | ``null`` **default**: ``null``

Returns all the places where the given object currently is (its *marking*). By
default it returns an array of place names; set ``placesNameOnly`` to ``false``
to get the entire marking, which is an array indexed by place name:

.. code-block:: html+twig

    {% for place in workflow_marked_places(post) %}
        <span class="label">{{ place }}</span>
    {% endfor %}

See `workflow_can`_ for the details of the optional ``name`` argument.

workflow_metadata
~~~~~~~~~~~~~~~~~

.. code-block:: twig

    {{ workflow_metadata(subject, key, metadataSubject = null, name = null) }}

``subject``
    **type**: ``object``
``key``
    **type**: ``string``
``metadataSubject`` *(optional)*
    **type**: ``string`` | :class:`Symfony\\Component\\Workflow\\Transition` | ``null`` **default**: ``null``
``name`` *(optional)*
    **type**: ``string`` | ``null`` **default**: ``null``

Returns the value of the ``key`` metadata defined in the workflow configuration.
Workflows, places and transitions can all define their own metadata, so use the
``metadataSubject`` argument to select which one to read: pass a place name to
read place metadata, a :class:`Symfony\\Component\\Workflow\\Transition` object
to read transition metadata and omit it to read the metadata of the workflow
itself:

.. code-block:: twig

    {# metadata of the workflow #}
    {{ workflow_metadata(post, 'title') }}

    {# metadata of the 'draft' place #}
    {{ workflow_metadata(post, 'max_num_of_words', 'draft') }}

    {# metadata of the 'to_review' transition #}
    {{ workflow_metadata(post, 'priority', workflow_transition(post, 'to_review')) }}

See `workflow_can`_ for the details of the optional ``name`` argument.

.. seealso::

    Read more about :ref:`storing metadata in workflows <workflow_storing-metadata>`.

workflow_transition
~~~~~~~~~~~~~~~~~~~

.. code-block:: twig

    {{ workflow_transition(subject, transitionName, name = null) }}

``subject``
    **type**: ``object``
``transitionName``
    **type**: ``string``
``name`` *(optional)*
    **type**: ``string`` | ``null`` **default**: ``null``

Returns the :class:`Symfony\\Component\\Workflow\\Transition` object matching the
given name, or ``null`` if that transition is not enabled for the object in its
current state. It's mostly used to pass a transition to the `workflow_metadata`_
function.

See `workflow_can`_ for the details of the optional ``name`` argument.

workflow_transition_blockers
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: twig

    {{ workflow_transition_blockers(subject, transitionName, name = null) }}

``subject``
    **type**: ``object``
``transitionName``
    **type**: ``string``
``name`` *(optional)*
    **type**: ``string`` | ``null`` **default**: ``null``

Returns a :class:`Symfony\\Component\\Workflow\\TransitionBlockerList` object with
the reasons why the given object can't apply the given transition. These reasons
are added by the :ref:`guard event listeners <workflow-usage-guard-events>` of the
workflow, so use them to explain to the user why some action is not available:

.. code-block:: html+twig

    {% for blocker in workflow_transition_blockers(post, 'publish') %}
        <span class="error">{{ blocker.message }}</span>
    {% endfor %}

See `workflow_can`_ for the details of the optional ``name`` argument.

workflow_transitions
~~~~~~~~~~~~~~~~~~~~

.. code-block:: twig

    {{ workflow_transitions(subject, name = null) }}

``subject``
    **type**: ``object``
``name`` *(optional)*
    **type**: ``string`` | ``null`` **default**: ``null``

Returns an array of :class:`Symfony\\Component\\Workflow\\Transition` objects with
all the transitions that the given object can apply in its current state. Use it
to render all the available actions at once instead of checking them one by one
with `workflow_can`_:

.. code-block:: html+twig

    {% for transition in workflow_transitions(post) %}
        <a href="...">{{ transition.name }}</a>
    {% else %}
        No actions available.
    {% endfor %}

See `workflow_can`_ for the details of the optional ``name`` argument.

.. _reference-twig-filters:

Filters
-------

abbr_class
~~~~~~~~~~

.. code-block:: twig

    {{ class|abbr_class }}

``class``
    **type**: ``string``

Generates an ``<abbr>`` element with the short name of a PHP class (the
FQCN will be shown in a tooltip when a user hovers over the element).

.. code-block:: twig

    {{ 'App\\Entity\\Product'|abbr_class }}

The above example will be rendered as:

.. code-block:: html

    <abbr title="App\Entity\Product">Product</abbr>

abbr_method
~~~~~~~~~~~

.. code-block:: twig

    {{ method|abbr_method }}

``method``
    **type**: ``string``

Generates an ``<abbr>`` element using the ``FQCN::method()`` syntax. If
``method`` is ``Closure``, ``Closure`` will be used instead and if ``method``
doesn't have a class name, it's shown as a function (``method()``).

.. code-block:: twig

    {{ 'App\\Controller\\ProductController::list'|abbr_method }}

The above example will be rendered as:

.. code-block:: html

    <abbr title="App\Controller\ProductController">ProductController</abbr>::list()

.. _reference-twig-filter-emojify:

emojify
~~~~~~~

.. code-block:: twig

    {{ text|emojify(catalog = null) }}

``text``
    **type**: ``string``

``catalog`` *(optional)*
    **type**: ``string`` | ``null``

    The emoji set used to generate the textual representation (``slack``,
    ``github``, ``gitlab``, etc.)

It transforms the textual representation of an emoji (e.g. ``:wave:``) into the
actual emoji (👋):

.. code-block:: twig

    {{ ':+1:'|emojify }}                 {# renders: 👍 #}
    {{ ':+1:'|emojify('github') }}       {# renders: 👍 #}
    {{ ':thumbsup:'|emojify('gitlab') }} {# renders: 👍 #}

file_excerpt
~~~~~~~~~~~~

.. code-block:: twig

    {{ file|file_excerpt(line, srcContext = 3) }}

``file``
    **type**: ``string``
``line``
    **type**: ``integer``
``srcContext`` *(optional)*
    **type**: ``integer``

Generates an excerpt of a code file around the given ``line`` number. The
``srcContext`` argument defines the total number of lines to display around the
given line number (use ``-1`` to display the whole file).

Consider the following as the content of ``file.txt``:

.. code-block:: text

    a
    b
    c
    d
    e

.. code-block:: html+twig

    {{ '/path/to/file.txt'|file_excerpt(line = 4, srcContext = 1) }}
    {# output: #}
    <ol start="3">
        <li><a class="anchor" id="line3"></a><code>c</code></li>
        <li class="selected"><a class="anchor" id="line4"></a><code>d</code></li>
        <li><a class="anchor" id="line5"></a><code>e</code></li>
    </ol>

    {{ '/path/to/file.txt'|file_excerpt(line = 1, srcContext = 0) }}
    {# output: #}
    <ol start="1">
        <li class="selected"><a class="anchor" id="line1"></a><code>a</code></li>
    </ol>

file_link
~~~~~~~~~

.. code-block:: twig

    {{ file|file_link(line) }}

``file``
    **type**: ``string``
``line``
    **type**: ``integer``

Generates a link to the provided file and line number using
a preconfigured scheme.

.. code-block:: twig

    {{ 'path/to/file/file.txt'|file_link(line = 3) }}
    {# output: file://path/to/file/file.txt#L3 #}

file_relative
~~~~~~~~~~~~~

.. code-block:: twig

    {{ file|file_relative }}

``file``
    **type**: ``string``

It transforms the given absolute file path into a new file path relative to
project's root directory:

.. code-block:: twig

    {{ '/var/www/blog/templates/admin/index.html.twig'|file_relative }}
    {# if project root dir is '/var/www/blog/', it returns 'templates/admin/index.html.twig' #}

If the given file path is out of the project directory, a ``null`` value
will be returned.

format_args
~~~~~~~~~~~

.. code-block:: twig

    {{ args|format_args }}

``args``
    **type**: ``array``

Generates a string with the arguments and their types (within ``<em>`` elements).

format_args_as_text
~~~~~~~~~~~~~~~~~~~

.. code-block:: twig

    {{ args|format_args_as_text }}

``args``
    **type**: ``array``

Equal to the `format_args`_ filter, but without using HTML tags.

format_file
~~~~~~~~~~~

.. code-block:: twig

    {{ file|format_file(line, text = null) }}

``file``
    **type**: ``string``
``line``
    **type**: ``integer``
``text`` *(optional)*
    **type**: ``string`` **default**: ``null``

Generates the file path inside an ``<a>`` element. If the path is inside
the kernel root directory, the kernel root directory path is replaced by
``kernel.project_dir`` (showing the full path in a tooltip on hover).

.. code-block:: html+twig

    {{ '/path/to/file.txt'|format_file(line = 1, text = "my_text") }}
    {# output: #}
    <a href="/path/to/file.txt#L1"
        title="Click to open this file" class="file_link">my_text at line 1
    </a>

    {{ "/path/to/file.txt"|format_file(line = 3) }}
    {# output: #}
    <a href="/path/to/file.txt&amp;line=3"
        title="Click to open this file" class="file_link">/path/to/file.txt at line 3
    </a>

.. tip::

    If you set the :ref:`framework.ide <reference-framework-ide>` option, the
    generated links will change to open the file in that IDE/editor. For example,
    when using PhpStorm, the ``<a href="/path/to/file.txt&amp;line=3"`` link will
    become ``<a href="phpstorm://open?file=/path/to/file.txt&amp;line=3"``.

format_file_from_text
~~~~~~~~~~~~~~~~~~~~~

.. code-block:: twig

    {{ text|format_file_from_text }}

``text``
    **type**: ``string``

Uses `format_file`_ to improve the output of default PHP errors.

.. _reference-twig-humanize-filter:

humanize
~~~~~~~~

.. code-block:: twig

    {{ text|humanize }}

``text``
    **type**: ``string``

Transforms the given string into a human readable string (by replacing underscores
with spaces, capitalizing the string, etc.) It's useful e.g. when displaying
the names of PHP properties/variables to end users:

.. code-block:: twig

    {{ 'dateOfBirth'|humanize }}    {# renders: Date of birth #}
    {{ 'DateOfBirth'|humanize }}    {# renders: Date of birth #}
    {{ 'date-of-birth'|humanize }}  {# renders: Date-of-birth #}
    {{ 'date_of_birth'|humanize }}  {# renders: Date of birth #}
    {{ 'date of birth'|humanize }}  {# renders: Date of birth #}
    {{ 'Date Of Birth'|humanize }}  {# renders: Date of birth #}

.. _reference-twig-filter-trans:

trans
~~~~~

.. code-block:: twig

    {{ message|trans(arguments = [], domain = null, locale = null) }}

``message``
    **type**: ``string`` | ``Translatable``
``arguments`` *(optional)*
    **type**: ``array`` **default**: ``[]``
``domain`` *(optional)*
    **type**: ``string`` **default**: ``null``
``locale`` *(optional)*
    **type**: ``string`` **default**: ``null``

Translates the text into the current language. More information in
:ref:`Translation Filters <translation-filters>`.

.. configuration-block::

    .. code-block:: yaml

        # translations/messages.en.yaml
        message: Hello %name%

    .. code-block:: xml

        <!-- translations/messages.en.xlf -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="message">
                        <source>message</source>
                        <target>Hello %name%</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: php

        // translations/messages.en.php
        return [
            'message' => "Hello %name%",
        ];

Using the filter will be rendered as:

.. code-block:: twig

    {{ 'message'|trans(arguments = {'%name%': 'John'}, domain = 'messages', locale = 'en') }}
    {# output: Hello John #}

sanitize_html
~~~~~~~~~~~~~

.. code-block:: twig

    {{ body|sanitize_html(sanitizer = "default") }}

``body``
    **type**: ``string``
``sanitizer`` *(optional)*
    **type**: ``string`` **default**: ``"default"``

Sanitizes the text using the HTML Sanitizer component. More information in
:ref:`HTML Sanitizer <html-sanitizer-twig>`.

.. _reference-twig-filter-normalize:

normalize
~~~~~~~~~

.. versionadded:: 8.2

    The ``normalize`` filter was introduced in Symfony 8.2.

.. code-block:: text

    {{ object|normalize(format = null, context = []) }}

``object``
    **type**: ``mixed``

``format`` *(optional)*
    **type**: ``string`` | ``null`` **default**: ``null``

``context`` *(optional)*
    **type**: ``array`` **default**: ``[]``

Accepts any data that can be normalized by the
:doc:`Serializer component </serializer>` and returns the normalized value.
Unlike the ``serialize`` filter, this filter does not encode the result into a
string.

Use this filter when another Twig function, component or helper encodes the
value itself. For example, ``vue_component()`` encodes its props as JSON, so
passing a serialized string would encode the data twice:

.. code-block:: text

    <div {{ vue_component('ProductCard', {
        product: product|normalize(context = { groups: 'card' }),
    }) }}></div>

If the ``serializer`` service is replaced by an implementation that does not
implement :class:`Symfony\\Component\\Serializer\\Normalizer\\NormalizerInterface`,
using this filter throws a ``LogicException``.

.. _reference-twig-filter-serialize:

serialize
~~~~~~~~~

.. code-block:: twig

    {{ object|serialize(format = 'json', context = []) }}

``object``
    **type**: ``mixed``

``format`` *(optional)*
    **type**: ``string``

``context`` *(optional)*
    **type**: ``array``

Accepts any data that can be serialized by the :doc:`Serializer component </serializer>`
and returns a serialized string in the specified ``format``.

Use the :ref:`normalize filter <reference-twig-filter-normalize>` instead when
the consumer expects arrays or scalar values and encodes the value itself.

For example::

    $object = new \stdClass();
    $object->foo = 'bar';
    $object->content = [];
    $object->createdAt = new \DateTime('2024-11-30');

.. code-block:: twig

    {{ object|serialize(format = 'json', context = {
        'datetime_format': 'D, Y-m-d',
        'empty_array_as_object': true,
    }) }}
    {# output: {"foo":"bar","content":{},"createdAt":"Sat, 2024-11-30"} #}

yaml_dump
~~~~~~~~~

.. code-block:: twig

    {{ value|yaml_dump(inline = 0, dumpObjects = false) }}

``value``
    **type**: ``mixed``
``inline`` *(optional)*
    **type**: ``integer`` **default**: ``0``
``dumpObjects`` *(optional)*
    **type**: ``boolean`` **default**: ``false``

Does the same as `yaml_encode() <yaml_encode>`_, but includes the type in
the output.

The ``inline`` argument is the level where the generated output switches to inline YAML:

.. code-block:: twig

    {% set array = {
        'a': {
            'c': 'e'
        },
        'b': {
            'd': 'f'
        }
    } %}

    {{ array|yaml_dump(inline = 0) }}
    {# output:
       %array% { a: { c: e }, b: { d: f } } #}

    {{ array|yaml_dump(inline = 1) }}
    {# output:
       %array% a: { c: e }
       b: { d: f } #}

The ``dumpObjects`` argument enables the dumping of PHP objects::

    // ...
    $object = new \stdClass();
    $object->foo = 'bar';
    // ...

.. code-block:: twig

    {{ object|yaml_dump(dumpObjects = false) }}
    {# output: %object% null #}

    {{ object|yaml_dump(dumpObjects = true) }}
    {# output: %object% !php/object 'O:8:"stdClass":1:{s:3:"foo";s:3:"bar";}' #}

yaml_encode
~~~~~~~~~~~

.. code-block:: twig

    {{ input|yaml_encode(inline = 0, dumpObjects = false) }}

``input``
    **type**: ``mixed``
``inline`` *(optional)*
    **type**: ``integer`` **default**: ``0``
``dumpObjects`` *(optional)*
    **type**: ``boolean`` **default**: ``false``

Transforms the input into YAML syntax.

The ``inline`` argument is the level where the generated output switches to inline YAML:

.. code-block:: twig

    {% set array = {
        'a': {
            'c': 'e'
        },
        'b': {
            'd': 'f'
        }
    } %}

    {{ array|yaml_encode(inline = 0) }}
    {# output:
       { a: { c: e }, b: { d: f } } #}

    {{ array|yaml_encode(inline = 1) }}
    {# output:
       a: { c: e }
       b: { d: f } #}

The ``dumpObjects`` argument enables the dumping of PHP objects::

    // ...
    $object = new \stdClass();
    $object->foo = 'bar';
    // ...

.. code-block:: twig

    {{ object|yaml_encode(dumpObjects = false) }}
    {# output: null #}

    {{ object|yaml_encode(dumpObjects = true) }}
    {# output: !php/object 'O:8:"stdClass":1:{s:5:"foo";s:7:"bar";}' #}

See :ref:`components-yaml-dump` for more information.

.. _reference-twig-tags:

Tags
----

.. _reference-twig-tag-dump:

dump
~~~~

.. code-block:: twig

    {% dump variable1, variable2, ... %}

Dumps the contents of the given variables using the
:doc:`VarDumper component </components/var_dumper>`. Unlike the
:ref:`dump() function <reference-twig-function-dump>`, which displays the
contents inside the web page, this tag sends them to the
:doc:`web debug toolbar </profiler>`, so the web page contents are not modified.

To avoid leaking sensitive information, this tag is only available in the
``dev`` and ``test`` :ref:`configuration environments <configuration-environments>`.

.. seealso::

    Read more about the :ref:`dump Twig utilities <twig-dump-utilities>`.

.. _reference-twig-tag-form-theme:

form_theme
~~~~~~~~~~

.. code-block:: twig

    {% form_theme form resources %}

``form``
    **type**: ``FormView``
``resources``
    **type**: ``array`` | ``string``

Sets the resources to override the form theme for the given form view instance.
You can use ``_self`` as resources to set it to the current resource. More
information in :doc:`/form/form_customization`.

.. _reference-twig-tag-stopwatch:

stopwatch
~~~~~~~~~

.. code-block:: twig

    {% stopwatch 'event_name' %}...{% endstopwatch %}

This measures the time and memory used to execute some code in the template and
displays it in the Symfony profiler. See :ref:`how to profile Symfony applications <profiling-applications>`.

trans
~~~~~

.. code-block:: twig

    {% trans with vars from domain into locale %}{% endtrans %}

``vars`` *(optional)*
    **type**: ``array`` **default**: ``[]``
``domain`` *(optional)*
    **type**: ``string`` **default**: ``string``
``locale`` *(optional)*
    **type**: ``string`` **default**: ``string``

Renders the translation of the content. More information in :ref:`translation-tags`.

trans_default_domain
~~~~~~~~~~~~~~~~~~~~

.. code-block:: twig

    {% trans_default_domain domain %}
    {% trans_default_domain domain for _self %}

``domain``
    **type**: ``string``

This will set the default domain for translations made after this tag, within
the current Twig scope (e.g. it does not apply to the body of an ``embed``
tag).

.. versionadded:: 8.2

    The ``for _self`` modifier was introduced in Symfony 8.2. When added,
    ``domain`` must be a constant value and the tag must be used only once
    per template, before any ``embed`` tag. The domain then applies to the
    whole template instead: It also reaches macros, blocks inherited by
    child templates, ``embed`` bodies, and ``trans`` calls written above the
    declaration. A scoped ``trans_default_domain`` (without ``for _self``)
    still takes precedence where it applies.

    This is especially useful for :ref:`Twig Components <templates-twig-components>`,
    which are built on top of ``embed`` under the hood: without ``for _self``,
    the domain would have to be redeclared within each and every Twig
    Component used in the same file, instead of being set once at the top.

.. _reference-twig-tests:

Tests
-----

The following tests related to Symfony Forms are available. They are explained
in the article about :doc:`customizing form rendering </form/form_customization>`:

* :ref:`selectedchoice() <form-twig-selectedchoice>`
* :ref:`rootform() <form-twig-rootform>`

Global Variables
----------------

app
~~~

The ``app`` variable is injected automatically by Symfony in all templates and
provides access to lots of useful application information. Read more about the
:ref:`Twig global app variable <twig-app-variable>`.

.. _`default filters and functions defined by Twig`: https://twig.symfony.com/doc/3.x/#reference
