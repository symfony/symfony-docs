How to Define Relationships with Abstract Classes and Interfaces
================================================================

One of the goals of bundles is to create discrete bundles of functionality
that do not have many (if any) dependencies, allowing you to use that
functionality in other applications without including unnecessary items.

Doctrine 2.2 includes a new utility called the ``ResolveTargetEntityListener``,
that functions by intercepting certain calls inside Doctrine and rewriting
``targetEntity`` parameters in your metadata mapping at runtime. It means that
in your bundle you are able to use an interface or abstract class in your
mappings and expect correct mapping to a concrete entity at runtime.

This functionality allows you to define relationships between different entities
without making them hard dependencies.

Background
----------

Suppose you have an InvoiceBundle which provides invoicing functionality
and a CustomerBundle that contains customer management tools. You want
to keep these separated, because they can be used in other systems without
each other, but for your application you want to use them together.

In this case, you have an ``Invoice`` entity with a relationship to a
non-existent object, an ``InvoiceSubjectInterface``. The goal is to get
the ``ResolveTargetEntityListener`` to replace any mention of the interface
with a real object that implements that interface.

Set up
------

This article uses the following two basic entities (which are incomplete for
brevity) to explain how to set up and use the ``ResolveTargetEntityListener``.

A Customer entity::

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

An Invoice entity::

    // src/Entity/Invoice.php
    namespace App\Entity;

    use App\Model\InvoiceSubjectInterface;
    use Doctrine\ORM\Mapping as ORM;

    /**
     * Represents an Invoice.
     */
    #[ORM\Entity]
    #[ORM\Table(name: 'invoice')]
    class Invoice
    {
        #[ORM\ManyToOne(targetEntity: InvoiceSubjectInterface::class)]
        protected InvoiceSubjectInterface $subject;
    }

An InvoiceSubjectInterface::

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

Next, you need to configure the listener, which tells the DoctrineBundle
about the replacement:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/doctrine.yaml
        doctrine:
            # ...
            orm:
                # ...
                resolve_target_entities:
                    App\Model\InvoiceSubjectInterface: App\Entity\Customer

    .. code-block:: xml

        <!-- config/packages/doctrine.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/doctrine
                https://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd">

            <doctrine:config>
                <doctrine:orm>
                    <!-- ... -->
                    <doctrine:resolve-target-entity interface="App\Model\InvoiceSubjectInterface">App\Entity\Customer</doctrine:resolve-target-entity>
                </doctrine:orm>
            </doctrine:config>
        </container>

    .. code-block:: php

        // config/packages/doctrine.php
        use App\Entity\Customer;
        use App\Model\InvoiceSubjectInterface;
        use Symfony\Config\DoctrineConfig;

        return static function (DoctrineConfig $doctrine): void {
            $orm = $doctrine->orm();
            // ...
            $orm->resolveTargetEntity(InvoiceSubjectInterface::class, Customer::class);
        };

Final Thoughts
--------------

With the ``ResolveTargetEntityListener``, you are able to decouple your
bundles, keeping them usable by themselves, but still being able to
define relationships between different objects. By using this method,
your bundles will end up being easier to maintain independently.
