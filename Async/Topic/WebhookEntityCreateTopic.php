<?php

namespace Aligent\AsyncEventsBundle\Async\Topic;

use Symfony\Component\OptionsResolver\OptionsResolver;

class WebhookEntityCreateTopic extends WebhookEntityGenericTopic
{
    public const string NAME = 'aligent.webhook.entity.create';

    public static function getDescription(): string
    {
        return 'Send entity create events via webhooks';
    }
}
