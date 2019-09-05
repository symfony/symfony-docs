.. index::
   single: Form; Embed collection of forms

How to Embed a Collection of Forms
==================================

In this article, you'll learn how to create a form that embeds a collection
of many other forms. This could be useful, for example, if you had a ``Task``
class and you wanted to edit/create/remove many ``Tag`` objects related to
that Task, right inside the same form.

.. note::

    In this article, it's loosely assumed that you're using Doctrine as your
    database store. But if you're not using Doctrine, it's all very similar.
    There are only a few parts of this tutorial that really care about
    persistence.

    If you *are* using Doctrine, you'll need to add the Doctrine metadata,
    including the ``ManyToMany`` association mapping definition on the
    ``tags`` property of ``Task``.

First, suppose that each ``Task`` belongs to multiple ``Tag`` objects. Start
by creating a simple ``Task`` class::

    // src/App/Entity/Task.php
    namespace App\Entity;

    use Doctrine\Common\Collections\ArrayCollection;

    class Task
    {
        protected $description;

        protected $tags;

        public function __construct()
        {
            $this->tags = new ArrayCollection();
        }

        public function getDescription()
        {
            return $this->description;
        }

        public function setDescription($description)
        {
            $this->description = $description;
        }

        public function getTags()
        {
            return $this->tags;
        }
    }

.. note::

    The ``ArrayCollection`` is specific to Doctrine and is basically the
    same as using an ``array`` (but it must be an ``ArrayCollection`` if
    you're using Doctrine).

Now, create a ``Tag`` class. As you saw above, a ``Task`` can have many ``Tag``
objects::

    // src/App/Entity/Tag.php
    namespace App\Entity;

    class Tag
    {
        private $name;

        public function getName()
        {
            return $this->name;
        }

        public function setName($name)
        {
            $this->name = $name;
        }
    }

Then, create a form class so that a ``Tag`` object can be modified by the user::

    // src/App/Form/TagType.php
    namespace App\Form;

    use App\Entity\Tag;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class TagType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder->add('name');
        }

        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults(array(
                'data_class' => Tag::class,
            ));
        }
    }

With this, you have enough to render a tag form by itself. But since the end
goal is to allow the tags of a ``Task`` to be modified right inside the task
form itself, create a form for the ``Task`` class.

Notice that you embed a collection of ``TagType`` forms using the
:doc:`CollectionType </reference/forms/types/collection>` field::

    // src/App/Form/TaskType.php
    namespace App\Form;

    use App\Entity\Task;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;
    use Symfony\Component\Form\Extension\Core\Type\CollectionType;

    class TaskType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder->add('description');

            $builder->add('tags', CollectionType::class, array(
                'entry_type' => TagType::class,
                'entry_options' => array('label' => false),
            ));
        }

        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults(array(
                'data_class' => Task::class,
            ));
        }
    }

In your controller, you'll create a new form from the ``TaskType``::

    // src/App/Controller/TaskController.php
    namespace App\Controller;

    use App\Entity\Task;
    use App\Entity\Tag;
    use App\Form\TaskType;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Routing\Annotation\Route;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

    class TaskController extends AbstractController
    {
        /**
         * @Route("/task/new")
         */
        public function new(Request $request)
        {
            $task = new Task();

            // dummy code - this is here just so that the Task has some tags
            // otherwise, this isn't an interesting example
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
                // ... maybe do some form processing, like saving the Task and Tag objects
            }

            return $this->render('task/new.html.twig', array(
                'form' => $form->createView(),
            ));
        }
    }

The corresponding template is now able to render both the ``description``
field for the task form as well as all the ``TagType`` forms for any tags
that are already related to this ``Task``. The above controller has some
dummy code so that you can see this in action (since a newly created ``Task``
has zero tags).

