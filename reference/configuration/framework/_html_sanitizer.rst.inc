html_sanitizer
~~~~~~~~~~~~~~

The ``html_sanitizer`` option is used to configure custom HTML sanitizers.
Read more about the usage in the
:ref:`HTML sanitizer documentation <html-sanitizer-configuration>`.

sanitizers
..........

**type**: ``prototype``

Defines the list of custom HTML sanitizers. Each key is the sanitizer name
(used as a service identifier), and the value is the sanitizer configuration.

default_action
""""""""""""""

**type**: ``string`` **possible values**: ``'drop'``, ``'block'`` or ``'allow'``

Defines how the sanitizer must behave by default for elements not explicitly
configured. ``drop`` removes the element and its children, ``block`` removes
the element but keeps its children, and ``allow`` keeps the element.

allow_safe_elements
"""""""""""""""""""

**type**: ``boolean`` **default**: ``false``

Allows all "safe" elements and attributes. This includes all static elements
except those that can lead to CSS injection or click-jacking.

allow_static_elements
"""""""""""""""""""""

**type**: ``boolean`` **default**: ``false``

Allows all static elements and attributes from the `W3C Sanitizer API standard`_.

allow_elements
""""""""""""""

**type**: ``array``

Configures the elements that the sanitizer should retain from the input.
The key is the element name, the value is either ``*`` to allow the
default set of attributes or a list of allowed attribute names:

.. code-block:: yaml

    # config/packages/html_sanitizer.yaml
    framework:
        html_sanitizer:
            sanitizers:
                app.post_sanitizer:
                    allow_elements:
                        i: '*'
                        a: ['title']
                        span: 'class'

block_elements
""""""""""""""

**type**: ``array``

Configures elements as blocked. Blocked elements are elements the sanitizer
should remove from the input, but retain their children.

drop_elements
"""""""""""""

**type**: ``array``

Configures elements as dropped. Dropped elements are elements the sanitizer
should remove from the input, including their children.

allow_attributes
""""""""""""""""

**type**: ``array``

Configures attributes as allowed globally. Allowed attributes are attributes
the sanitizer should retain from the input. The key is the attribute name,
the value is a list of elements on which this attribute is allowed (or ``*``
to allow on all elements).

drop_attributes
"""""""""""""""

**type**: ``array``

Configures attributes as dropped globally. Dropped attributes are attributes
the sanitizer should remove from the input. The key is the attribute name,
the value is a list of elements on which this attribute is dropped (or ``*``
to drop on all elements).

force_attributes
""""""""""""""""

**type**: ``array``

Forcefully sets the values of certain attributes on certain elements. The key
is the element name, the value is an array of attribute name/value pairs:

.. code-block:: yaml

    # config/packages/html_sanitizer.yaml
    framework:
        html_sanitizer:
            sanitizers:
                app.post_sanitizer:
                    force_attributes:
                        a:
                            rel: noopener noreferrer

force_https_urls
""""""""""""""""

**type**: ``boolean`` **default**: ``false``

Transforms URLs using the HTTP scheme to use the HTTPS scheme instead.

allowed_link_schemes
""""""""""""""""""""

**type**: ``array``

Allows only a given list of schemes to be used in links ``href`` attributes.

allowed_link_hosts
""""""""""""""""""

**type**: ``array`` **default**: ``null``

Allows only a given list of hosts to be used in links ``href`` attributes.
By default (``null``), all hosts are allowed.

allow_relative_links
""""""""""""""""""""

**type**: ``boolean`` **default**: ``false``

Allows relative URLs to be used in links ``href`` attributes.

allowed_media_schemes
"""""""""""""""""""""

**type**: ``array``

Allows only a given list of schemes to be used in media source attributes
(``img``, ``audio``, ``video``, ...).

allowed_media_hosts
"""""""""""""""""""

**type**: ``array`` **default**: ``null``

Allows only a given list of hosts to be used in media source attributes
(``img``, ``audio``, ``video``, ...). By default (``null``), all hosts are
allowed.

allow_relative_medias
"""""""""""""""""""""

**type**: ``boolean`` **default**: ``false``

Allows relative URLs to be used in media source attributes (``img``,
``audio``, ``video``, ...).

with_attribute_sanitizers
"""""""""""""""""""""""""

**type**: ``array``

Registers custom attribute sanitizer services. Each entry is a service
identifier implementing
:class:`Symfony\\Component\\HtmlSanitizer\\Visitor\\AttributeSanitizer\\AttributeSanitizerInterface`.

without_attribute_sanitizers
""""""""""""""""""""""""""""

**type**: ``array``

Unregisters custom attribute sanitizer services that were previously
registered (e.g. by the default configuration).

max_input_length
""""""""""""""""

**type**: ``integer`` **default**: ``0``

The maximum length allowed for the sanitized input. When set to ``0`` (the
default), the length is unlimited.

.. _`W3C Sanitizer API standard`: https://wicg.github.io/sanitizer-api/#default-configuration
