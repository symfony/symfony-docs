.. index::
   single: Form; Embed collection of forms

How to Embed a Collection of Forms
==================================

In this entry, you'll learn how to create a form that embeds a collection
of many other forms. This could be useful, for example, if you had a ``Task``
class and you wanted to edit/create/remove many ``Tag`` objects related to
that Task, right inside the same form.

.. note::

    In this entry, we'll loosely assume that you're using Doctrine as your
    database store. But if you're not using Doctrine (e.g. Propel or just
    a database connection), it's all very similar. There are only a few parts
    of this tutorial that really care about "persistence".
    
    If you *are* using Doctrine, you'll need to add the Doctrine metadata,
    including the ``ManyToMany`` on the Task's ``tags`` property.

Let's start there: suppose that each ``Task`` belongs to multiple ``Tags``
objects. Start by creating a simple ``Task`` class::

    // src/Acme/TaskBundle/Entity/Task.php
    namespace Acme\TaskBundle\Entity;

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

        public function setTags(ArrayCollection $tags)
        {
            $this->tags = $tags;
        }
    }

.. note::

    The ``ArrayCollection`` is specific to Doctrine and is basically the
    same as using an ``array`` (but it must be an ``ArrayCollection``) if
    you're using Doctrine.

Now, create a ``Tag`` class. As you saw above, a ``Task`` can have many ``Tag``
objects::

    // src/Acme/TaskBundle/Entity/Tag.php
    namespace Acme\TaskBundle\Entity;

    class Tag
    {
        public $name;
    }

.. tip::

    The ``name`` property is public here, but it can just as easily be protected
    or private (but then it would need ``getName`` and ``setName`` methods).

Now let's get to the forms. Create a form class so that a ``Tag`` object
can be modified by the user::

    // src/Acme/TaskBundle/Form/Type/TagType.php
    namespace Acme\TaskBundle\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolverInterface;

    class TagType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder->add('name');
        }

        public function setDefaultOptions(OptionsResolverInterface $resolver)
        {
            $resolver->setDefaults(array(
                'data_class' => 'Acme\TaskBundle\Entity\Tag',
            ));
        }

        public function getName()
        {
            return 'tag';
        }
    }

With this, we have enough to render a tag form by itself. But since the end
goal is to allow the tags of a ``Task`` to be modified right inside the task
form itself, create a form for the ``Task`` class.

Notice that we embed a collection of ``TagType`` forms using the
:doc:`collection</reference/forms/types/collection>` field type::

    // src/Acme/TaskBundle/Form/Type/TaskType.php
    namespace Acme\TaskBundle\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolverInterface;

    class TaskType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder->add('description');

            $builder->add('tags', 'collection', array('type' => new TagType()));
        }

        public function setDefaultOptions(OptionsResolverInterface $resolver)
        {
            $resolver->setDefaults(array(
                'data_class' => 'Acme\TaskBundle\Entity\Task',
            ));
        }

        public function getName()
        {
            return 'task';
        }
    }

In your controller, you'll now initialize a new instance of ``TaskType``::

    // src/Acme/TaskBundle/Controller/TaskController.php
    namespace Acme\TaskBundle\Controller;

    use Acme\TaskBundle\Entity\Task;
    use Acme\TaskBundle\Entity\Tag;
    use Acme\TaskBundle\Form\Type\TaskType;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class TaskController extends Controller
    {
        public function newAction(Request $request)
        {
            $task = new Task();
            
            // dummy code - this is here just so that the Task has some tags
            // otherwise, this isn't an interesting example
            $tag1 = new Tag();
            $tag1->name = 'tag1';
            $task->getTags()->add($tag1);
            $tag2 = new Tag();
            $tag2->name = 'tag2';
            $task->getTags()->add($tag2);
            // end dummy code

            $form = $this->createForm(new TaskType(), $task);

            // process the form on POST
            if ('POST' === $request->getMethod()) {
                $form->bind($request);
                if ($form->isValid()) {
                    // maybe do some form processing, like saving the Task and Tag objects
                }
            }

            return $this->render('AcmeTaskBundle:Task:new.html.twig', array(
                'form' => $form->createView(),
            ));
        }
    }

