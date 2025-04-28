<?php

namespace Aligent\AsyncEventsBundle\Async;

use Aligent\AsyncEventsBundle\Async\Topic\WebhookEntityCreateTopic;
use Aligent\AsyncEventsBundle\Async\Topic\WebhookEntityDeleteTopic;
use Aligent\AsyncEventsBundle\Async\Topic\WebhookEntityUpdateTopic;
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
    const EVENT_MAP = [
        WebhookConfigProvider::UPDATE => WebhookEntityUpdateTopic::NAME,
        WebhookConfigProvider::DELETE => WebhookEntityDeleteTopic::NAME,
        WebhookConfigProvider::CREATE => WebhookEntityCreateTopic::NAME,
    ];
}
