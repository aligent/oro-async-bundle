<?php

namespace Aligent\AsyncEventsBundle\Async\Topic;

use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WebhookEntityUpdateTopic extends WebhookEntityGenericTopic
{
    public const string NAME = 'aligent.webhook.entity.update';

    public static function getDescription(): string
    {
        return 'Send entity update events via webhooks';
    }
}
