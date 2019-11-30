.. index::
   single: Pre render events

Recommendations:
================
This feature is help full for partials. This way you do not have to load the content for the partial in your controller.
Simple listen for when it is ready to render, and add the content then.

How to Use the event dispatcher for Twig
========================================

.. configuration-block::

    .. code-block:: yaml

        # config/packages/twig.yaml
        twig:
            # ...
            event_dispatcher: 'event_dispatcher'

    .. code-block:: php

        // config/packages/twig.php
        $container->loadFromExtension('twig', [
            // ...
            'event_dispatcher' => new \Symfony\Component\EventDispatcher\EventDispatcher()
        ]);

Once the dispatcher is set it will fire an event just before rendering the template in the wrapper.
This will be done for every template.

The event which is dispatched will be: twig.pre_render:{templateName}
The template name will be the full name from the template.

For listing make a class twig events in the Event folder from your application:

.. code-block:: php

    namespace App\Event;

    use Twig\Event\TwigEvents as BaseTwigEvents;

    class TwigEvents extends BaseTwigEvents
    {

        /**
         * @Event("Twig\Event\PreRenderEvent")
         */
        const PRE_RENDER_FOO_BAR = self::PRE_RENDER . 'foo/bar.html.twig';
    }


Next step is make a subscriber:

.. code-block:: php

    namespace App\EventSubscriber;

    use App\Event\TwigEvents;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Twig\Event\PreRenderEvent;

    class RowComposerSubscriber implements EventSubscriberInterface
    {
        /**
         * @return array
         */
        public static function getSubscribedEvents(): array
        {
            return [
                TwigEvents::PRE_RENDER_FOO_BAR => 'compose'
            ];
        }

        /**
         * @param PreRenderEvent $event
         */
        public function compose(PreRenderEvent $event): void
        {
            $event->addContext('foo', 'bar');
        }
    }

This subscriber listen for the moment the template is ready for rendering.
And will add additional context through the event on the template.
It is also possible to overrule all content by using `setContent`.

Now the event and subscribers are ready, we can no render a new template, In your controller use:
`$this->render('foo/bar.html.twig')`

Make the `foo/bar.html.twig`:

`Showing the {{ foo }} loaded through the subscriber.`

Loading the page now, will result in:

`Showing the bar loaded through the subscriber.`