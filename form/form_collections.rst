How to Embed a Collection of Forms
==================================

Symfony Forms can embed a collection of many other forms, which is useful to
edit related entities in a single form. In this article, you'll create a form to
edit a ``Task`` class and, right inside the same form, you'll be able to edit,
create and remove many ``Tag`` objects related to that Task.

Let's start by creating a ``Task`` entity::

    // src/Entity/Task.php
    namespace App\Entity;

    use Doctrine\Common\Collections\ArrayCollection;
    use Doctrine\Common\Collections\Collection;

    class Task
    {
        protected $description;
        protected $tags;

        public function __construct()
        {
            $this->tags = new ArrayCollection();
        }

        public function getDescription(): string
        {
            return $this->description;
        }

        public function setDescription(string $description): void
        {
            $this->description = $description;
        }

        public function getTags(): Collection
        {
            return $this->tags;
        }
    }

.. note::

    The `ArrayCollection`_ is specific to Doctrine and is similar to a PHP array
    but provides many utility methods.

Now, create a ``Tag`` class. As you saw above, a ``Task`` can have many ``Tag``
objects::

    // src/Entity/Tag.php
    namespace App\Entity;

    class Tag
    {
        private $name;

        public function getName(): string
        {
            return $this->name;
        }

        public function setName(string $name): void
        {
            $this->name = $name;
        }
    }

Then, create a form class so that a ``Tag`` object can be modified by the user::

    // src/Form/TagType.php
    namespace App\Form;

    use App\Entity\Tag;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class TagType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder->add('name');
        }

        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'data_class' => Tag::class,
            ]);
        }
    }

Next, let's create a form for the ``Task`` entity, using a
:doc:`CollectionType </reference/forms/types/collection>` field of ``TagType``
forms. This will allow us to modify all the ``Tag`` elements of a ``Task`` right
inside the task form itself::

    // src/Form/TaskType.php
    namespace App\Form;

    use App\Entity\Task;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\CollectionType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class TaskType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder->add('description');

            $builder->add('tags', CollectionType::class, [
                'entry_type' => TagType::class,
                'entry_options' => ['label' => false],
            ]);
        }

        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'data_class' => Task::class,
            ]);
        }
    }

In your controller, you'll create a new form from the ``TaskType``::

    // src/Controller/TaskController.php
    namespace App\Controller;

    use App\Entity\Tag;
    use App\Entity\Task;
    use App\Form\TaskType;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;

    class TaskController extends AbstractController
    {
        public function new(Request $request): Response
        {
            $task = new Task();

            // dummy code - add some example tags to the task
            // (otherwise, the template will render an empty list of tags)
            $tag1 = new Tag();
            $tag1->setName('tag1');
            $task->getTags()->add($tag1);
            $tag2 = new Tag();
            $tag2->setName('tag2');
            $task->getTags()->add($tag2);
            // end dummy code

            $form = $this->createForm(TaskType::class, $task);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // ... do your form processing, like saving the Task and Tag entities
            }

            return $this->renderForm('task/new.html.twig', [
                'form' => $form,
            ]);
        }
    }

In the template, you can now iterate over the existing ``TagType`` forms
to render them:

