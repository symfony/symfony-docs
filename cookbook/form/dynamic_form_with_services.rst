.. index::
   single: Form; Events

How to Dynamically Generate Forms based on user data
====================================================

Sometimes you want a form to be generated dynamically based not only on data
from this form (see :doc:`Dynamic form generation</cookbook/dynamic_form_generation>`)
but also on something else. For example depending on the user currently using
the application. If you have a social website where a user can only message
people who are his friends on the website, then the current user doesn't need to
be included as a field of your form, but a "choice list" of whom to message
should only contain users that are the current user's friends.

Creating the form type
----------------------

Using an event listener, our form could be built like this::

    namespace Acme\WhateverBundle\FormType;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\Form\FormEvents;
    use Symfony\Component\Form\FormEvent;
    use Symfony\Component\Security\Core\SecurityContext;
    use Symfony\Component\OptionsResolver\OptionsResolverInterface;
    use Acme\WhateverBundle\FormSubscriber\UserListener;

    class FriendMessageFormType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('subject', 'text')
                ->add('body', 'textarea')
            ;
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
                // ... add a choice list of friends of the current application user
            });
        }

        public function getName()
        {
            return 'acme_friend_message';
        }

        public function setDefaultOptions(OptionsResolverInterface $resolver)
        {
        }
    }

The problem is now to get the current application user and create a choice field
that would contain only this user's friends.

Luckily it is pretty easy to inject a service inside of the form. This can be
done in the constructor.

.. code-block:: php

    private $securityContext;

    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }

.. note::

    You might wonder, now that we have access to the User (through) the security
    context, why don't we just use that inside of the buildForm function and
    still use a listener?
    This is because doing so in the buildForm method would result in the whole
    form type being modified and not only one form instance.

Customizing the form type
-------------------------

Now that we have all the basics in place, we can put everything in place and add
our listener::

    class FriendMessageFormType extends AbstractType
    {
        private $securityContext;

        public function __construct(SecurityContext $securityContext)
        {
            $this->securityContext = $securityContext;
        }

        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('subject', 'text')
                ->add('body', 'textarea')
            ;
            $user = $this->securityContext->getToken()->getUser();
            $factory = $builder->getFormFactory();

            $builder->addEventListener(
                FormEvents::PRE_SET_DATA,
                function(FormEvent $event) use($user, $factory){
                    $form = $event->getForm();
                    $userId = $user->getId();

                    $form_options = [
                        'class' => 'Acme\WhateverBundle\Document\User',
                        'multiple' => false,
                        'expanded' => false,
                        'property' => 'fullName',
                        'query_builder' => function(DocumentRepository $dr) use ($userId) {
                            return $dr->createQueryBuilder()->field('friends.$id')->equals(new \MongoId($userId));
                        }
                    ];

                    $form->add($factory->createNamed('friend', 'document', null, $form_options));
                }
            );
        }

        public function getName()
        {
            return 'acme_friend_message';
        }

        public function setDefaultOptions(OptionsResolverInterface $resolver)
        {
        }
    }

Using the form
--------------

Our form is now ready to use. We have two possible ways to use it inside of a
controller. Either by creating it everytime and remembering to pass the security
context, or by defining it as a service. This is the option we will show here.

To define your form as a service, you simply add the configuration to your
``config.yml`` file.

.. code-block:: yaml

    acme.form.friend_message:
        class: Acme\WhateverBundle\FormType\FriendMessageType
        arguments: [@security.context]
        tags:
            - { name: form.type, alias: acme_friend_message}

By adding the form as a service, we make sure that this form can now be used
simply from anywhere. If you need to add it to another form, you will just need
to use::

    $builder->add('message', 'acme_friend_message');

If you wish to create it from within a controller or any other service that has
access to the form factory, you then use::

    // src/AcmeDemoBundle/Controller/FriendMessageController.php
    public function friendMessageAction()
    {
        $form = $this->get('form.factory')->create('acme_friend_message');
        $form = $form->createView();

        return compact('form');
    }