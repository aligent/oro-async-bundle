<?php
/**
 * @category  Aligent
 * @author    Bruno Pasqualini <bruno.pasqualini@aligent.com.au>
 * @copyright 2023 Aligent Consulting.
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\AsyncEventsBundle\Provider;

use Symfony\Contracts\Translation\TranslatorInterface;

class WebhookCustomEventsProvider implements WebhookCustomEventsProviderInterface
{
    /**
     * @var iterable<WebhookCustomEventsProviderInterface>
     */
    protected iterable $customEvents;

    /**
     * @param WebhookCustomEventsProviderInterface[]|iterable $customEvents
     */
    public function __construct(iterable $customEvents)
    {
        $this->customEvents = $customEvents;
    }

    /**
     * @inheritDoc
     */
    public function getCustomEvents(): iterable
    {
        return $this->customEvents;
    }
}