The corresponding template is now able to render both the ``description``
field for the task form as well as all the ``TagType`` forms for any tags
that are already related to this ``Task``. In the above controller, I added
some dummy code so that you can see this in action (since a ``Task`` has
zero tags when first created).

.. configuration-block::

    .. code-block:: html+jinja

        {# src/Acme/TaskBundle/Resources/views/Task/new.html.twig #}

        {# ... #}

        <form action="..." method="POST" {{ form_enctype(form) }}>
            {# render the task's only field: description #}
            {{ form_row(form.description) }}

            <h3>Tags</h3>
            <ul class="tags">
                {# iterate over each existing tag and render its only field: name #}
                {% for tag in form.tags %}
                    <li>{{ form_row(tag.name) }}</li>
                {% endfor %}
            </ul>

            {{ form_rest(form) }}
            {# ... #}
        </form>

    .. code-block:: html+php

        <!-- src/Acme/TaskBundle/Resources/views/Task/new.html.php -->

        <!-- ... -->

        <form action="..." method="POST" ...>
            <h3>Tags</h3>
            <ul class="tags">
                <?php foreach($form['tags'] as $tag): ?>
                    <li><?php echo $view['form']->row($tag['name']) ?></li>
                <?php endforeach; ?>
            </ul>

            <?php echo $view['form']->rest($form) ?>
        </form>
        
        <!-- ... -->

When the user submits the form, the submitted data for the ``Tags`` fields
are used to construct an ArrayCollection of ``Tag`` objects, which is then
set on the ``tag`` field of the ``Task`` instance.

The ``Tags`` collection is accessible naturally via ``$task->getTags()``
and can be persisted to the database or used however you need.

So far, this works great, but this doesn't allow you to dynamically add new
tags or delete existing tags. So, while editing existing tags will work
great, your user can't actually add any new tags yet.

.. caution::

    In this entry, we embed only one collection, but you are not limited
    to this. You can also embed nested collection as many level down as you
    like. But if you use Xdebug in your development setup, you may receive
    a ``Maximum function nesting level of '100' reached, aborting!`` error.
    This is due to the ``xdebug.max_nesting_level`` PHP setting, which defaults
    to ``100``.

    This directive limits recursion to 100 calls which may not be enough for
    rendering the form in the template if you render the whole form at
    once (e.g ``form_widget(form)``). To fix this you can set this directive
    to a higher value (either via a PHP ini file or via :phpfunction:`ini_set`,
    for example in ``app/autoload.php``) or render each form field by hand
    using ``form_row``.

.. _cookbook-form-collections-new-prototype:

Allowing "new" tags with the "prototype"
-----------------------------------------

Allowing the user to dynamically add new tags means that we'll need to
use some JavaScript. Previously we added two tags to our form in the controller.
Now we need to let the user add as many tag forms as he needs directly in the browser.
This will be done through a bit of JavaScript.

The first thing we need to do is to let the form collection know that it will
receive an unknown number of tags. So far we've added two tags and the form
type expects to receive exactly two, otherwise an error will be thrown:
``This form should not contain extra fields``. To make this flexible, we
add the ``allow_add`` option to our collection field::

    // src/Acme/TaskBundle/Form/Type/TaskType.php

    // ...
    
    use Symfony\Component\Form\FormBuilderInterface;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('description');

        $builder->add('tags', 'collection', array(
            'type' => new TagType(),
            'allow_add' => true,
            'by_reference' => false,
        ));
    }

Note that we also added ``'by_reference' => false``. Normally, the form
framework would modify the tags on a `Task` object *without* actually
ever calling `setTags`. By setting :ref:`by_reference<reference-form-types-by-reference>`
to `false`, `setTags` will be called. This will be important later as you'll
see.

In addition to telling the field to accept any number of submitted objects, the
``allow_add`` also makes a "prototype" variable available to you. This "prototype"
is a little "template" that contains all the HTML to be able to render any
new "tag" forms. To render it, make the following change to your template:

.. configuration-block::

    .. code-block:: html+jinja
    
        <ul class="tags" data-prototype="{{ form_widget(form.tags.vars.prototype)|e }}">
            ...
        </ul>
    
    .. code-block:: html+php
    
        <ul class="tags" data-prototype="<?php echo $view->escape($view['form']->row($form['tags']->getVar('prototype'))) ?>">
            ...
        </ul>

.. note::

    If you render your whole "tags" sub-form at once (e.g. ``form_row(form.tags)``),
    then the prototype is automatically available on the outer ``div`` as
    the ``data-prototype`` attribute, similar to what you see above.

.. tip::

    The ``form.tags.vars.prototype`` is form element that looks and feels just
    like the individual ``form_widget(tag)`` elements inside our ``for`` loop.
    This means that you can call ``form_widget``, ``form_row``, or ``form_label``
    on it. You could even choose to render only one of its fields (e.g. the
    ``name`` field):
    
    .. code-block:: html+jinja
    
        {{ form_widget(form.tags.vars.prototype.name)|e }}

On the rendered page, the result will look something like this:

.. code-block:: html

    <ul class="tags" data-prototype="&lt;div&gt;&lt;label class=&quot; required&quot;&gt;__name__&lt;/label&gt;&lt;div id=&quot;task_tags___name__&quot;&gt;&lt;div&gt;&lt;label for=&quot;task_tags___name___name&quot; class=&quot; required&quot;&gt;Name&lt;/label&gt;&lt;input type=&quot;text&quot; id=&quot;task_tags___name___name&quot; name=&quot;task[tags][__name__][name]&quot; required=&quot;required&quot; maxlength=&quot;255&quot; /&gt;&lt;/div&gt;&lt;/div&gt;&lt;/div&gt;">

The goal of this section will be to use JavaScript to read this attribute
and dynamically add new tag forms when the user clicks a "Add a tag" link.
To make things simple, we'll use jQuery and assume you have it included somewhere
on your page.

Add a ``script`` tag somewhere on your page so we can start writing some JavaScript.

First, add a link to the bottom of the "tags" list via JavaScript. Second,
bind to the "click" event of that link so we can add a new tag form (``addTagForm``
will be show next):

.. code-block:: javascript

    // Get the div that holds the collection of tags
    var collectionHolder = $('ul.tags');

    // setup an "add a tag" link
    var $addTagLink = $('<a href="#" class="add_tag_link">Add a tag</a>');
    var $newLinkLi = $('<li></li>').append($addTagLink);

    jQuery(document).ready(function() {
        // add the "add a tag" anchor and li to the tags ul
        collectionHolder.append($newLinkLi);

        $addTagLink.on('click', function(e) {
            // prevent the link from creating a "#" on the URL
            e.preventDefault();

            // add a new tag form (see next code block)
            addTagForm(collectionHolder, $newLinkLi);
        });
    });

The ``addTagForm`` function's job will be to use the ``data-prototype`` attribute
to dynamically add a new form when this link is clicked. The ``data-prototype``
HTML contains the tag ``text`` input element with a name of ``task[tags][__name__][name]``
and id of ``task_tags___name___name``. The ``__name__`` is a little "placeholder",
which we'll replace with a unique, incrementing number (e.g. ``task[tags][3][name]``).

.. versionadded:: 2.1
    The placeholder was changed from ``$$name$$`` to ``__name__`` in Symfony 2.1

The actual code needed to make this all work can vary quite a bit, but here's
one example:

.. code-block:: javascript

    function addTagForm(collectionHolder, $newLinkLi) {
        // Get the data-prototype we explained earlier
        var prototype = collectionHolder.attr('data-prototype');

        // Replace '__name__' in the prototype's HTML to
        // instead be a number based on the current collection's length.
        var newForm = prototype.replace(/__name__/g, collectionHolder.children().length);

        // Display the form in the page in an li, before the "Add a tag" link li
        var $newFormLi = $('<li></li>').append(newForm);
        $newLinkLi.before($newFormLi);
    }

.. note:

    It is better to separate your javascript in real JavaScript files than
    to write it inside the HTML as we are doing here.

Now, each time a user clicks the ``Add a tag`` link, a new sub form will
appear on the page. When we submit, any new tag forms will be converted into
new ``Tag`` objects and added to the ``tags`` property of the ``Task`` object.

.. sidebar:: Doctrine: Cascading Relations and saving the "Inverse" side

    To get the new tags to save in Doctrine, you need to consider a couple
    more things. First, unless you iterate over all of the new ``Tag`` objects
    and call ``$em->persist($tag)`` on each, you'll receive an error from
    Doctrine:
    
        A new entity was found through the relationship 'Acme\TaskBundle\Entity\Task#tags' that was not configured to cascade persist operations for entity...
    
    To fix this, you may choose to "cascade" the persist operation automatically
    from the ``Task`` object to any related tags. To do this, add the ``cascade``
    option to your ``ManyToMany`` metadata:
    
    .. configuration-block::
    
        .. code-block:: php-annotations

            // src/Acme/TaskBundle/Entity/Task.php

            // ...

            /**
             * @ORM\ManyToMany(targetEntity="Tag", cascade={"persist"})
             */
            protected $tags;

        .. code-block:: yaml

            # src/Acme/TaskBundle/Resources/config/doctrine/Task.orm.yml
            Acme\TaskBundle\Entity\Task:
                type: entity
                # ...
                oneToMany:
                    tags:
                        targetEntity: Tag
                        cascade:      [persist]
    
    A second potential issue deals with the `Owning Side and Inverse Side`_
    of Doctrine relationships. In this example, if the "owning" side of the
    relationship is "Task", then persistence will work fine as the tags are
    properly added to the Task. However, if the owning side is on "Tag", then
    you'll need to do a little bit more work to ensure that the correct side
    of the relationship is modified.

    The trick is to make sure that the single "Task" is set on each "Tag".
    One easy way to do this is to add some extra logic to ``setTags()``,
    which is called by the form framework since :ref:`by_reference<reference-form-types-by-reference>`
    is set to ``false``::
    
        // src/Acme/TaskBundle/Entity/Task.php

        // ...

        public function setTags(ArrayCollection $tags)
        {
            foreach ($tags as $tag) {
                $tag->addTask($this);
            }

            $this->tags = $tags;
        }

    Inside ``Tag``, just make sure you have an ``addTask`` method::

        // src/Acme/TaskBundle/Entity/Tag.php

        // ...

        public function addTask(Task $task)
        {
            if (!$this->tasks->contains($task)) {
                $this->tasks->add($task);
            }
        }
    
    If you have a ``OneToMany`` relationship, then the workaround is similar,
    except that you can simply call ``setTask`` from inside ``setTags``.

.. _cookbook-form-collections-remove:

Allowing tags to be removed
----------------------------

The next step is to allow the deletion of a particular item in the collection.
The solution is similar to allowing tags to be added.

Start by adding the ``allow_delete`` option in the form Type::
    
    // src/Acme/TaskBundle/Form/Type/TaskType.php

    // ...
    use Symfony\Component\Form\FormBuilderInterface;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('description');

        $builder->add('tags', 'collection', array(
            'type' => new TagType(),
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
        ));
    }

Templates Modifications
~~~~~~~~~~~~~~~~~~~~~~~
    
The ``allow_delete`` option has one consequence: if an item of a collection 
isn't sent on submission, the related data is removed from the collection
on the server. The solution is thus to remove the form element from the DOM.

First, add a "delete this tag" link to each tag form:

.. code-block:: javascript

    jQuery(document).ready(function() {
        // add a delete link to all of the existing tag form li elements
        collectionHolder.find('li').each(function() {
            addTagFormDeleteLink($(this));
        });
    
        // ... the rest of the block from above
    });
    
    function addTagForm() {
        // ...
        
        // add a delete link to the new form
        addTagFormDeleteLink($newFormLi);
    }

The ``addTagFormDeleteLink`` function will look something like this:

.. code-block:: javascript

    function addTagFormDeleteLink($tagFormLi) {
        var $removeFormA = $('<a href="#">delete this tag</a>');
        $tagFormLi.append($removeFormA);

        $removeFormA.on('click', function(e) {
            // prevent the link from creating a "#" on the URL
            e.preventDefault();

            // remove the li for the tag form
            $tagFormLi.remove();
        });
    }

When a tag form is removed from the DOM and submitted, the removed ``Tag`` object
will not be included in the collection passed to ``setTags``. Depending on
your persistence layer, this may or may not be enough to actually remove
the relationship between the removed ``Tag`` and ``Task`` object.

.. sidebar:: Doctrine: Ensuring the database persistence

    When removing objects in this way, you may need to do a little bit more
    work to ensure that the relationship between the Task and the removed Tag
    is properly removed.

    In Doctrine, you have two side of the relationship: the owning side and the
    inverse side. Normally in this case you'll have a ManyToMany relation
    and the deleted tags will disappear and persist correctly (adding new
    tags also works effortlessly).

    But if you have an ``OneToMany`` relation or a ``ManyToMany`` with a
    ``mappedBy`` on the Task entity (meaning Task is the "inverse" side),
    you'll need to do more work for the removed tags to persist correctly.

    In this case, you can modify the controller to remove the relationship
    on the removed tag. This assumes that you have some ``editAction`` which
    is handling the "update" of your Task::

        // src/Acme/TaskBundle/Controller/TaskController.php

        // ...

        public function editAction($id, Request $request)
        {
            $em = $this->getDoctrine()->getManager();
            $task = $em->getRepository('AcmeTaskBundle:Task')->find($id);
    
            if (!$task) {
                throw $this->createNotFoundException('No task found for is '.$id);
            }

            $originalTags = array();

            // Create an array of the current Tag objects in the database
            foreach ($task->getTags() as $tag) $originalTags[] = $tag;
          
            $editForm = $this->createForm(new TaskType(), $task);

            if ('POST' === $request->getMethod()) {
                $editForm->bind($this->getRequest());

                if ($editForm->isValid()) {
        
                    // filter $originalTags to contain tags no longer present
                    foreach ($task->getTags() as $tag) {
                        foreach ($originalTags as $key => $toDel) {
                            if ($toDel->getId() === $tag->getId()) {
                                unset($originalTags[$key]);
                            }
                        }
                    }

                    // remove the relationship between the tag and the Task
                    foreach ($originalTags as $tag) {
                        // remove the Task from the Tag
                        $tag->getTasks()->removeElement($task);
    
                        // if it were a ManyToOne relationship, remove the relationship like this
                        // $tag->setTask(null);
                        
                        $em->persist($tag);

                        // if you wanted to delete the Tag entirely, you can also do that
                        // $em->remove($tag);
                    }

                    $em->persist($task);
                    $em->flush();

                    // redirect back to some edit page
                    return $this->redirect($this->generateUrl('task_edit', array('id' => $id)));
                }
            }
            
            // render some form template
        }

    As you can see, adding and removing the elements correctly can be tricky.
    Unless you have a ManyToMany relationship where Task is the "owning" side,
    you'll need to do extra work to make sure that the relationship is properly
    updated (whether you're adding new tags or removing existing tags) on
    each Tag object itself.


.. _`Owning Side and Inverse Side`: http://docs.doctrine-project.org/en/latest/reference/unitofwork-associations.html