.. code-block:: html+twig

    {# templates/task/new.html.twig #}

    {# ... #}

    {{ form_start(form) }}
        {{ form_row(form.description) }}

        <h3>Tags</h3>
        <ul class="tags">
            {% for tag in form.tags %}
                <li>{{ form_row(tag.name) }}</li>
            {% endfor %}
        </ul>
    {{ form_end(form) }}

    {# ... #}

When the user submits the form, the submitted data for the ``tags`` field is
used to construct an ``ArrayCollection`` of ``Tag`` objects. The collection is
then set on the ``tag`` field of the ``Task`` and can be accessed via ``$task->getTags()``.

So far, this works great, but only to edit *existing* tags. It doesn't allow us
yet to add new tags or delete existing ones.

.. caution::

    You can embed nested collections as many levels down as you like. However,
    if you use Xdebug, you may receive a ``Maximum function nesting level of '100'
    reached, aborting!`` error. To fix this, increase the ``xdebug.max_nesting_level``
    PHP setting, or render each form field by hand using ``form_row()`` instead of
    rendering the whole form at once (e.g ``form_widget(form)``).

.. _form-collections-new-prototype:

Allowing "new" Tags with the "Prototype"
----------------------------------------

Previously you added two tags to your task in the controller. Now let the users
add as many tag forms as they need directly in the browser. This requires a bit
of JavaScript code.

But first, you need to let the form collection know that instead of exactly two,
it will receive an *unknown* number of tags. Otherwise, you'll see a
*"This form should not contain extra fields"* error. This is done with the
``allow_add`` option::

    // src/Form/TaskType.php

    // ...

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // ...

        $builder->add('tags', CollectionType::class, [
            'entry_type' => TagType::class,
            'entry_options' => ['label' => false],
            'allow_add' => true,
        ]);
    }

The ``allow_add`` option also makes a ``prototype`` variable available to you.
This "prototype" is a little "template" that contains all the HTML needed to
dynamically create any new "tag" forms with JavaScript. To render the prototype, add
the following ``data-prototype`` attribute to the existing ``<ul>`` in your
template:

.. code-block:: html+twig

    {# the data-index attribute is required for the JavaScript code below #}
    <ul class="tags"
        data-index="{{ form.tags|length > 0 ? form.tags|last.vars.name + 1 : 0 }}"
        data-prototype="{{ form_widget(form.tags.vars.prototype)|e('html_attr') }}"
    ></ul>

On the rendered page, the result will look something like this:

.. code-block:: html

    <ul class="tags"
        data-index="0"
        data-prototype="&lt;div&gt;&lt;label class=&quot; required&quot;&gt;__name__&lt;/label&gt;&lt;div id=&quot;task_tags___name__&quot;&gt;&lt;div&gt;&lt;label for=&quot;task_tags___name___name&quot; class=&quot; required&quot;&gt;Name&lt;/label&gt;&lt;input type=&quot;text&quot; id=&quot;task_tags___name___name&quot; name=&quot;task[tags][__name__][name]&quot; required=&quot;required&quot; maxlength=&quot;255&quot; /&gt;&lt;/div&gt;&lt;/div&gt;&lt;/div&gt;"
    ></ul>

Now add a button to dynamically add a new tag:

.. code-block:: html+twig

    <button type="button" class="add_item_link" data-collection-holder-class="tags">Add a tag</button>

.. seealso::

    If you want to customize the HTML code in the prototype, see
    :ref:`form-custom-prototype`.

.. tip::

    The ``form.tags.vars.prototype`` is a form element that looks and feels just
    like the individual ``form_widget(tag.*)`` elements inside your ``for`` loop.
    This means that you can call ``form_widget()``, ``form_row()`` or ``form_label()``
    on it. You could even choose to render only one of its fields (e.g. the
    ``name`` field):

    .. code-block:: twig

        {{ form_widget(form.tags.vars.prototype.name)|e }}

.. note::

    If you render your whole "tags" sub-form at once (e.g. ``form_row(form.tags)``),
    the ``data-prototype`` attribute is automatically added to the containing ``div``,
    and you need to adjust the following JavaScript accordingly.

Now add some JavaScript to read this attribute and dynamically add new tag forms
when the user clicks the "Add a tag" link. Add a ``<script>`` tag somewhere
on your page to include the required functionality with JavaScript:

.. code-block:: javascript

    document
      .querySelectorAll('.add_item_link')
      .forEach(btn => {
          btn.addEventListener("click", addFormToCollection)
      });

The ``addFormToCollection()`` function's job will be to use the ``data-prototype``
attribute to dynamically add a new form when this link is clicked. The ``data-prototype``
HTML contains the tag's ``text`` input element with a name of ``task[tags][__name__][name]``
and id of ``task_tags___name___name``. The ``__name__`` is a placeholder, which
you'll replace with a unique, incrementing number (e.g. ``task[tags][3][name]``):

.. code-block:: javascript

    const addFormToCollection = (e) => {
      const collectionHolder = document.querySelector('.' + e.currentTarget.dataset.collectionHolderClass);

      const item = document.createElement('li');

      item.innerHTML = collectionHolder
        .dataset
        .prototype
        .replace(
          /__name__/g,
          collectionHolder.dataset.index
        );

      collectionHolder.appendChild(item);

      collectionHolder.dataset.index++;
    };

Now, each time a user clicks the ``Add a tag`` link, a new sub form will
appear on the page. When the form is submitted, any new tag forms will be converted
into new ``Tag`` objects and added to the ``tags`` property of the ``Task`` object.

.. seealso::

    You can find a working example in this `JSFiddle`_.

To make handling these new tags easier, add an "adder" and a "remover" method
for the tags in the ``Task`` class::

    // src/Entity/Task.php
    namespace App\Entity;

    // ...
    class Task
    {
        // ...

        public function addTag(Tag $tag): void
        {
            $this->tags->add($tag);
        }

        public function removeTag(Tag $tag): void
        {
            // ...
        }
    }

Next, add a ``by_reference`` option to the ``tags`` field and set it to ``false``::

    // src/Form/TaskType.php

    // ...
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // ...

        $builder->add('tags', CollectionType::class, [
            // ...
            'by_reference' => false,
        ]);
    }

With these two changes, when the form is submitted, each new ``Tag`` object
is added to the ``Task`` class by calling the ``addTag()`` method. Before this
change, they were added internally by the form by calling ``$task->getTags()->add($tag)``.
That was fine, but forcing the use of the "adder" method makes handling
these new ``Tag`` objects easier (especially if you're using Doctrine, which
you will learn about next!).

.. caution::

    You have to create **both** ``addTag()`` and ``removeTag()`` methods,
    otherwise the form will still use ``setTag()`` even if ``by_reference`` is ``false``.
    You'll learn more about the ``removeTag()`` method later in this article.

.. caution::

    Symfony can only make the plural-to-singular conversion (e.g. from the
    ``tags`` property to the ``addTag()`` method) for English words. Code
    written in any other language won't work as expected.

.. sidebar:: Doctrine: Cascading Relations and saving the "Inverse" side

    To save the new tags with Doctrine, you need to consider a couple more
    things. First, unless you iterate over all of the new ``Tag`` objects and
    call ``$entityManager->persist($tag)`` on each, you'll receive an error from
    Doctrine:

    .. code-block:: text

        A new entity was found through the relationship
        ``App\Entity\Task#tags`` that was not configured to
        cascade persist operations for entity...

    To fix this, you may choose to "cascade" the persist operation automatically
    from the ``Task`` object to any related tags. To do this, add the ``cascade``
    option to your ``ManyToMany`` metadata:

    .. configuration-block::

        .. code-block:: php-annotations

            // src/Entity/Task.php

            // ...

            /**
             * @ORM\ManyToMany(targetEntity="App\Entity\Tag", cascade={"persist"})
             */
            protected $tags;

        .. code-block:: yaml

            # src/Resources/config/doctrine/Task.orm.yaml
            App\Entity\Task:
                type: entity
                # ...
                oneToMany:
                    tags:
                        targetEntity: App\Entity\Tag
                        cascade:      [persist]

        .. code-block:: xml

            <!-- src/Resources/config/doctrine/Task.orm.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                                https://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">

                <entity name="App\Entity\Task">
                    <!-- ... -->
                    <one-to-many field="tags" target-entity="Tag">
                        <cascade>
                            <cascade-persist/>
                        </cascade>
                    </one-to-many>
                </entity>
            </doctrine-mapping>

    A second potential issue deals with the `Owning Side and Inverse Side`_
    of Doctrine relationships. In this example, if the "owning" side of the
    relationship is "Task", then persistence will work fine as the tags are
    properly added to the Task. However, if the owning side is on "Tag", then
    you'll need to do a little bit more work to ensure that the correct side
    of the relationship is modified.

    The trick is to make sure that the single "Task" is set on each "Tag".
    One way to do this is to add some extra logic to ``addTag()``, which
    is called by the form type since ``by_reference`` is set to ``false``::

        // src/Entity/Task.php

        // ...
        public function addTag(Tag $tag): void
        {
            // for a many-to-many association:
            $tag->addTask($this);

            // for a many-to-one association:
            $tag->setTask($this);

            $this->tags->add($tag);
        }

    If you're going for ``addTask()``, make sure you have an appropriate method
    that looks something like this::

        // src/Entity/Tag.php

        // ...
        public function addTask(Task $task): void
        {
            if (!$this->tasks->contains($task)) {
                $this->tasks->add($task);
            }
        }

.. _form-collections-remove:

Allowing Tags to be Removed
---------------------------

The next step is to allow the deletion of a particular item in the collection.
The solution is similar to allowing tags to be added.

Start by adding the ``allow_delete`` option in the form Type::

    // src/Form/TaskType.php

    // ...
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // ...

        $builder->add('tags', CollectionType::class, [
            // ...
            'allow_delete' => true,
        ]);
    }

Now, you need to put some code into the ``removeTag()`` method of ``Task``::

    // src/Entity/Task.php

    // ...
    class Task
    {
        // ...

        public function removeTag(Tag $tag): void
        {
            $this->tags->removeElement($tag);
        }
    }


The ``allow_delete`` option means that if an item of a collection
isn't sent on submission, the related data is removed from the collection
on the server. In order for this to work in an HTML form, you must remove
the DOM element for the collection item to be removed, before submitting
the form.

First, add a "delete this tag" link to each tag form:

.. code-block:: javascript

    document
        .querySelectorAll('ul.tags li')
        .forEach((tag) => {
            addTagFormDeleteLink(tag)
        })

    // ... the rest of the block from above

    const addFormToCollection = (e) => {
        // ...

        // add a delete link to the new form
        addTagFormDeleteLink(item);
    }

The ``addTagFormDeleteLink()`` function will look something like this:

.. code-block:: javascript

    const addTagFormDeleteLink = (item) => {
        const removeFormButton = document.createElement('button');
        removeFormButton.innerText = 'Delete this tag';

        item.append(removeFormButton);

        removeFormButton.addEventListener('click', (e) => {
            e.preventDefault();
            // remove the li for the tag form
            item.remove();
        });
    }

When a tag form is removed from the DOM and submitted, the removed ``Tag`` object
will not be included in the collection passed to ``setTags()``. Depending on
your persistence layer, this may or may not be enough to actually remove
the relationship between the removed ``Tag`` and ``Task`` object.

.. sidebar:: Doctrine: Ensuring the database persistence

    When removing objects in this way, you may need to do a little bit more
    work to ensure that the relationship between the ``Task`` and the removed
    ``Tag`` is properly removed.

    In Doctrine, you have two sides of the relationship: the owning side and the
    inverse side. Normally in this case you'll have a many-to-one relationship
    and the deleted tags will disappear and persist correctly (adding new
    tags also works effortlessly).

    But if you have a one-to-many relationship or a many-to-many relationship with a
    ``mappedBy`` on the Task entity (meaning Task is the "inverse" side),
    you'll need to do more work for the removed tags to persist correctly.

    In this case, you can modify the controller to remove the relationship
    on the removed tag. This assumes that you have some ``edit()`` action which
    is handling the "update" of your Task::

        // src/Controller/TaskController.php

        // ...
        use App\Entity\Task;
        use Doctrine\Common\Collections\ArrayCollection;

        class TaskController extends AbstractController
        {
            public function edit(Task $task, Request $request, EntityManagerInterface $entityManager): Response
            {
                $originalTags = new ArrayCollection();

                // Create an ArrayCollection of the current Tag objects in the database
                foreach ($task->getTags() as $tag) {
                    $originalTags->add($tag);
                }

                $editForm = $this->createForm(TaskType::class, $task);

                $editForm->handleRequest($request);

                if ($editForm->isSubmitted() && $editForm->isValid()) {
                    // remove the relationship between the tag and the Task
                    foreach ($originalTags as $tag) {
                        if (false === $task->getTags()->contains($tag)) {
                            // remove the Task from the Tag
                            $tag->getTasks()->removeElement($task);

                            // if it was a many-to-one relationship, remove the relationship like this
                            // $tag->setTask(null);

                            $entityManager->persist($tag);

                            // if you wanted to delete the Tag entirely, you can also do that
                            // $entityManager->remove($tag);
                        }
                    }

                    $entityManager->persist($task);
                    $entityManager->flush();

                    // redirect back to some edit page
                    return $this->redirectToRoute('task_edit', ['id' => $id]);
                }

                // ... render some form template
            }
        }

    As you can see, adding and removing the elements correctly can be tricky.
    Unless you have a many-to-many relationship where Task is the "owning" side,
    you'll need to do extra work to make sure that the relationship is properly
    updated (whether you're adding new tags or removing existing tags) on
    each Tag object itself.

.. seealso::

    The Symfony community has created some JavaScript packages that provide the
    functionality needed to add, edit and delete elements of the collection.
    Check out the `@a2lix/symfony-collection`_ package for modern browsers and
    the `symfony-collection`_ package based on jQuery for the rest of browsers.

.. _`Owning Side and Inverse Side`: https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/unitofwork-associations.html
.. _`JSFiddle`: https://jsfiddle.net/ey8ozh6n/
.. _`@a2lix/symfony-collection`: https://github.com/a2lix/symfony-collection
.. _`symfony-collection`: https://github.com/ninsuo/symfony-collection
.. _`ArrayCollection`: https://www.doctrine-project.org/projects/doctrine-collections/en/1.6/index.html
