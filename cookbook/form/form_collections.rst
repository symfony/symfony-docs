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
    a database connection), it's all pretty much the same.
    
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
    use Symfony\Component\Form\FormBuilder;

    class TagType extends AbstractType
    {
        public function buildForm(FormBuilder $builder, array $options)
        {
            $builder->add('name');
        }

        public function getDefaultOptions(array $options)
        {
            return array(
                'data_class' => 'Acme\TaskBundle\Entity\Tag',
            );
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
    use Symfony\Component\Form\FormBuilder;

    class TaskType extends AbstractType
    {
        public function buildForm(FormBuilder $builder, array $options)
        {
            $builder->add('description');

            $builder->add('tags', 'collection', array('type' => new TagType()));
        }

        public function getDefaultOptions(array $options)
        {
            return array(
                'data_class' => 'Acme\TaskBundle\Entity\Task',
            );
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
    use Acme\TaskBundle\Form\TaskType;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    
    class TaskController extends Controller
    {
        public function newAction(Request $request)
        {
            $task = new Task();
            
            // dummy code - this is here just so that the Task has some tags
            // otherwise, this isn't an interesting example
            $tag1 = new Tag()
            $tag1->name = 'tag1';
            $task->getTags()->add($tag1);
            $tag2 = new Tag()
            $tag2->name = 'tag2';
            $task->getTags()->add($tag2);
            // end dummy code
            
            $form = $this->createForm(new TaskType(), $task);
            
            // maybe do some form process here in a POST request
            
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

    .. code-block:: html+php

        <!-- src/Acme/TaskBundle/Resources/views/Task/new.html.php -->
        <!-- ... -->

        <h3>Tags</h3>
        <ul class="tags">
			<?php foreach($form['tags'] as $tag): ?>
            	<li><?php echo $view['form']->row($tag['name']) ?></li>
			<?php endforeach; ?>
        </ul>

        <?php echo $view['form']->rest($form) ?>
        <!-- ... -->

When the user submits the form, the submitted data for the ``Tags`` fields
are used to construct an ArrayCollection of ``Tag`` objects, which is then
set on the ``tag`` field of the ``Task`` instance.

The ``Tags`` collection is accessible naturally via ``$task->getTags()``
and can be persisted to the database or used however you need.

So far, this works great, but this doesn't allow you to dynamically add new
todos or delete existing todos. So, while editing existing todos will work
great, your user can't actually add any new todos yet.

.. _cookbook-form-collections-new-prototype:

Allowing "new" todos with the "prototype"
-----------------------------------------

This section has not been written yet, but will soon. If you're interested
in writing this entry, see :doc:`/contributing/documentation/overview`.

.. _cookbook-form-collections-remove:

Allowing todos to be removed
----------------------------

This section has not been written yet, but will soon. If you're interested
in writing this entry, see :doc:`/contributing/documentation/overview`.