.. index::
    single: Templating; Three-level inheritance pattern

How to Organize Your Twig Templates Using Inheritance
=====================================================

One common way to use inheritance is to use a three-level approach. This
method works perfectly with the three different types of templates that were just
covered:

* Create an ``templates/base.html.twig`` file that contains the main
  layout for your application (like in the previous example). Internally, this
  template is called ``base.html.twig``;

* Create a template for each "section" of your site. For example, the blog
  functionality would have a template called ``blog/layout.html.twig`` that
  contains only blog section-specific elements;

  .. code-block:: html+twig

      {# templates/blog/layout.html.twig #}
      {% extends 'base.html.twig' %}

      {% block body %}
          <h1>Blog Application</h1>

          {% block content %}{% endblock %}
      {% endblock %}

* Create individual templates for each page and make each extend the appropriate
  section template. For example, the "index" page would be called something
  close to ``blog/index.html.twig`` and list the actual blog posts.

  .. code-block:: html+twig

      {# templates/blog/index.html.twig #}
      {% extends 'blog/layout.html.twig' %}

      {% block content %}
          {% for entry in blog_entries %}
              <h2>{{ entry.title }}</h2>
              <p>{{ entry.body }}</p>
          {% endfor %}
      {% endblock %}

Notice that this template extends the section template (``blog/layout.html.twig``)
which in turn extends the base application layout (``base.html.twig``). This is
the common three-level inheritance model.

When building your application, you may choose to follow this method or simply
make each page template extend the base application template directly
(e.g. ``{% extends 'base.html.twig' %}``). The three-template model is a
best-practice method used by vendor bundles so that the base template for a
bundle can be easily overridden to properly extend your application's base
layout.
