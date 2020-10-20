Reporting a Bug
===============

Whenever you find a bug in Symfony, we kindly ask you to report it. It helps
us make a better Symfony.

.. caution::

    If you think you've found a security issue, please use the special
    :doc:`procedure <security>` instead.

Before submitting a bug:

* Double-check the official :doc:`documentation </index>` to see if you're not misusing the
  framework;

* Ask for assistance on `Stack Overflow`_, on the #support channel of
  `the Symfony Slack`_ or on the ``#symfony`` `IRC channel`_ if you're not sure if
  your issue really is a bug.

If your problem definitely looks like a bug, report it using the official bug
`tracker`_ and follow some basic rules:

* Use the title field to clearly describe the issue;

* Describe the steps needed to reproduce the bug with short code examples
  (providing a unit test that illustrates the bug is best);

* If the bug you experienced is not simple or affects more than one layer,
  providing a simple failing unit test may not be sufficient. In this case,
  please :doc:`provide a reproducer </contributing/code/reproducer>`;

* Give as much detail as possible about your environment (OS, PHP version,
  Symfony version, enabled extensions, ...);

* If there was an exception and you would like to report it, it is
  valuable to provide the :doc:`stack trace
  </contributing/code/stack_trace>` for that exception.
  If you want to provide a stack trace you got on an HTML page, be sure to
  provide the plain text version, which should appear at the bottom of the
  page. *Do not* provide it as a screenshot, since search engines will not be
  able to index the text inside them. Same goes for errors encountered in a
  terminal, do not take a screenshot, but copy/paste the contents. If
  the stack trace is long, consider enclosing it in a `<details> HTML tag`_.
  **Be wary that stack traces may contain sensitive information, and if it is
  the case, be sure to redact them prior to posting your stack trace.**

* *(optional)* Attach a :doc:`patch <pull_requests>`.

.. _`Stack Overflow`: https://stackoverflow.com/questions/tagged/symfony
.. _IRC channel: https://symfony.com/irc
.. _the Symfony Slack: https://symfony.com/slack-invite
.. _tracker: https://github.com/symfony/symfony/issues
.. _<details> HTML tag: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/details
