``by_reference``
~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

In most cases, if you have an ``author`` field, then you expect ``setAuthor()``
to be called on the underlying object. In some cases, however, ``setAuthor()``
may *not* be called. Setting ``by_reference`` to ``false`` ensures that the setter is
called in all cases.

To explain this further, here's a simple example::

    use Symfony\Component\Form\Extension\Core\Type\EmailType;
    use Symfony\Component\Form\Extension\Core\Type\FormType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    // ...

    $builder = $this->createFormBuilder($article);
    $builder
        ->add('title', TextType::class)
        ->add(
            $builder->create('author', FormType::class, ['by_reference' => ?])
                ->add('name', TextType::class)
                ->add('email', EmailType::class)
        )

If ``by_reference`` is true, the following takes place behind the scenes
when you call ``submit()`` (or ``handleRequest()``) on the form::

    $article->setTitle('...');
    $article->getAuthor()->setName('...');
    $article->getAuthor()->setEmail('...');

Notice that ``setAuthor()`` is not called. The author is modified by reference.

If you set ``by_reference`` to false, submitting looks like this::

    $article->setTitle('...');
    $author = clone $article->getAuthor();
    $author->setName('...');
    $author->setEmail('...');
    $article->setAuthor($author);

So, all that ``by_reference=false`` really does is that it clones the object,
which enforces the framework to call the setter on the parent object.

Similarly, if you're using the :doc:`CollectionType </reference/forms/types/collection>`
field where your underlying collection data is an object (like with
Doctrine's ``ArrayCollection``), then ``by_reference`` must be set to ``false``
if you need the adder and remover (e.g. ``addAuthor()`` and ``removeAuthor()``)
to be called.
