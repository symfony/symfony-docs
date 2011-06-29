How to force HTTPS or HTTP for Different URLs
=============================================

You can force areas of your site to use the ``HTTPS`` protocol in the security
config. This is done through the ``access_control`` rules using the ``requires_channel``
option. For example, if you want to force all URLs starting with ``/secure``
to use ``HTTPS`` then you could use the following config:

.. configuration-block::

        .. code-block:: yaml

            access_control:
                - path: ^/secure
                  roles: ROLE_ADMIN
                  requires_channel: https

        .. code-block:: xml

            <access-control>
                <rule path="^/secure" role="ROLE_ADMIN" requires_channel="https" />
            </access-control>

        .. code-block:: php

            'access_control' => array(
                array('path' => '^/secure', 
                      'role' => 'ROLE_ADMIN', 
                      'requires_channel' => 'https'
                ),
            ),

The login form itself needs to allow anonymous access otherwise users will
be unable to authenticate. To force it to use ``HTTPS`` you can still use
``access_control`` rules by using the ``IS_AUTHENTICATED_ANONYMOUSLY`` 
role:

.. configuration-block::

        .. code-block:: yaml

            access_control:
                - path: ^/login
                  roles: IS_AUTHENTICATED_ANONYMOUSLY
                  requires_channel: https

        .. code-block:: xml

            <access-control>
                <rule path="^/login" 
                      role="IS_AUTHENTICATED_ANONYMOUSLY" 
                      requires_channel="https" />
            </access-control>

        .. code-block:: php

            'access_control' => array(
                array('path' => '^/login', 
                      'role' => 'IS_AUTHENTICATED_ANONYMOUSLY', 
                      'requires_channel' => 'https'
                ),
            ),

It is also possible to specify using ``HTTPS`` in the routing configuration
see :doc:`/cookbook/routing/scheme` for more details.
