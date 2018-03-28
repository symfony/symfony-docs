Workflow
========

A workflow is a model of a process in your application. It may be the process
of how a blog post goes from draft, review and publish. Another example is when
a user submits a series of different forms to complete a task. Such processes are
best kept away from your models and should be defined in configuration.

A **definition** of a workflow consist of places and actions to get from one
place to another. The actions are called **transitions**. A workflow does also
need to know each object's position in the workflow. That **marking store** writes
to a property of the object to remember the current place.

.. note::

    The terminology above is commonly used when discussing workflows and
    `Petri nets`_

The Workflow component does also support state machines. A state machine is a subset
of a workflow and its purpose is to hold a state of your model. Read more about the
differences and specific features of state machine in :doc:`/workflow/state-machines`.

Examples
--------

The simplest workflow looks like this. It contains two places and one transition.

.. image:: /_images/components/workflow/simple.png

Workflows could be more complicated when they describe a real business case. The
workflow below describes the process to fill in a job application.

.. image:: /_images/components/workflow/job_application.png

When you fill in a job application in this example there are 4 to 7 steps depending
on the what job you are applying for. Some jobs require personality tests, logic tests
and/or formal requirements to be answered by the user. Some jobs don't. The
``GuardEvent`` is used to decide what next steps are allowed for a specific application.

By defining a workflow like this, there is an overview how the process looks like. The process
logic is not mixed with the controllers, models or view. The order of the steps can be changed
by changing the configuration only.

.. toctree::
   :maxdepth: 1
   :glob:

   workflow/*

.. _Petri nets: https://en.wikipedia.org/wiki/Petri_net
