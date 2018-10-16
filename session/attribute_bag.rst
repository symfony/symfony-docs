.. index::
   single: Sessions, Attribute Bag

Changing the Session Attribute Bag provider
===========================================

Symfony uses attribute bags to represent values stored in the session. It ships 
with the default :class:`Symfony\\Component\\HttpFoundation\\Session\\Attribute\\AttributeBag`, 
which stores key-value pairs, and the 
:class:`Symfony\\Component\\HttpFoundation\\Session\\Attribute\\NamespacedAttributeBag`, 
which allows you to :ref:`set and fetch values using character-separated paths <attribute-bag-interface>`.

This is fully documented :doc:`in the component documentation </components/http_foundation/sessions>`.

Changing the attribute bag in Symfony
-------------------------------------
To use a different attribute bag for your sessions, override the service 
definition. In this example, we switch the default ``AttributeBag`` to the 
``NamespacedAttributeBag``:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        session:
            class: Symfony\Component\HttpFoundation\Session\Session
            arguments: ["@session.storage", "@session.namespacedattributebag", "@session.flash_bag"]

        session.namespacedattributebag:
            class: Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag