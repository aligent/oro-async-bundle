<?php
/**
 * @category  Aligent
 * @author    Bruno Pasqualini <bruno.pasqualini@aligent.com.au>
 * @copyright 2023 Aligent Consulting.
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\AsyncEventsBundle\Provider;

interface WebhookCustomEventsProviderInterface
{
    /**
     * @return iterable<WebhookCustomEventInterface>
     */
    public function getCustomEvents(): iterable;
}
