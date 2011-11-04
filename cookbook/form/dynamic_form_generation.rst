How to Dynamically Generate Forms Using Form Events
===================================================

Before jumping right into dynamic form generation, let's have a quick review 
of what a form class boiled down to its most bare essentials looks like:

.. code-block:: php

    //src/Acme/DemoBundle/Form/ProductType.php
    namespace Acme\DemoBundle\Form

    use Symfony\Component\Form\AbstractType
    use Symfony\Component\Form\FormBuilder;
    
    class ProductType extends AbstractType
    {
        public function buildForm(FormBuilder $builder, array $options)
        {
            $builder->add('name');
            $builder->add('price');
        }

        public function getName()
        {
            return 'product';
        }
    }

.. note::

    If this particular section of code isn't already familiar to you, you 
    probably need to take a step back and first review the :doc:`Forms chapter </book/forms>` 
    before proceeding.

Let's assume for a moment that this form utilizes an imaginary "Product" entity 
that has only two relevant properties ("name" and "price"). The form generated 
from this class will look the exact same if it is holding a Product object 
that is brand new, and has not had any of its properties set, as when it 
is being used to alter or update an existing record in the database that has 
been fetched with Doctrine.

Suppose now, that you don't want the user to be able to change the `name` value 
once the object has been created. To do this, you can rely on Symfony's :ref:`Event Dispatcher <book-internals-event-dispatcher>` 
system to analyze the data on the object and modify the form based on the 
Product object's data. In this entry, you'll learn how to add this level of 
flexibility to your forms.

.. _`cookbook-forms-event-subscriber`:

Adding An Event Subscriber To A Form Class
------------------------------------------

So, instead of directly adding that "name" widget via our ProductType form 
class, let's delegate the responsibility of creating that particular widget 
to an Event Subscriber

.. code-block:: php

    //src/Acme/DemoBundle/Form/ProductType.php
    namespace Acme\DemoBundle\Form

    use Symfony\Component\Form\AbstractType
    use Symfony\Component\Form\FormBuilder;
    use Acme\DemoBundle\Form\EventListener\MyFormListener;

    class ProductType extends AbstractType
    {
        public function buildForm(FormBuilder $builder, array $options)
        {
            $listener = new MyFormListener($builder->getFormFactory());
            $builder->addEventSubscriber($listener);
            $builder->add('price');
        }

        public function getName()
        {
            return 'product';
        }
    }

The listener is passed the FormFactory object in its constructor so that our 
new listener class is capable of creating the form widget once it is notified 
of the dispatched event during form creation.

.. _`cookbook-forms-listener-class`:

Inside of The Listener Class
----------------------------

Based on our previous scenario where our form creates a widget for the "name" 
property if and only if it is passed an object that has never been persisted 
to the database (and thus has a blank "id" property) our listener might look 
look like the following:

.. code-block:: php

    // src/Acme/DemoBundle/Form/EventListener/MyFormListener.php
    namespace Acme\DemoBundle\Form\EventListener;

    use Symfony\Component\Form\Event\DataEvent;
    use Symfony\Component\Form\FormFactoryInterface;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\Form\FormEvents;

    class MyFormListener implements EventSubscriberInterface
    {
        private $factory;
        
        public function __construct(FormFactoryInterface $factory)
        {
            $this->factory = $factory;
        }
        
        public static function getSubscribedEvents()
        {
            // Tells the dispatcher to pass form.pre_set_data's event object 
            // to our event subscriber via the preSetData() method.
            return array(FormEvents::PRE_SET_DATA => 'preSetData');
        }

        public function preSetData(DataEvent $event)
        {
            $data = $event->getData();
            $form = $event->getForm();
            
            // During form creation setData() is called with null as an argument 
            // by the FormBuilder constructor. We're only concerned with when 
            // setData is called with an actual Entity object in it (whether new,
            // or fetched with Doctrine). This if statement let's us skip right 
            // over the null condition.
            if (null === $data) {
                return;
            }

            if ($data->getId()) {
                $form->add($this->factory->createNamed('text', 'name'));
            }
        }

    }

.. caution::

    It is easy to misunderstand the purpose of the ``if ($data == null)`` segment 
    of this event subscriber. To fully understand its role, you might consider 
    also taking a look at the `Form class`_ and paying special attention to 
    where setData() is called at the end of the constructor, as well as the 
    setData() method itself.

The ``FormEvents::PRE_SET_DATA`` line actually resolves to ``form.pre_set_data``. 
The FormEvents class serves an organizational purpose. It is a centralized 
location in which you can find all of the various form events available.

While this example could have used the ``form.set_data`` or even the ``form.post_set_data`` 
events just as effectively, by using ``form.pre_set_data`` we guarantee that 
the data being retrieved from the ``Event`` object has in no way been modified 
by any other subscribers or listeners. This is because ``form.pre_set_data`` 
passes a `DataEvent`_ object instead of the `FilterDataEvent`_ object passed 
by the ``form.set_data`` event. `DataEvent`_, unlike its child `FilterDataEvent`_, 
lacks a setData() method.

.. note::

    You may view the full list of form events via the `FormEvents class`_, 
    found in the form bundle.

.. _`DataEvent`: https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Form/Event/DataEvent.php
.. _`FormEvents class`: https://github.com/symfony/Form/blob/master/FormEvents.php
.. _`Form class`: https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Form/Form.php
.. _`FilterDataEvent`: https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Form/Event/FilterDataEvent.php
