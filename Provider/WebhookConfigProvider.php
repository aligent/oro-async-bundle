<?php

namespace Aligent\AsyncEventsBundle\Provider;

use Doctrine\Common\Cache\CacheProvider;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Symfony\Bridge\Doctrine\ManagerRegistry;

/**
 * Class WebhookConfigProvider
 *
 * @category  Aligent
 * @package   Aligent\WebhookBundle\Provider
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @link      http://www.aligent.com.au/
 */
class WebhookConfigProvider
{
    const CREATE = 'create';
    const UPDATE = 'update';
    const DELETE = 'delete';

    // Cache Keys
    const CONFIG_CACHE_KEY = 'WebhookConfig';

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var CacheProvider
     */
    protected $cache;

    /**
     * WebhookEntityProvider constructor.
     * @param ManagerRegistry $registry
     * @param CacheProvider $cache
     */
    public function __construct(ManagerRegistry $registry, CacheProvider $cache)
    {
        $this->registry = $registry;
        $this->cache = $cache;
    }

    /**
     * Initialize the webhook config cache
     */
    protected function getWebhookConfig()
    {
        if ($webhookConfig = $this->cache->fetch(self::CONFIG_CACHE_KEY)) {
            return $webhookConfig;
        }

        // Get All enabled webhook integrations
        $repo = $this->registry->getRepository(Channel::class);
        $channels = $repo->findBy(
            [
                'enabled' => true,
                'type'    => 'webhook',
            ]
        );

        $config = [];
        foreach ($channels as $channel) {
            $config[$channel->getId()] = $channel->getTransport();
        }

        $config = $this->normalizeConfig($config);
        $this->cache->save(static::CONFIG_CACHE_KEY, $config);
        return $config;
    }

    /**
     * @param $class
     * @return array|null
     */
    public function getEntityConfig($class)
    {
        $config = $this->getWebhookConfig();
        return $config[$class] ?? null;
    }

    /**
     * @param $class
     * @param $event
     * @return boolean
     */
    public function isManaged($class, $event)
    {
        $entityConfig = $this->getEntityConfig($class);
        if (!$entityConfig) {
            return false;
        }

        return isset($entityConfig[$event]);
    }

    /**
     * Returns a list of channel id's that wish to be notified of this event for this entity
     * @param $class
     * @param $event
     * @return int[]
     */
    public function getNotificationChannels($class, $event)
    {
        $entityConfig = $this->getEntityConfig($class);
        return $entityConfig[$event];
    }

    /**
     * Convert the webhook config to an easily queried format
     * @param array $config
     * @return array
     */
    protected function normalizeConfig(array $config)
    {
        $normalizedConfig = [];
        foreach ($config as $id => $webhookTransport) {
            $class = $webhookTransport->getEntity();
            $normalizedConfig[$class][$webhookTransport->getEvent()] = [$id];
            $normalizedConfig[$class]['channels'][] = $id;
        }

        return $normalizedConfig;
    }
}
