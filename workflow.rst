Workflow
--------

A workflow is a model of a process in your application. It may be the process
of how a blog post goes from draft, review and publish. An other example is when
a user submitts a series of different forms to complete a task. Such process are
best kept away from your models and should be defined in configuration.

A state machine is a subset of a workflow and its purpose is to hold a state of
your model. Both the workflow and state machine defines what actions (transitions)
that are allowed on the model at each state.

.. toctree::
   :maxdepth: 1
   :glob:

   workflow/*
