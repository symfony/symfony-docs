Creating a Bug Reproducer
=========================

The main Symfony code repository receives thousands of issues reports per year.
Some of those issues are so obvious or easy to understand, that Symfony Core
developers can fix them without any other information. However, other issues are
much harder to understand because developers can't easily reproduce them in their
computers. That's when we'll ask you to create a "bug reproducer", which is the
minimum amount of code needed to make the bug appear when executed.

Reproducing Simple Bugs
-----------------------

If you are reporting a bug related to some Symfony component used outside the
Symfony framework, it's enough to share a small PHP script that when executed
shows the bug::

    // First, run "composer require symfony/validator"
    // Then, execute this file:
    <?php
    require_once __DIR__.'/../vendor/autoload.php';
    use Symfony\Component\Validator\Constraints;

    $wrongUrl = 'http://example.com/exploit.html?<script>alert(1);</script>';
    $urlValidator = new Constraints\UrlValidator();
    $urlConstraint = new Constraints\Url();

    // The URL is wrong, so var_dump() should display an error, but it displays
    // "null" instead because there is no context to build a validator violation
    var_dump($urlValidator->validate($wrongUrl, $urlConstraint));

Reproducing Complex Bugs
------------------------

If the bug is related to the Symfony Framework or if it's too complex to create
a PHP script, it's better to reproduce the bug by forking the Symfony Standard
edition. To do so:

1. Go to https://github.com/symfony/symfony-standard and click on the **Fork**
   button to make a fork of that repository or go to your already forked copy.
2. Clone the forked repository into your computer:
   ``git clone git://github.com/YOUR-GITHUB-USERNAME/symfony-standard.git``
3. Browse the project and create a new branch (e.g. ``issue_23567``,
   ``reproduce_23657``, etc.)
4. Now you must add the minimum amount of code to reproduce the bug. This is the
   trickiest part and it's explained a bit more later.
5. Add, commit and push all your changes.
6. Add a comment in your original issue report to share the URL of your forked
   project (e.g. ``https://github.com/YOUR-GITHUB-USERNAME/symfony-standard/tree/issue_23567``)
   and, if necessary, explain the steps to reproduce (e.g. "browse this URL",
   "fill in this data in the form and submit it", etc.)

Adding the Minimum Amount of Code Possible
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The key to create a bug reproducer is to solely focus on the feature that you
suspect is failing. For example, imagine that you suspect that the bug is related
to a route definition. Then, after forking the Symfony Standard Edition:

1. Don't edit any of the default Symfony configuration options.
2. Don't copy your original application code and don't use the same structure
   of bundles, controllers, actions, etc. as in your original application.
3. Open the default controller class of the AppBundle and add your routing
   definition using annotations.
4. Don't create or modify any other file.
5. Execute the ``server:run`` command and browse the previously defined route
   to see if the bug appears or not.
6. If you can see the bug, you're done and you can already share the code with us.
7. If you can't see the bug, you must keep making small changes. For example, if
   your original route was defined using XML, forget about the previous route
   annotation and define the route using XML instead. Or maybe your application
   uses bundle inheritance and that's where the real bug is. Then, forget about
   AppBundle and quickly generate a new AppParentBundle, make AppBundle inherit
   from it and test if the route is working.

In short, the idea is to keep adding small and incremental changes to the default
Symfony Standard edition until you can reproduce the bug.
