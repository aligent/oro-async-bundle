<?php
/**
 *
 *
 * @category  Aligent
 * @package
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\AsyncEventsBundle\Integration;

use Oro\Bundle\IntegrationBundle\Provider\ChannelInterface;

class WebhookChannel implements ChannelInterface
{
    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        return 'aligent.webhook.channel.label';
    }
}
