.. index::
   single: PHP Templates

How to Use PHP instead of Twig for Templates
============================================

Symfony defaults to Twig for its template engine, but you can still use
plain PHP code if you want. Both templating engines are supported equally in
Symfony. Symfony adds some nice features on top of PHP to make writing
templates with PHP more powerful.

.. tip::

    If you choose *not* use Twig and you disable it, you'll need to implement
    your own exception handler via the ``kernel.exception`` event.

Rendering PHP Templates
-----------------------

If you want to use the PHP templating engine, first, make sure to enable it in
your application configuration file:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            # ...
            templating:
                engines: ['twig', 'php']

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <!-- ... -->
                <framework:templating>
                    <framework:engine id="twig" />
                    <framework:engine id="php" />
                </framework:templating>
            </framework:config>
        </container>

    .. code-block:: php

        $container->loadFromExtension('framework', array(
            // ...
            'templating' => array(
                'engines' => array('twig', 'php'),
            ),
        ));

You can now render a PHP template instead of a Twig one simply by using the
``.php`` extension in the template name instead of ``.twig``. The controller
below renders the ``index.html.php`` template::

    // src/AppBundle/Controller/HelloController.php

    // ...
    public function indexAction($name)
    {
        return $this->render(
            'AppBundle:Hello:index.html.php',
            array('name' => $name)
        );
    }

You can also use the `@Template`_ shortcut to render the default
``AppBundle:Hello:index.html.php`` template::

    // src/AppBundle/Controller/HelloController.php
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

    // ...

    /**
     * @Template(engine="php")
     */
    public function indexAction($name)
    {
        return array('name' => $name);
    }

