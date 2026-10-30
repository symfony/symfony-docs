Program Status
==============

.. versionadded:: 8.2

    Program status reporting was introduced in Symfony 8.2.

Long-running applications (builds, deployments, coding agents, etc.)
alternate between working on their own, waiting for the user and
finishing. A Tui application can tell the terminal which of these it is
doing with the `Program Status Protocol`_ (OSC 7501). Supporting terminals
and multiplexers show it however they see fit: a tab indicator, a
notification, a list of the programs that need the user, etc.

Reporting a Status
------------------

Pass a ``ProgramStatus`` to the terminal of the Tui::

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

* ``Idle``: at rest, waiting for the next instruction of the user.
* ``Working``: running.
* ``Done``: finished a piece of work whose result the user has not seen
  yet.
* ``Blocked``: can't continue until the user does something.
* ``Error``: failed and stopped.

The other arguments are optional:

* ``message``: one human-readable line saying what the program is doing,
  waiting for or finished. Control characters are replaced by spaces and
  long messages are cut.
* ``app``: a stable identifier of the program, like ``cargo`` or
  ``terraform``, that terminals use for grouping, filtering or icons.
* ``kind``: what a blocked program waits for, as a ``ProgramBlockKind``
  case: ``Permission`` (an approval), ``Question`` (an answer to type) or
  ``Auth`` (a login, a token or a credential).
* ``progress``: a percentage for a working or blocked program. Leave it
  out when the program has no percentage to report.

For example, a program waiting for an approval reports::

    use Symfony\Component\Tui\Terminal\ProgramStatus\ProgramBlockKind;

    $terminal->setProgramStatus(new ProgramStatus(
        ProgramState::Blocked,
        'Approve the deploy to production?',
        app: 'deploy',
        kind: ProgramBlockKind::Permission,
    ));

Each report replaces the previous one as a whole, so repeat the ``app``
in every report. When the user cancels the work, report ``Idle``. A
program that exits right after finishing should report ``Done`` or
``Error`` before exiting, so the terminal keeps showing the outcome.

Reporting the Status of Tasks
-----------------------------

A program running several tasks (workers, sub-agents, deployments to
several regions, etc.) can report the status of each of them under its
own ``id``, along with a short ``title``. The program status and the task
statuses exist side by side::

    $terminal->setProgramStatus(
        new ProgramStatus(ProgramState::Working, app: 'deploy')
    );
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

A ``/`` in an id nests a task under another one: ``build/test`` is a task
of ``build``. Ids are made of up to 8 segments of letters, digits, ``_``,
``.``, ``+`` and ``-``.

Clearing a Status
-----------------

Remove the status of a task, and of the tasks nested under it, with
``clearProgramStatus()``. Without an id, it removes every status::

    // removes "us-east" and "us-east/*"
    $terminal->clearProgramStatus('us-east');

    // removes every status, the program one included
    $terminal->clearProgramStatus();

Terminal Support
----------------

Reports are sent as they are, without checking whether the terminal
supports the protocol: terminals ignore the sequences they don't know, so
reporting a status is always safe.

In tests, the ``VirtualTerminal`` writes every report to its output, so
you can assert on what the application reports.

.. _`Program Status Protocol`: https://www.superlogical.com/rex/docs/build/program-status
