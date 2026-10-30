Program Status
==============

.. versionadded:: 8.2

    Program status reporting was introduced in Symfony 8.2.

Long-running applications (builds, deployments, coding agents, etc.)
alternate between working on their own, waiting for the user and
finishing their work. A Tui application can tell the terminal which of
these it's doing with the `Program Status Protocol`_ (OSC 7501). Terminals
and multiplexers that support this protocol decide how to display it: a tab
indicator, a notification, a list of the programs that need the user, etc.

Reporting a Status
------------------

Pass a ``ProgramStatus`` object to the ``setProgramStatus()`` method of the
terminal::

    use Symfony\Component\Tui\Terminal\ProgramStatus\ProgramState;
    use Symfony\Component\Tui\Terminal\ProgramStatus\ProgramStatus;

    $terminal = $tui->getTerminal();

    $terminal->setProgramStatus(
        new ProgramStatus(ProgramState::Working, 'Deploying', app: 'deploy')
    );

    // ... once the work is finished
    $terminal->setProgramStatus(
        new ProgramStatus(ProgramState::Done, 'Deployed', app: 'deploy')
    );

The state is one of the ``ProgramState`` cases:

* ``Idle``: the program is waiting for the next instruction of the user;
* ``Working``: the program is running;
* ``Done``: the program finished some work and the user hasn't seen the
  result yet;
* ``Blocked``: the program can't continue until the user does something;
* ``Error``: the program failed and stopped.

The other arguments are optional:

* ``message``: a single human-readable line that describes what the program
  is doing, waiting for or has finished. Control characters are replaced by
  spaces and long messages are truncated;
* ``app``: a stable identifier of the program (e.g. ``cargo`` or
  ``terraform``) that terminals use to group, filter or display icons;
* ``kind``: what a blocked program is waiting for, as a ``ProgramBlockKind``
  case: ``Permission`` (an approval), ``Question`` (an answer typed by the
  user) or ``Auth`` (a login, a token or other credentials);
* ``progress``: the completion percentage of a working or blocked program.
  Omit it when the program can't compute a percentage.

For example, this is how a program reports that it's waiting for an
approval::

    use Symfony\Component\Tui\Terminal\ProgramStatus\ProgramBlockKind;

    $terminal->setProgramStatus(new ProgramStatus(
        ProgramState::Blocked,
        'Approve the deploy to production?',
        app: 'deploy',
        kind: ProgramBlockKind::Permission,
    ));

Each report fully replaces the previous one, so include the ``app`` argument
in all reports. When the user cancels the work, report the ``Idle`` state.
If the program exits right after finishing its work, report the ``Done`` or
``Error`` state before exiting so the terminal keeps showing the outcome.

Reporting the Status of Tasks
-----------------------------

A program running several tasks (workers, sub-agents, deployments to
several regions, etc.) can report the status of each task using a unique
``id`` and a short ``title``. Task statuses don't replace the program status;
they are reported alongside it::

    // the status of the program itself
    $terminal->setProgramStatus(
        new ProgramStatus(ProgramState::Working, app: 'deploy')
    );

    // the status of each task
    $terminal->setProgramStatus(new ProgramStatus(
        ProgramState::Working,
        'Pushing image',
        progress: 40,
        id: 'us-east',
        title: 'US East',
    ));
    $terminal->setProgramStatus(new ProgramStatus(
        ProgramState::Done,
        'Healthy',
        id: 'eu-west',
        title: 'EU West',
    ));

Use ``/`` in an ID to nest a task inside another one (e.g. ``build/test`` is
a task of ``build``). IDs can contain up to 8 segments, each made of letters,
digits, ``_``, ``.``, ``+`` and ``-``.

Clearing a Status
-----------------

The ``clearProgramStatus()`` method removes the status of a task and the
statuses of all the tasks nested inside it. If you don't pass an ID, it
removes all statuses::

    // removes "us-east" and "us-east/*"
    $terminal->clearProgramStatus('us-east');

    // removes all statuses, including the status of the program itself
    $terminal->clearProgramStatus();

Terminal Support
----------------

Tui sends status reports without checking whether the terminal supports the
protocol. Terminals ignore the sequences they don't recognize, so reporting
a status is always safe.

In tests, ``VirtualTerminal`` writes all reports to its output, so you can
make assertions about the statuses reported by your application.

.. _`Program Status Protocol`: https://www.superlogical.com/rex/docs/build/program-status
