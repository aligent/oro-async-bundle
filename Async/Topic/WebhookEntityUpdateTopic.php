<?php

namespace Aligent\AsyncEventsBundle\Async\Topic;

class WebhookEntityUpdateTopic extends \Oro\Component\MessageQueue\Topic\AbstractTopic
{
    public static function getName(): string
    {
        return 'aligent.webhook.entity.update';
    }
    public static function getDescription(): string
    {
        // TODO: Implement getDescription() method.
        return '';
    }
    public function configureMessageBody(\Symfony\Component\OptionsResolver\OptionsResolver $resolver): void
    {
        // TODO: Implement configureMessageBody() method.
    }
}
