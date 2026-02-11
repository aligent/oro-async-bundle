<?php

namespace Aligent\AsyncEventsBundle\Async\Topic;

use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WebhookEntityCustomTopic extends WebhookEntityGenericTopic
{
    public const string NAME = 'aligent.webhook.entity.custom';

    public static function getDescription(): string
    {
        return 'Send entity custom events via webhooks';
    }
}
