Referencing Entities with Abstract Classes and Interfaces
=========================================================

In applications where functionality is organized in layers or modules with
minimal concrete dependencies, such as monoliths split into multiple modules,
it can be challenging to avoid tight coupling between entities.

Doctrine provides a utility called the ``ResolveTargetEntityListener`` to solve
this issue. It works by intercepting certain calls within Doctrine and rewriting
``targetEntity`` parameters in your metadata mapping at runtime. This allows you
to reference an interface or abstract class in your mappings and have it resolved
to a concrete entity at runtime.

This makes it possible to define relationships between entities without
creating hard dependencies. This feature also works with the ``EntityValueResolver``
:ref:`as explained in the main Doctrine article <doctrine-entity-value-resolver-resolve-target-entities>`.

Background
----------

Suppose you have an application with two modules: an Invoice module that
provides invoicing functionality, and a Customer module that handles customer
management. You want to keep these modules decoupled, so that neither is aware
of the other's implementation details.

In this case, your ``Invoice`` entity has a relationship to the interface
``InvoiceSubjectInterface``. Since interfaces are not valid Doctrine entities,
the goal is to use the ``ResolveTargetEntityListener`` to replace all
references to this interface with a concrete class that implements it.

Set up
------

This article uses two basic (incomplete) entities to demonstrate how to set up
and use the ``ResolveTargetEntityListener``.

A ``Customer`` entity::

    // src/Entity/Customer.php
    namespace App\Entity;

    use App\Entity\CustomerInterface as BaseCustomer;
    use App\Model\InvoiceSubjectInterface;
    use Doctrine\ORM\Mapping as ORM;

    #[ORM\Entity]
    #[ORM\Table(name: 'customer')]
    class Customer extends BaseCustomer implements InvoiceSubjectInterface
    {
        // In this example, any methods defined in the InvoiceSubjectInterface
        // are already implemented in the BaseCustomer
    }

An ``Invoice`` entity::

    // src/Entity/Invoice.php
    namespace App\Entity;

    use App\Model\InvoiceSubjectInterface;
    use Doctrine\ORM\Mapping as ORM;

    #[ORM\Entity]
    #[ORM\Table(name: 'invoice')]
    class Invoice
    {
        #[ORM\ManyToOne(targetEntity: InvoiceSubjectInterface::class)]
        protected InvoiceSubjectInterface $subject;
    }

The interface representing the subject used in the invoice::

    // src/Model/InvoiceSubjectInterface.php
    namespace App\Model;

    /**
     * An interface that the invoice Subject object should implement.
     * In most circumstances, only a single object should implement
     * this interface as the ResolveTargetEntityListener can only
     * change the target to a single object.
     */
    interface InvoiceSubjectInterface
    {
        // List any additional methods that your InvoiceBundle
        // will need to access on the subject so that you can
        // be sure that you have access to those methods.

        public function getName(): string;
    }

Now configure the ``resolve_target_entities`` option to tell Doctrine
how to replace the interface with the concrete class:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/doctrine.yaml
        doctrine:
            # ...
            orm:
                # ...
                resolve_target_entities:
                    App\Model\InvoiceSubjectInterface: App\Entity\Customer

    .. code-block:: php

        // config/packages/doctrine.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Entity\Customer;
        use App\Model\InvoiceSubjectInterface;

        return App::config([
            'doctrine' => [
                'orm' => [
                    'resolve_target_entities' => [
                        InvoiceSubjectInterface::class => Customer::class,
                    ],
                ],
            ],
        ]);

Final Thoughts
--------------

Using ``ResolveTargetEntityListener`` allows you to decouple your modules
while still defining relationships between their entities. This makes your
codebase more modular and easier to maintain over time.
