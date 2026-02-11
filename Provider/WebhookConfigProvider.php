<?php

namespace Aligent\AsyncEventsBundle\Provider;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Psr\Cache\CacheItemPoolInterface;

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
    const CUSTOM = 'custom';

    // Cache Keys
    const CONFIG_CACHE_KEY = 'WebhookConfig';

    protected CacheItemPoolInterface $cache;
    protected ManagerRegistry $registry;

    public function __construct(ManagerRegistry $registry, CacheItemPoolInterface $cache)
    {
        $this->registry = $registry;
        $this->cache = $cache;
    }

    /**
     * Initialize the webhook config cache
     * @return array<string, array<string, array<int>>>
     */
    protected function getWebhookConfig(): array
    {
        $webhookConfigItem = $this->cache->getItem(self::CONFIG_CACHE_KEY);
        $webhookConfig = $webhookConfigItem->get();
        if ($webhookConfig !== null) {
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
        $webhookConfigItem->set($config);
        $this->cache->save($webhookConfigItem);

        return $config;
    }

    /**
     * @param $class
     * @return array<string, array<int>>|null
     */
    public function getEntityConfig($class): ?array
    {
        $config = $this->getWebhookConfig();
        return $config[$class] ?? null;
    }

    /**
     * @param string $class
     * @param string $event
     * @return boolean
     */
    public function isManaged(string $class, string $event): bool
    {
        $entityConfig = $this->getEntityConfig($class);
        if (!$entityConfig) {
            return false;
        }

        return isset($entityConfig[$event]);
    }

    /**
     * Returns a list of channel id's that wish to be notified of this event for this entity
     * @param string $class
     * @param string $event
     * @return int[]
     */
    public function getNotificationChannels(string $class, string $event): array
    {
        $entityConfig = $this->getEntityConfig($class);
        if ($entityConfig == null) {
            return [];
        }
        return $entityConfig[$event];
    }

    /**
     * Convert the webhook config to an easily queried format
     * @param array<int, Transport> $config
     * @return array<string, array<string, array<int>>>
     */
    protected function normalizeConfig(array $config): array
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
