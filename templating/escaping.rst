.. index::
    single: Templating; Output escaping

How to Escape Output in Templates
=================================

When generating HTML from a template, there is always a risk that a template
variable may output unintended HTML or dangerous client-side code. The result
is that dynamic content could break the HTML of the resulting page or allow
a malicious user to perform a `Cross Site Scripting`_ (XSS) attack. Consider
this classic example:

.. code-block:: html+twig

    Hello {{ name }}

Imagine the user enters the following code for their name:

.. code-block:: html

    <script>alert('hello!')</script>

Without any output escaping, the resulting template will cause a JavaScript
alert box to pop up:

.. code-block:: html

    Hello <script>alert('hello!')</script>

And while this seems harmless, if a user can get this far, that same user
should also be able to write JavaScript that performs malicious actions
inside the secure area of an unknowing, legitimate user.

The answer to the problem is output escaping. With output escaping on, the
same template will render harmlessly, and literally print the ``script``
tag to the screen:

.. code-block:: html

    Hello &lt;script&gt;alert(&#39;hello!&#39;)&lt;/script&gt;

The Twig and PHP templating systems approach the problem in different ways.
If you're using Twig, output escaping is on by default and you're protected.
In PHP, output escaping is not automatic, meaning you'll need to manually
escape where necessary.

Output Escaping in Twig
-----------------------

If you're using Twig templates, then output escaping is on by default. This
means that you're protected out-of-the-box from the unintentional consequences
of user-submitted code. By default, the output escaping assumes that content
is being escaped for HTML output.

In some cases, you'll need to disable output escaping when you're rendering
a variable that is trusted and contains markup that should not be escaped.
Suppose that administrative users are able to write articles that contain
HTML code. By default, Twig will escape the article body.

To render it normally, add the ``raw`` filter:

.. code-block:: twig

    {{ article.body|raw }}

You can also disable output escaping inside a ``{% block %}`` area or
for an entire template. For more information, see `Output Escaping`_ in
the Twig documentation.

Output Escaping in PHP
----------------------

Output escaping is not automatic when using PHP templates. This means that
unless you explicitly choose to escape a variable, you're not protected. To
use output escaping, use the special ``escape()`` view method:

.. code-block:: html+php

    Hello <?php echo $view->escape($name) ?>

By default, the ``escape()`` method assumes that the variable is being rendered
within an HTML context (and thus the variable is escaped to be safe for HTML).
The second argument lets you change the context. For example, to output something
in a JavaScript string, use the ``js`` context:

.. code-block:: html+php

    var myMsg = 'Hello <?php echo $view->escape($name, 'js') ?>';

.. _`Cross Site Scripting`: https://en.wikipedia.org/wiki/Cross-site_scripting
.. _`Output Escaping`: http://twig.sensiolabs.org/doc/api.html#escaper-extension
