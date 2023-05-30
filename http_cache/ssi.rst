.. _server-side-includes:

Working with Server Side Includes
=================================

In a similar way as :doc:`ESI (Edge Side Includes) </http_cache/esi>`,
SSI can be used to control HTTP caching on fragments of a response.
The most important difference that is SSI is known directly by most
web servers like `Apache`_, `Nginx`_ etc.

The SSI instructions are done via HTML comments:

.. code-block:: html

    <!DOCTYPE html>
    <html>
        <body>
            <!-- ... some content -->

            <!-- Embed the content of another page here -->
            <!--#include virtual="/..." -->

            <!-- ... more content -->
        </body>
    </html>

There are some other `available directives`_ but
Symfony manages only the ``#include virtual`` one.

.. caution::

    Be careful with SSI, your website may fall victim to injections.
    Please read this `OWASP article`_ first!

When the web server reads an SSI directive, it requests the given URI or gives
directly from its cache. It repeats this process until there is no more
SSI directives to handle. Then, it merges all responses into one and sends
it to the client.

.. _using-ssi-in-symfony:

Using SSI in Symfony
~~~~~~~~~~~~~~~~~~~~

First, to use SSI, be sure to enable it in your application configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            ssi: { enabled: true }

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/symfony"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:ssi enabled="true"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->ssi()
                ->enabled(true)
            ;
        };

Suppose you have a page with private content like a Profile page and you want
to cache a static GDPR content block. With SSI, you can add some expiration
on this block and keep the page private::

.. configuration-block::

    .. code-block:: php-attributes

        // src/Controller/ProfileController.php
        namespace App\Controller;

        use Symfony\Component\HttpKernel\Attribute\Cache;
        // ...

        class ProfileController extends AbstractController
        {
            public function index(): Response
            {
                // by default, responses are private
                return $this->render('profile/index.html.twig');
            }

            #[Cache(smaxage: 600)]
            public function gdpr(): Response
            {
                return $this->render('profile/gdpr.html.twig');
            }
        }

    .. code-block:: php

        // src/Controller/ProfileController.php
        namespace App\Controller;

        // ...
        class ProfileController extends AbstractController
        {
            public function index(): Response
            {
                // by default, responses are private
                return $this->render('profile/index.html.twig');
            }

            public function gdpr(): Response
            {
                $response = $this->render('profile/gdpr.html.twig');

                // sets to public and adds some expiration
                $response->setSharedMaxAge(600);

                return $response;
            }
        }

The profile index page has not public caching, but the GDPR block has
10 minutes of expiration. Let's include this block into the main one:

.. code-block:: twig

    {# templates/profile/index.html.twig #}

    {# you can use a controller reference #}
    {{ render_ssi(controller('App\\Controller\\ProfileController::gdpr')) }}

    {# ... or a path (in server's SSI configuration is common to use relative paths instead of absolute URLs) #}
    {{ render_ssi(path('profile_gdpr')) }}

The ``render_ssi`` twig helper will generate something like:

.. code-block:: html

    <!--#include virtual="/_fragment?_hash=abcdef1234&_path=_controller=App\Controller\ProfileController::gdpr" -->

``render_ssi`` ensures that SSI directive is generated only if the request
has the header requirement like ``Surrogate-Capability: device="SSI/1.0"``
(normally given by the web server).
Otherwise it will embed directly the sub-response.

.. note::

    For more information about Symfony cache fragments, take a tour on
    the :ref:`ESI documentation <http_cache-fragments>`.

.. _`Apache`: https://httpd.apache.org/docs/current/en/howto/ssi.html
.. _`Nginx`: https://nginx.org/en/docs/http/ngx_http_ssi_module.html
.. _`available directives`: https://en.wikipedia.org/wiki/Server_Side_Includes#Directives
.. _`OWASP article`: https://www.owasp.org/index.php/Server-Side_Includes_(SSI)_Injection
