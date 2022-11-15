<?php

namespace Aligent\AsyncEventsBundle\Async;

use Aligent\AsyncEventsBundle\Provider\WebhookConfigProvider;

/**
 * Class Topics
 *
 * @category  Aligent
 * @package   Aligent\WebhookBundle\Async
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @link      http://www.aligent.com.au/
 */
class Topics
{
    const WEBHOOK_ENTITY_CREATE = 'aligent.webhook.entity.create';
    const WEBHOOK_ENTITY_UPDATE = 'aligent.webhook.entity.update';
    const WEBHOOK_ENTITY_DELETE = 'aligent.webhook.entity.delete';

    const EVENT_MAP = [
        WebhookConfigProvider::UPDATE => self::WEBHOOK_ENTITY_UPDATE,
        WebhookConfigProvider::DELETE => self::WEBHOOK_ENTITY_DELETE,
        WebhookConfigProvider::CREATE => self::WEBHOOK_ENTITY_CREATE,
    ];
}
