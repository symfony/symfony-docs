.. index::
   single: Workflow
   single: Components; Workflow

The Workflow Component
======================

    The Workflow component provides tools for managing a workflow or finite
    state machine.

.. versionadded:: 3.2
    The Workflow component was introduced in Symfony 3.2.

Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/workflow`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/workflow).

.. include:: /components/require_autoload.rst.inc

Creating a Workflow
-------------------

The workflow component gives you an object oriented way to define a process
or a life cycle that your object goes through. Each step or stage in the
process is called a *place*. You do also define *transitions* that describe
the action to get from one place to another.

.. image:: /_images/components/workflow/states_transitions.png

A set of places and transitions creates a **definition**. A workflow needs
a ``Definition`` and a way to write the states to the objects (i.e. an
instance of a :class:`Symfony\\Component\\Workflow\\MarkingStore\\MarkingStoreInterface`).

Consider the following example for a blog post. A post can have one of a number
of predefined statuses (`draft`, `review`, `rejected`, `published`). In a workflow,
these statuses are called **places**. You can define the workflow like this::

    use Symfony\Component\Workflow\Definition;
    use Symfony\Component\Workflow\Transition;
    use Symfony\Component\Workflow\Workflow;
    use Symfony\Component\Workflow\MarkingStore\ScalarMarkingStore;

    $states = ['draft', 'review', 'rejected', 'published'];

    // Transitions are defined with a unique name, an origin place and a destination place
    $transitions[] = new Transition('to_review', 'draft', 'review');
    $transitions[] = new Transition('publish', 'review', 'published');
    $transitions[] = new Transition('reject', 'review', 'rejected');

    $definition = new Definition($states, $transitions);
    $definition->setInitialPlace('draft');

    $marking = new ScalarMarkingStore('currentState');
    $workflow = new Workflow($definition, $marking);

The ``Workflow`` can now help you to decide what actions are allowed
on a blog post depending on what *place* it is in. This will keep your domain
logic in one place and not spread all over your application.

When you define multiple workflows you should consider using a ``Registry``,
which is an object that stores and provides access to different workflows.
A registry will also help you to decide if a workflow supports the object you
are trying to use it with::

    use Symfony\Component\Workflow\Registry;
    use Acme\Entity\BlogPost;
    use Acme\Entity\Newsletter;

    $blogWorkflow = ...
    $newsletterWorkflow = ...

    $registry = new Registry();
    $registry->add($blogWorkflow, BlogPost::class);
    $registry->add($newsletterWorkflow, Newsletter::class);

    // ...
    $post = new BlogPost();
    $workflow = $registry->get($post);

Learn more
----------

.. toctree::
    :maxdepth: 1
    :glob:

    /workflow
    /workflow/*

.. _Packagist: https://packagist.org/packages/symfony/workflow
