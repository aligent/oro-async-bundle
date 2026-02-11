<?php
/**
 * Base class for the create, delete, and update topics, which all have the same message structure
 *
 * @category  Aligent
 * @package
 * @author    Benno Lang <benno.lang@aligent.com.au>
 * @copyright 2024 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

declare(strict_types=1);

namespace Aligent\AsyncEventsBundle\Async\Topic;

use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class WebhookEntityGenericTopic extends AbstractTopic
{
    public static function getName(): string
    {
        return static::NAME;
    }

    public function configureMessageBody(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['changeSet', 'class', 'id', 'channelId'])
            ->setAllowedTypes('class', 'string')
            ->setAllowedTypes('id', 'array')
            ->setAllowedTypes('channelId', 'int');
    }
}