.. code-block:: html+twig

    {# templates/task/new.html.twig #}

    {# ... #}
    
    <h2>New Task</h2>
    {{ form_start(form) }}
        {# render the task's only field: description #}
        {{ form_row(form.description) }}

        <h3>Tags</h3>
        <ul class="tags">
            {# iterate over each existing tag and render its only field: name #}
            {% for tag in form.tags %}
                <li>{{ form_row(tag.name) }}</li>
            {% endfor %}
        </ul>
    {{ form_end(form) }}

    {# ... #}

When the user submits the form, the submitted data for the ``tags`` field is
used to construct an ``ArrayCollection`` of ``Tag`` objects, which is then set
on the ``tag`` field of the ``Task`` instance.

The ``tags`` collection is accessible naturally via ``$task->getTags()``
and can be persisted to the database or used however you need.

So far, this works great, but this doesn't allow you to dynamically add new
tags or delete existing tags. So, while editing existing tags will work
great, your users can't actually add any new tags yet.

.. caution::

    In this article, you embed only one collection, but you are not limited
    to this. You can also embed nested collections as many levels down as you
    like. But if you use Xdebug in your development setup, you may receive
    a ``Maximum function nesting level of '100' reached, aborting!`` error.
    This is due to the ``xdebug.max_nesting_level`` PHP setting, which defaults
    to ``100``.

    This directive limits recursion to 100 calls which may not be enough for
    rendering the form in the template if you render the whole form at
    once (e.g ``form_widget(form)``). To fix this you can set this directive
    to a higher value (either via a ``php.ini`` file or via :phpfunction:`ini_set`,
    for example in ``app/autoload.php``) or render each form field by hand
    using ``form_row()``.

.. _form-collections-new-prototype:

Allowing "new" Tags with the "Prototype"
----------------------------------------

Allowing the user to dynamically add new tags means that you'll need to
use some JavaScript. Previously you added two tags to your form in the controller.
Now let the users add as many tag forms as they need directly in the browser.

The first thing you need to do is to let the form collection know that it will
receive an unknown number of tags. So far you've added two tags and the form
type expects to receive exactly two. If it gets more, the following error will be
thrown: ``This form should not contain extra fields``. To make the number flexible,
add the ``allow_add`` option to your collection field::

    // src/App/Form/TaskType.php

    // ...
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // ...
        $builder->add('tags', CollectionType::class, array(
            // ...
            'allow_add' => true,
        ));
    }

In addition to telling the field to accept any number of submitted objects,
``allow_add`` also makes a ``prototype`` variable available to you. This
"prototype" is a little "template" that contains all the HTML needed to dynamically
render any new "tag" forms with JavaScript. To render the prototype, make the following
change to the existing ``<ul>`` in your template:

.. code-block:: html+twig

    {# templates/task/new.html.twig #}
    
    {# ... #}
    
    {{ form_start(form) }}
    
        {# ... #}

        <ul class="tags" data-prototype="{{ form_widget(form.tags.vars.prototype)|e('html_attr') }}">
            {# ... #}
        </ul>
    {{ form_end(form) }}

    {# ... #}

On the rendered page, the result will look something like this:

.. code-block:: html

    <ul class="tags" data-prototype="&lt;div&gt;&lt;label class=&quot; required&quot;&gt;__name__&lt;/label&gt;&lt;div id=&quot;task_tags___name__&quot;&gt;&lt;div&gt;&lt;label for=&quot;task_tags___name___name&quot; class=&quot; required&quot;&gt;Name&lt;/label&gt;&lt;input type=&quot;text&quot; id=&quot;task_tags___name___name&quot; name=&quot;task[tags][__name__][name]&quot; required=&quot;required&quot; maxlength=&quot;255&quot; /&gt;&lt;/div&gt;&lt;/div&gt;&lt;/div&gt;">


.. seealso::

    If you want to customize the HTML code in the prototype, see
    :ref:`form-custom-prototype`.

.. tip::

    The ``form.tags.vars.prototype`` is a form element that looks and feels just
    like the individual ``form_widget(tag)`` elements inside your ``for`` loop.
    This means that you can call ``form_widget()``, ``form_row()`` or ``form_label()``
    on it. You could even choose to render only one of its fields (e.g. the
    ``name`` field):

    .. code-block:: html+twig

        {{ form_widget(form.tags.vars.prototype.name)|e }}

.. note::

    You can also render the entire "tags" sub-form at once (e.g. ``form_row(form.tags)``).
    In this case the ``data-prototype`` attribute is automatically added to the containing ``div``,
    and you need to adjust the following JavaScript accordingly.

Now we need some JavaScript to read this attribute and dynamically add new tag forms
when the user clicks an "Add a tag" link. To make things simple, this example uses `jQuery`_.

First, add the link somewhere in your template:

.. code-block:: html+twig

    {# templates/task/new.html.twig #}
    
    {# ... #}
    
    {{ form_start(form) }}
    
        {# ... #}

        <a href="#" class="add_tag_link">Add a tag</a>
        
    {{ form_end(form) }}

    {# ... #}

Now add the required functionality with JavaScript:

.. code-block:: javascript

    // JavaScript

    $(document).ready(function() {
        $('.add_tag_link').click(function(e) {
            e.preventDefault();
            addTagForm();
        });
    });
    
    function addTagForm() {
        var collectionHolder = 'ul.tags';
        // Set up a new `li` with the contents of the prototype:
        var newForm = '<li>' + $(collectionHolder).data('prototype') + '</li>';

        // Get the index of the new `li`:
        var index = $(collectionHolder + ' li').length;

        // Replace `__name__` in the prototype's HTML with the current index:
        newForm = newForm.replace(/__name__/g, index);

        // You need this only if you didn't set 'label' => false in your tags field in TaskType
        // Replace '__name__label__' in the prototype's HTML to
        // instead be a number based on how many items we have
        // newForm = newForm.replace(/__name__label__/g, index);
        
        // Convert the HTML string to a jQuery object. This is needed later, when we add a "delete this tag" link:
        var $newForm = $(newForm);
        $(collectionHolder).append($newForm);        
    }

The ``data-prototype`` HTML contains the tag ``text`` input element with a name of
``task[tags][__name__][name]`` and id of ``task_tags___name___name``. The ``__name__``
is a little "placeholder", which is replaced with a unique, incrementing number
(e.g. ``task[tags][3][name]``).

.. seealso::

    You can find a working example in this `JSFiddle`_.

Now, each time a user clicks the ``Add a tag`` link, a new sub form will
appear on the page. When the form is submitted, any new tag forms will be converted
into new ``Tag`` objects and added to the ``tags`` property of the ``Task`` object.

To make handling these new tags easier, add an "adder" and a "remover" method
for the tags in the ``Task`` class::

    // src/App/Entity/Task.php

    // ...
    class Task
    {
        // ...
        public function addTag(Tag $tag)
        {
            $this->tags->add($tag);
        }

        public function removeTag(Tag $tag)
        {
            // empty for now
        }
    }

Next, add a ``by_reference`` option to the ``tags`` field and set it to ``false``::

    // src/App/Form/TaskType.php

    // ...
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // ...
        $builder->add('tags', CollectionType::class, array(
            // ...
            'by_reference' => false,
        ));
    }

With these two changes, when the form is submitted, each new ``Tag`` object
is added to the ``Task`` class by calling the ``addTag()`` method. Before this
change, they were added internally by the form by calling ``$task->getTags()->add($tag)``.
That was just fine, but forcing the use of the "adder" method makes handling
these new ``Tag`` objects easier (especially if you're using Doctrine, which
you will learn about next!).

.. caution::

    You have to create **both** ``addTag()`` and ``removeTag()`` methods,
    otherwise the form will still use ``setTag()`` even if ``by_reference`` is ``false``.
    You'll learn more about the ``removeTag()`` method later in this article.

.. sidebar:: Doctrine: Cascading Relations and saving the "Inverse" side

    To save the new tags with Doctrine, you need to consider two more things. First,
    unless you iterate over all of the new ``Tag`` objects and call
    ``$entityManager->persist($tag)`` on each, you'll receive this error from Doctrine:

        A new entity was found through the relationship
        ``App\Entity\Task#tags`` that was not configured to
        cascade persist operations for entity...

    To fix this, you may choose to "cascade" the persist operation automatically
    from the ``Task`` object to any related tags. To do this, add the ``cascade``
    option to your mapping:

    .. configuration-block::

        .. code-block:: php-annotations

            // src/App/Entity/Task.php

            // ...

            /**
             * @ORM\ManyToMany(targetEntity="Tag", cascade={"persist"})
             */
            protected $tags;

        .. code-block:: yaml

            # src/App/Resources/config/doctrine/Task.orm.yml
            App\Entity\Task:
                type: entity
                # ...
                oneToMany:
                    tags:
                        targetEntity: Tag
                        cascade:      [persist]

        .. code-block:: xml

            <!-- src/App/Resources/config/doctrine/Task.orm.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                                http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">

                <entity name="App\Entity\Task">
                    <!-- ... -->
                    <one-to-many field="tags" target-entity="Tag">
                        <cascade>
                            <cascade-persist />
                        </cascade>
                    </one-to-many>
                </entity>
            </doctrine-mapping>

    The second potential issue deals with the `Owning Side and Inverse Side`_
    of Doctrine relationships. In the many-to-many association of this example,
    if the "owning" side of the relationship is ``Task``, then persistence will
    work fine as the tags are properly added to the task.
    
    However, if you have a **one-to-many** association, the owning side is always the
    "many" side (``Tag`` in our example). In this case you have to make sure that
    the ``Task`` is set on each ``Tag``. This can be done by adding a single line
    to the "adder" we created above, which is called by the form type since
    ``by_reference`` is set to ``false``::

        // src/App/Entity/Task.php

        public function addTag(Tag $tag)
        {
            // for a many-to-many association:
            $tag->addTask($this);

            // for a many-to-one association:
            $tag->setTask($this);

            // ...
        }

    In case of the many-to-many association, just make sure you have an appropriate
    method inside ``Tag`` that looks something like this::

        // src/App/Entity/Tag.php

        public function addTask(Task $task)
        {
            if (!$this->tasks->contains($task)) {
                $this->tasks->add($task);
            }
        }

.. _form-collections-remove:

Allowing Tags to Be Removed
---------------------------

The next step is to allow the deletion of a particular item in the collection.

Start by adding the ``allow_delete`` option in the form Type::

    // src/App/Form/TaskType.php

    // ...
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // ...
        $builder->add('tags', CollectionType::class, array(
            // ...
            'allow_delete' => true,
        ));
    }

Now, you need to put some code into the ``removeTag()`` method of ``Task``::

    // src/App/Entity/Task.php

    // ...
    class Task
    {
        // ...
        public function removeTag(Tag $tag)
        {
            $this->tags->removeElement($tag);
        }
    }

The ``allow_delete`` option means that if an item of a collection
isn't sent on submission, the related data is removed from the collection
on the server. In order for this to work in an HTML form, you must remove
the DOM element for the collection item to be removed, before submitting
the form.

First, prepare a JavaScript function that adds a "delete this tag" link:

.. code-block:: javascript

    function addDeleteLink($tagFormLi) {
        var $removeFormButton = $('<a href="#">Delete this tag</a>');
        $tagFormLi.append($removeFormButton);

        $removeFormButton.click(function(e) {
            e.preventDefault();
            $tagFormLi.remove();
        });
    }

Now call this function in ``addTagForm()`` function from above:

.. code-block:: javascript

    function addTagForm() {
        // ...
        addDeleteLink($newForm);
    }

This adds the "delete" link to all tag forms that were created by JavaScript
(i.e. after the user clicked the "add new tag" link from above). But what
about any tags that already existed on the server? You could easily add the
"delete" link inside the ``for`` loop of your Twig template, but you'd have
to adjust the JavaScript a little. Or you can just create them with JavaScript
too:

.. code-block:: javascript

    $(document).ready(function() {
        $('ul.tags').find('li').each(function() {
            addDeleteLink($(this));
        });
    });

When a tag form is removed from the DOM on submission, the removed ``Tag`` object
will not be included in the collection passed to ``setTags()``. Depending on
your persistence layer, this may or may not be enough to actually remove
the relationship between the removed ``Tag`` and ``Task`` object.

.. sidebar:: Doctrine: Ensuring the database persistence

    When removing objects in this way, you may need to do a little bit more
    work to ensure that the relationship between the ``Task`` and the removed
    ``Tag`` is properly removed.

    In Doctrine, you have two sides of the relationship: the owning side and the
    inverse side. Normally in this case you'll have a many-to-many relationship
    and the deleted tags will disappear and persist correctly (adding new
    tags also works effortlessly).

    But if you have a one-to-many relationship or a many-to-many relationship with a
    ``mappedBy`` on the Task entity (meaning Task is the "inverse" side),
    you'll need to do more work for the removed tags to persist correctly.

    In this case, you can modify the controller to remove the relationship
    on the removed tag. This assumes that you have some ``editAction()`` which
    is handling the "update" of your Task::

        // src/App/Controller/TaskController.php

        use App\Entity\Task;
        use Doctrine\Common\Collections\ArrayCollection;

        // ...
        public function editAction($id, Request $request)
        {
            $entityManager = $this->getDoctrine()->getManager();
            $task = $entityManager->getRepository(Task::class)->find($id);

            if (!$task) {
                throw $this->createNotFoundException('No task found for id '.$id);
            }

            $originalTags = new ArrayCollection();

            // Create an ArrayCollection of the current Tag objects in the database
            foreach ($task->getTags() as $tag) {
                $originalTags->add($tag);
            }

            $editForm = $this->createForm(TaskType::class, $task);

            $editForm->handleRequest($request);

            if ($editForm->isValid()) {

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
                return $this->redirectToRoute('task_edit', array('id' => $id));
            }

            // render some form template
        }

    As you can see, adding and removing the elements correctly can be tricky.
    Unless you have a many-to-many relationship where Task is the "owning" side,
    you'll need to do extra work to make sure that the relationship is properly
    updated (whether you're adding new tags or removing existing tags) on
    each Tag object itself.

.. sidebar:: Form collection jQuery plugin

    The jQuery plugin  `symfony-collection`_ helps with ``collection`` form elements,
    by providing the JavaScript functionality needed to add, edit and delete
    elements of the collection. More advanced functionality like moving or duplicating
    an element in the collection and customizing the buttons is also possible.

.. _`Owning Side and Inverse Side`: https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/unitofwork-associations.html
.. _`jQuery`: http://jquery.com/
.. _`JSFiddle`: http://jsfiddle.net/847Kf/4/
.. _`symfony-collection`: https://github.com/ninsuo/symfony-collection
