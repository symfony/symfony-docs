.. index::
   single: Forms; Choice Loaders

Choice Loaders
==============

The built-in :doc:`choice  Field Type </reference/forms/types/choice>` offers a
powerful way to render and handle a list of options the user can choose from.
These options are stored in objects called *choice lists*. By default, choice
lists are cached and will be reused throughout the form for increased
performance. In addition, the process of creating choice lists can also be
delegated to *choice loaders*.

There are multiple scenarios where using choice loaders is beneficial over
only providing a list of choices:

* You want to use *lazy loading* to load the choice list.
* You want to load the choice list only *partially* in cases where a
  fully-loaded list is not necessary (such as the user submitting the form
  through a PUT/POST request).
* You want to load the choice list from a data source with *custom logic*
  (such as a third-party API or a search engine).

The following sections describe how to setup a choice loader for each
of these use-cases.

Lazy loading for Choice Lists
-----------------------------

The most basic way to add lazy loading to your choice lists is to implement the
:class:`Symfony\\Component\\Form\\ChoiceList\\Loader\\CallbackChoiceLoader` class.
It accepts a callback as its only argument that will be called when the form
needs the data provided by the choice loader (e.g. when the form is rendered).

First, define the `choice_loader` option for the `ChoiceType` and use the
`CallbackChoiceLoader` class to set the callable that's executed to get the
list of choices::

    use AppBundle\Entity\Category;
    use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

    $builder->add('displayMode', ChoiceType::class, array(
        'choice_loader' => new CallbackChoiceLoader(function() {
            return Category::getDisplayModes();
        },
    ));

Creating a Choice Loader Class
------------------------------
