<?php

namespace Aligent\AsyncEventsBundle\Async;

use Aligent\AsyncEventsBundle\Exception\RetryableException;
use Aligent\AsyncEventsBundle\Entity\WebhookTransport as WebhookTransportEntity;
use Aligent\AsyncEventsBundle\Integration\WebhookTransport;
use Aligent\AsyncEventsBundle\Provider\WebhookConfigProvider;
use Doctrine\Common\Cache\CacheProvider;
use GuzzleHttp\Exception\GuzzleException;
use Oro\Bundle\ImportExportBundle\Serializer\SerializerInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Component\MessageQueue\Client\Config;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Util\JSON;

/**
 * Class WebhookEntityHandler
 *
 * @category  Aligent
 * @package   Aligent\WebhookBundle\Async
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @link      http://www.aligent.com.au/
 */
class WebhookEntityProcessor extends AbstractRetryableProcessor implements TopicSubscriberInterface
{
    const EVENT_MAP = [
        Topics::WEBHOOK_ENTITY_UPDATE => WebhookConfigProvider::UPDATE,
        Topics::WEBHOOK_ENTITY_DELETE => WebhookConfigProvider::DELETE,
        Topics::WEBHOOK_ENTITY_CREATE => WebhookConfigProvider::CREATE,
        Topics::WEBHOOK_ENTITY_CUSTOM => WebhookConfigProvider::CUSTOM,
    ];

    protected WebhookTransport $transport;
    protected SerializerInterface $serializer;
    protected WebhookConfigProvider $configProvider;
    protected CacheProvider $cache;

    /**
     * @param SerializerInterface $serializer
     * @return WebhookEntityProcessor
     */
    public function setSerializer(SerializerInterface $serializer): WebhookEntityProcessor
    {
        $this->serializer = $serializer;

        return $this;
    }

    /**
     * @param WebhookConfigProvider $configProvider
     * @return WebhookEntityProcessor
     */
    public function setConfigProvider(WebhookConfigProvider $configProvider): WebhookEntityProcessor
    {
        $this->configProvider = $configProvider;

        return $this;
    }

    /**
     * @param WebhookTransport $transport
     * @return WebhookEntityProcessor
     */
    public function setTransport(WebhookTransport $transport): WebhookEntityProcessor
    {
        $this->transport = $transport;

        return $this;
    }

    /**
     * @param CacheProvider $cache
     * @return WebhookEntityProcessor
     */
    public function setCache(CacheProvider $cache): WebhookEntityProcessor
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function execute(MessageInterface $message): string
    {
        $data = JSON::decode($message->getBody());
        $topic = $message->getProperty(Config::PARAMETER_TOPIC_NAME);
        $channelRepo = $this->registry->getRepository(Channel::class);
        $channel = $channelRepo->find($data['channelId']);

        if (!$channel) {
            $this->logger->critical("Channel {$data['channelId']} no longer exists. Skipping webhook event.");
            // remove channel from cache
            $this->cache->deleteAll();
            return self::REJECT;
        }

        try {
            /** @var WebhookTransportEntity $transport */
            $transport = $channel->getTransport();
            $this->transport->init($transport);
            $eventType = self::EVENT_MAP[$topic];

            $response = $this->transport->sendWebhookEvent(
                $transport->getMethod(),
                $this->buildPayload($eventType, $data)
            );
            $this->logger->info(
                'Webhook sent',
                [
                    'eventType' => $eventType,
                    'message' => $data,
                    'response' => $response
                ]
            );
        } catch (\Exception $exception) {
            throw new RetryableException($exception->getMessage(), $exception->getCode(), $exception);
        } catch (GuzzleException $e) {
            $message = "Server responded with non-200 status code";
            $this->logger->error(
                $message,
                [
                    
                    'channelId' => $channel->getId(),
                    'channel' => $channel->getName(),
                    'topic' => $topic,
                    'exception' => $e
                ]
            );
            throw new RetryableException($message, 0, $e);
        }

        return self::ACK;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedTopics(): array
    {
        return [
            Topics::WEBHOOK_ENTITY_CREATE,
            Topics::WEBHOOK_ENTITY_DELETE,
            Topics::WEBHOOK_ENTITY_UPDATE,
            Topics::WEBHOOK_ENTITY_CUSTOM,
        ];
    }

    /**
     * @param string $event
     * @param array $data
     * @return array<string, mixed>
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    protected function buildPayload(string $event, array $data): array
    {
        $entity = $this->registry->getRepository($data['class'])->find($data['id']);

        if (in_array($event, [WebhookConfigProvider::CREATE, WebhookConfigProvider::CUSTOM])) {
            $changeSet = [];
        } else {
            // extract all of the before values from the change set
            $changeSet = [];
            foreach ($data['changeSet'] as $field => $changes) {
                $changeSet[$field] = $changes[0];
            }
        }

        $reflClass = new \ReflectionClass($data['class']);

        return [
            'type' => $reflClass->getShortName(),
            'id' => count($data['id']) > 1 ? $data['id'] : reset($data['id']),
            'operation' => $event,
            'attributes' => $this->serializer->normalize($entity, null, ['webhook']),
            'before' => $changeSet
        ];
    }
}
