<?php

namespace Aligent\AsyncEventsBundle\EventListener;

use Aligent\AsyncEventsBundle\Entity\WebhookTransport;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Class WebhookConfigCacheEventListener
 *
 * @category  Aligent
 * @package   Aligent\WebhookBundle\EventListener
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @link      http://www.aligent.com.au/
 */
class WebhookConfigCacheEventListener
{
    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * WebhookConfigCacheEventListener constructor.
     * @param CacheInterface $cache
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param Transport $transport
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(Transport $transport, LifecycleEventArgs $args)
    {
        if ($transport instanceof WebhookTransport) {
            $this->cache->deleteAll();
        }
    }

    /**
     * @param Transport $transport
     * @param LifecycleEventArgs $args
     */
    public function postRemove(Transport $transport, LifecycleEventArgs $args)
    {
        if ($transport instanceof WebhookTransport) {
            $this->cache->deleteAll();
        }
    }
}
