.. index::
    single: Security; Impersonating User

How to impersonate a User
=========================

Sometimes, it's useful to be able to switch from one user to another without
having to log out and log in again (for instance when you are debugging or trying
to understand a bug a user sees that you can't reproduce). This can be easily
done by activating the ``switch_user`` firewall listener:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            firewalls:
                main:
                    # ...
                    switch_user: true

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall>
                <!-- ... -->
                <switch-user />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'main'=> array(
                    // ...
                    'switch_user' => true
                ),
            ),
        ));

To switch to another user, just add a query string with the ``_switch_user``
parameter and the username as the value to the current URL:

.. code-block:: text

    http://example.com/somewhere?_switch_user=thomas

To switch back to the original user, use the special ``_exit`` username:

.. code-block:: text

    http://example.com/somewhere?_switch_user=_exit

During impersonation, the user is provided with a special role called
``ROLE_PREVIOUS_ADMIN``. In a template, for instance, this role can be used
to show a link to exit impersonation:

.. configuration-block::

    .. code-block:: html+jinja

        {% if is_granted('ROLE_PREVIOUS_ADMIN') %}
            <a href="{{ path('homepage', {'_switch_user': '_exit'}) }}">Exit impersonation</a>
        {% endif %}

    .. code-block:: html+php

        <?php if ($view['security']->isGranted('ROLE_PREVIOUS_ADMIN')): ?>
            <a
                href="<?php echo $view['router']->generate('homepage', array(
                    '_switch_user' => '_exit',
                ) ?>"
            >
                Exit impersonation
            </a>
        <?php endif; ?>

Of course, this feature needs to be made available to a small group of users.
By default, access is restricted to users having the ``ROLE_ALLOWED_TO_SWITCH``
role. The name of this role can be modified via the ``role`` setting. For
extra security, you can also change the query parameter name via the ``parameter``
setting:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            firewalls:
                main:
                    # ...
                    switch_user: { role: ROLE_ADMIN, parameter: _want_to_be_this_user }

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall>
                <!-- ... -->
                <switch-user role="ROLE_ADMIN" parameter="_want_to_be_this_user" />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'main'=> array(
                    // ...
                    'switch_user' => array(
                        'role' => 'ROLE_ADMIN',
                        'parameter' => '_want_to_be_this_user',
                    ),
                ),
            ),
        ));
