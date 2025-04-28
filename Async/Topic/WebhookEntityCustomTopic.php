<?php

namespace Aligent\AsyncEventsBundle\Async\Topic;

use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WebhookEntityCustomTopic extends AbstractTopic
{
    public const string NAME = 'aligent.webhook.entity.custom';

    public static function getName(): string
    {
        return self::NAME;
    }

    public static function getDescription(): string
    {
        return 'Send entity custom events via webhooks';
    }

    public function configureMessageBody(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['class', 'id', 'channelId'])
            ->setAllowedTypes('class', 'string')
            ->setAllowedTypes('id', 'array')
            ->setAllowedTypes('channelId', 'int[]');
    }
}
