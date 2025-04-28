<?php

namespace Aligent\AsyncEventsBundle\Async\Topic;

use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WebhookEntityDeleteTopic extends WebhookEntityGenericTopic
{
    public const string NAME = 'aligent.webhook.entity.delete';

    public static function getDescription(): string
    {
        return 'Send entity delete events via webhooks';
    }
}
