Templates
=========

When PHP was created 20 years ago, developers loved its simplicity and how
well it blended HTML and dynamic code. But as time passed, other template
languages - like `Twig`_ - were created to make templating even better.

.. best-practice::

    Use Twig templating format for your templates.

Generally speaking, PHP templates are more verbose than Twig templates because
they lack native support for lots of modern features needed by templates,
like inheritance, automatic escaping and named arguments for filters and
functions.

Twig is the default templating format in Symfony and has the largest community
support of all non-PHP template engines (it's used in high profile projects
such as Drupal 8).

Template Locations
------------------

.. best-practice::

    Store the application templates in the ``templates/`` directory at the root
    of your project.

Centralizing your templates in a single location simplifies the work of your
designers. In addition, using this directory simplifies the notation used when
referring to templates (e.g. ``$this->render('admin/post/show.html.twig')``
instead of ``$this->render('@SomeTwigNamespace/Admin/Posts/show.html.twig')``).

.. best-practice::

    Use lowercased snake_case for directory and template names.

This recommendation aligns with Twig best practices, where variables and template
names use lowercased snake_case too (e.g. ``user_profile`` instead of ``userProfile``
and ``edit_form.html.twig`` instead of ``EditForm.html.twig``).

.. best-practice::

    Use a prefixed underscore for partial templates in template names.

You often want to reuse template code using the ``include`` function to avoid
redundant code. To determine those partials easily in the filesystem you should
prefix partials and any other template without HTML body or ``extends`` tag
with a single underscore.

Twig Extensions
---------------

.. best-practice::

    Define your Twig extensions in the ``src/Twig/`` directory. Your
    application will automatically detect them and configure them.

Our application needs a custom ``md2html`` Twig filter so that we can transform
the Markdown contents of each post into HTML. To do this, create a new
``Markdown`` class that will be used later by the Twig extension. It just needs
to define one single method to transform Markdown content into HTML::

    namespace App\Utils;

    class Markdown
    {
        // ...

        public function toHtml(string $text): string
        {
            return $this->parser->text($text);
        }
    }

Next, create a new Twig extension and define a filter called ``md2html`` using
the ``TwigFilter`` class. Inject the newly defined ``Markdown`` class in the
constructor of the Twig extension::

    namespace App\Twig;

    use App\Utils\Markdown;
    use Twig\Extension\AbstractExtension;
    use Twig\TwigFilter;

    class AppExtension extends AbstractExtension
    {
        private $parser;

        public function __construct(Markdown $parser)
        {
            $this->parser = $parser;
        }

        public function getFilters()
        {
            return [
                new TwigFilter('md2html', [$this, 'markdownToHtml'], [
                    'is_safe' => ['html'],
                    'pre_escape' => 'html',
                ]),
            ];
        }

        public function markdownToHtml($content)
        {
            return $this->parser->toHtml($content);
        }
    }

And that's it!

If you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
you're done! Symfony will automatically know about your new service and tag it to
be used as a Twig extension.

----

Next: :doc:`/best_practices/forms`

.. _`Twig`: http://twig.sensiolabs.org/
.. _`Parsedown`: http://parsedown.org/