.. caution::

    Enabling the ``php`` and ``twig`` template engines simultaneously is
    allowed, but it will produce an undesirable side effect in your application:
    the ``@`` notation for Twig namespaces will no longer be supported for the
    ``render()`` method::

        public function indexAction()
        {
            // ...

            // namespaced templates will no longer work in controllers
            $this->render('@App/Default/index.html.twig');

            // you must use the traditional template notation
            $this->render('AppBundle:Default:index.html.twig');
        }

    .. code-block:: twig

        {# inside a Twig template, namespaced templates work as expected #}
        {{ include('@App/Default/index.html.twig') }}

        {# traditional template notation will also work #}
        {{ include('AppBundle:Default:index.html.twig') }}


.. index::
  single: Templating; Layout
  single: Layout

Decorating Templates
--------------------

More often than not, templates in a project share common elements, like the
well-known header and footer. In Symfony, this problem is thought about
differently: a template can be decorated by another one.

The ``index.html.php`` template is decorated by ``layout.html.php``, thanks to
the ``extend()`` call:

.. code-block:: html+php

    <!-- app/Resources/views/Hello/index.html.php -->
    <?php $view->extend('AppBundle::layout.html.php') ?>

    Hello <?php echo $name ?>!

The ``AppBundle::layout.html.php`` notation sounds familiar, doesn't it? It
is the same notation used to reference a template. The ``::`` part simply
means that the controller element is empty, so the corresponding file is
directly stored under ``views/``.

Now, have a look at the ``layout.html.php`` file:

.. code-block:: html+php

    <!-- app/Resources/views/layout.html.php -->
    <?php $view->extend('::base.html.php') ?>

    <h1>Hello Application</h1>

    <?php $view['slots']->output('_content') ?>

The layout is itself decorated by another one (``::base.html.php``). Symfony
supports multiple decoration levels: a layout can itself be decorated by
another one. When the bundle part of the template name is empty, views are
looked for in the ``app/Resources/views/`` directory. This directory stores
global views for your entire project:

.. code-block:: html+php

    <!-- app/Resources/views/base.html.php -->
    <!DOCTYPE html>
    <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title><?php $view['slots']->output('title', 'Hello Application') ?></title>
        </head>
        <body>
            <?php $view['slots']->output('_content') ?>
        </body>
    </html>

For both layouts, the ``$view['slots']->output('_content')`` expression is
replaced by the content of the child template, ``index.html.php`` and
``layout.html.php`` respectively (more on slots in the next section).

As you can see, Symfony provides methods on a mysterious ``$view`` object. In
a template, the ``$view`` variable is always available and refers to a special
object that provides a bunch of methods that makes the template engine tick.

.. index::
   single: Templating; Slot
   single: Slot

Working with Slots
------------------

A slot is a snippet of code, defined in a template, and reusable in any layout
decorating the template. In the ``index.html.php`` template, define a
``title`` slot:

.. code-block:: html+php

    <!-- app/Resources/views/Hello/index.html.php -->
    <?php $view->extend('AppBundle::layout.html.php') ?>

    <?php $view['slots']->set('title', 'Hello World Application') ?>

    Hello <?php echo $name ?>!

The base layout already has the code to output the title in the header:

.. code-block:: html+php

    <!-- app/Resources/views/base.html.php -->
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php $view['slots']->output('title', 'Hello Application') ?></title>
    </head>

The ``output()`` method inserts the content of a slot and optionally takes a
default value if the slot is not defined. And ``_content`` is just a special
slot that contains the rendered child template.

For large slots, there is also an extended syntax:

.. code-block:: html+php

    <?php $view['slots']->start('title') ?>
        Some large amount of HTML
    <?php $view['slots']->stop() ?>

.. index::
   single: Templating; Include

Including other Templates
-------------------------

The best way to share a snippet of template code is to define a template that
can then be included into other templates.

Create a ``hello.html.php`` template:

.. code-block:: html+php

    <!-- app/Resources/views/Hello/hello.html.php -->
    Hello <?php echo $name ?>!

And change the ``index.html.php`` template to include it:

.. code-block:: html+php

    <!-- app/Resources/views/Hello/index.html.php -->
    <?php $view->extend('AppBundle::layout.html.php') ?>

    <?php echo $view->render('AppBundle:Hello:hello.html.php', array('name' => $name)) ?>

The ``render()`` method evaluates and returns the content of another template
(this is the exact same method as the one used in the controller).

.. index::
   single: Templating; Embedding pages

Embedding other Controllers
---------------------------

And what if you want to embed the result of another controller in a template?
That's very useful when working with Ajax, or when the embedded template needs
some variable not available in the main template.

If you create a ``fancy`` action, and want to include it into the
``index.html.php`` template, simply use the following code:

.. code-block:: html+php

    <!-- app/Resources/views/Hello/index.html.php -->
    <?php echo $view['actions']->render(
        new \Symfony\Component\HttpKernel\Controller\ControllerReference('AppBundle:Hello:fancy', array(
            'name'  => $name,
            'color' => 'green',
        ))
    ) ?>

Here, the ``AppBundle:Hello:fancy`` string refers to the ``fancy`` action of the
``Hello`` controller::

    // src/AppBundle/Controller/HelloController.php

    class HelloController extends Controller
    {
        public function fancyAction($name, $color)
        {
            // create some object, based on the $color variable
            $object = ...;

            return $this->render('AppBundle:Hello:fancy.html.php', array(
                'name'   => $name,
                'object' => $object
            ));
        }

        // ...
    }

But where is the ``$view['actions']`` array element defined? Like
``$view['slots']``, it's called a template helper, and the next section tells
you more about those.

.. index::
   single: Templating; Helpers

Using Template Helpers
----------------------

The Symfony templating system can be easily extended via helpers. Helpers are
PHP objects that provide features useful in a template context. ``actions`` and
``slots`` are two of the built-in Symfony helpers.

Creating Links between Pages
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Speaking of web applications, creating links between pages is a must. Instead
of hardcoding URLs in templates, the ``router`` helper knows how to generate
URLs based on the routing configuration. That way, all your URLs can be easily
updated by changing the configuration:

.. code-block:: html+php

    <a href="<?php echo $view['router']->generate('hello', array('name' => 'Thomas')) ?>">
        Greet Thomas!
    </a>

The ``generate()`` method takes the route name and an array of parameters as
arguments. The route name is the main key under which routes are referenced
and the parameters are the values of the placeholders defined in the route
pattern:

.. code-block:: yaml

    # src/AppBundle/Resources/config/routing.yml
    hello: # The route name
        path:     /hello/{name}
        defaults: { _controller: AppBundle:Hello:index }

Using Assets: Images, JavaScripts and Stylesheets
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

What would the Internet be without images, JavaScripts, and stylesheets?
Symfony provides the ``assets`` tag to deal with them easily:

.. code-block:: html+php

    <link href="<?php echo $view['assets']->getUrl('css/blog.css') ?>" rel="stylesheet" type="text/css" />

    <img src="<?php echo $view['assets']->getUrl('images/logo.png') ?>" />

The ``assets`` helper's main purpose is to make your application more
portable. Thanks to this helper, you can move the application root directory
anywhere under your web root directory without changing anything in your
template's code.

Profiling Templates
~~~~~~~~~~~~~~~~~~~

By using the ``stopwatch`` helper, you are able to time parts of your template
and display it on the timeline of the WebProfilerBundle::

    <?php $view['stopwatch']->start('foo') ?>
    ... things that get timed
    <?php $view['stopwatch']->stop('foo') ?>

.. tip::

    If you use the same name more than once in your template, the times are
    grouped on the same line in the timeline.

Output Escaping
---------------

When using PHP templates, escape variables whenever they are displayed to the
user::

    <?php echo $view->escape($var) ?>

By default, the ``escape()`` method assumes that the variable is outputted
within an HTML context. The second argument lets you change the context. For
instance, to output something in a JavaScript script, use the ``js`` context::

    <?php echo $view->escape($var, 'js') ?>

Form Theming in PHP
-------------------

When using PHP as a templating engine, the only way to customize a form fragment
is to create a new template file. The template file must be named after the
fragment. You must create a ``integer_widget.html.php`` file in order to
customize the ``integer_widget`` fragment.

.. code-block:: html+php

    <!-- app/Resources/views/form/integer_widget.html.php -->
    <div class="integer_widget">
        <?php echo $view['form']->block(
            $form,
            'form_widget_simple',
            array('type' => isset($type) ? $type : "number")
        ) ?>
    </div>

.. note::

    It is not possible to reference the base block when using PHP as the
    templating engine. You have to manually copy the content from the base block
    to your new template file.

Now that you've created the customized form template, you need to tell Symfony
to use it. Inside the template where you're actually rendering your form,
tell Symfony to use the theme via the ``setTheme()`` helper method::

    <?php $view['form']->setTheme($form, array(':form')); ?>

    <?php $view['form']->widget($form['age']) ?>

When the ``form.age`` widget is rendered, Symfony will use the customized
``integer_widget.html.php`` template and the ``input`` tag will be wrapped in
the ``div`` element.

If you want to apply a theme to a specific child form, pass it to the ``setTheme()``
method:

.. code-block:: php

    <?php $view['form']->setTheme($form['child'], ':form'); ?>

.. note::

    The ``:form`` syntax is based on the functional names for templates:
    ``Bundle:Directory``. As the form directory lives in the
    ``app/Resources/views`` directory, the ``Bundle`` part is empty, resulting
    in ``:form``.

Making Application-wide Customizations
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By using the following configuration, any customized form fragments inside the
``app/Resources/views/Form`` folder will be used globally when a form is
rendered.

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            templating:
                form:
                    resources:
                        - 'AppBundle:Form'
            # ...

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:templating>
                    <framework:form>
                        <framework:resource>AppBundle:Form</framework:resource>
                    </framework:form>
                </framework:templating>
                <!-- ... -->
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        // PHP
        $container->loadFromExtension('framework', array(
            'templating' => array(
                'form' => array(
                    'resources' => array(
                        'AppBundle:Form',
                    ),
                ),
             ),

             // ...
        ));

By default, the PHP engine uses a *div* layout when rendering forms. Some people,
however, may prefer to render forms in a *table* layout. Use the ``FrameworkBundle:FormTable``
resource to use such a layout:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            templating:
                form:
                    resources:
                        - 'FrameworkBundle:FormTable'

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:templating>
                    <framework:form>
                        <resource>FrameworkBundle:FormTable</resource>
                    </framework:form>
                </framework:templating>
                <!-- ... -->
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'templating' => array(
                'form' => array(
                    'resources' => array(
                        'FrameworkBundle:FormTable',
                    ),
                ),
            ),

             // ...
        ));

