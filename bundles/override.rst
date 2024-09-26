How to Override any Part of a Bundle
====================================

When using a third-party bundle, you might want to customize or override some of
its features. This document describes ways of overriding the most common
features of a bundle.

.. _override-templates:

Templates
---------

Third-party bundle templates can be overridden in the
``<your-project>/templates/bundles/<bundle-name>/`` directory. The new templates
must use the same name and path (relative to ``<bundle>/templates/``) as
the original templates.

For example, to override the ``templates/registration/confirmed.html.twig``
template from the AcmeUserBundle, create this template:
``<your-project>/templates/bundles/AcmeUserBundle/registration/confirmed.html.twig``

.. caution::

    If you add a template in a new location, you *may* need to clear your
    cache (``php bin/console cache:clear``), even if you are in debug mode.

Instead of overriding an entire template, you may just want to override one or
more blocks. However, since you are overriding the template you want to extend
from, you would end up in an infinite loop error. The solution is to use the
special ``!`` prefix in the template name to tell Symfony that you want to
extend from the original template, not from the overridden one:

.. code-block:: twig

    {# templates/bundles/AcmeUserBundle/registration/confirmed.html.twig #}
    {# the special '!' prefix avoids errors when extending from an overridden template #}
    {% extends "@!AcmeUser/registration/confirmed.html.twig" %}

    {% block some_block %}
        ...
    {% endblock %}

.. _templating-overriding-core-templates:

.. tip::

    Symfony internals use some bundles too, so you can apply the same technique
    to override the core Symfony templates. For example, you can
    :doc:`customize error pages </controller/error_pages>` overriding TwigBundle
    templates.

Routing
-------

Routing is never automatically imported in Symfony. If you want to include
the routes from any bundle, then they must be manually imported from somewhere
in your application (e.g. ``config/routes.yaml``).

The easiest way to "override" a bundle's routing is to never import it at
all. Instead of importing a third-party bundle's routing, copy
that routing file into your application, modify it, and import it instead.

Controllers
-----------

If the controller is a service, see the next section on how to override it.
Otherwise, define a new route + controller with the same path associated to the
controller you want to override (and make sure that the new route is loaded
before the bundle one).

Services & Configuration
------------------------

If you want to modify the services created by a bundle, you can use
:doc:`service decoration </service_container/service_decoration>`.

If you want to do more advanced manipulations, like removing services created by
other bundles, you must work with :doc:`service definitions </service_container/definitions>`
inside a :doc:`compiler pass </service_container/compiler_passes>`.

Entities & Entity Mapping
-------------------------

Overriding entity mapping is only possible if a bundle provides a mapped
superclass (such as the ``User`` entity in the FOSUserBundle). It's possible to
override attributes and associations in this way. Learn more about this feature
and its limitations in `the Doctrine documentation`_.

Forms
-----

Existing form types can be modified defining
:doc:`form type extensions </form/create_form_type_extension>`.

.. _override-validation:

Validation Metadata
-------------------

Symfony loads all validation configuration files from every bundle and
combines them into one validation metadata tree. This means you are able to
add new constraints to a property, but you cannot override them.

To overcome this, the 3rd party bundle needs to have configuration for
:doc:`validation groups </validation/groups>`. For instance, the FOSUserBundle
has this configuration. To create your own validation, add the constraints
to a new validation group:

.. configuration-block::

    .. code-block:: yaml

        # config/validator/validation.yaml
        FOS\UserBundle\Model\User:
            properties:
                plainPassword:
                    - NotBlank:
                        groups: [AcmeValidation]
                    - Length:
                        min: 6
                        minMessage: fos_user.password.short
                        groups: [AcmeValidation]

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping
                https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd"
        >
            <class name="FOS\UserBundle\Model\User">
                <property name="plainPassword">
                    <constraint name="NotBlank">
                        <option name="groups">
                            <value>AcmeValidation</value>
                        </option>
                    </constraint>

                    <constraint name="Length">
                        <option name="min">6</option>
                        <option name="minMessage">fos_user.password.short</option>
                        <option name="groups">
                            <value>AcmeValidation</value>
                        </option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

Now, update the FOSUserBundle configuration, so it uses your validation groups
instead of the original ones.

.. _override-translations:

Translations
------------

Translations are not related to bundles, but to translation domains.
For this reason, you can override any bundle translation file from the main
``translations/`` directory, as long as the new file uses the same domain.

For example, to override the translations defined in the
``translations/AcmeUserBundle.es.yaml`` file of the AcmeUserBundle,
create a ``<your-project>/translations/AcmeUserBundle.es.yaml`` file.

.. _`the Doctrine documentation`: https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/inheritance-mapping.html#overrides