If you only want to make the change in one template, add the following line to
your template file rather than adding the template as a resource:

.. code-block:: html+php

    <?php $view['form']->setTheme($form, array('FrameworkBundle:FormTable')); ?>

Note that the ``$form`` variable in the above code is the form view variable
that you passed to your template.

Other common Form Customizations
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Adding a "Required" Asterisk to Field Labels
............................................

If you want to denote all of your required fields with a required asterisk
(``*``), you can do this by customizing the ``form_label`` fragment. When using
PHP as a templating engine you have to copy the content from the original
template:

.. code-block:: html+php

    <!-- form_label.html.php -->

    <!-- original content -->
    <?php if ($required) { $label_attr['class'] = trim((isset($label_attr['class']) ? $label_attr['class'] : '').' required'); } ?>
    <?php if (!$compound) { $label_attr['for'] = $id; } ?>
    <?php if (!$label) { $label = $view['form']->humanize($name); } ?>
    <label <?php foreach ($label_attr as $k => $v) { printf('%s="%s" ', $view->escape($k), $view->escape($v)); } ?>><?php echo $view->escape($view['translator']->trans($label, array(), $translation_domain)) ?></label>

    <!-- customization -->
    <?php if ($required) : ?>
        <span class="required" title="This field is required">*</span>
    <?php endif ?>

Adding "help" Messages
......................

You can also customize your form widgets to have an optional "help" message.
When using PHP as a templating engine you have to copy the content from the
original template:

.. code-block:: html+php

    <!-- form_widget_simple.html.php -->

    <!-- Original content -->
    <input
        type="<?php echo isset($type) ? $view->escape($type) : 'text' ?>"
        <?php if (!empty($value)): ?>value="<?php echo $view->escape($value) ?>"<?php endif ?>
        <?php echo $view['form']->block($form, 'widget_attributes') ?>
    />

    <!-- Customization -->
    <?php if (isset($help)) : ?>
        <span class="help"><?php echo $view->escape($help) ?></span>
    <?php endif ?>


.. _`@Template`: https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/view
