<?php

namespace Aligent\AsyncEventsBundle\EventListener;

use Aligent\AsyncEventsBundle\Async\Topic\WebhookEntityCreateTopic;
use Aligent\AsyncEventsBundle\Async\Topic\WebhookEntityDeleteTopic;
use Aligent\AsyncEventsBundle\Async\Topic\WebhookEntityUpdateTopic;
use Aligent\AsyncEventsBundle\Provider\WebhookConfigProvider;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Oro\Bundle\PlatformBundle\EventListener\OptionalListenerInterface;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class EntityEventListener
 *
 * @category  Aligent
 * @package   Aligent\WebhookBundle\EventListener
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @link      http://www.aligent.com.au/
 */
class EntityEventListener implements EventSubscriberInterface, OptionalListenerInterface
{
    const EVENT_MAP = [
        WebhookConfigProvider::UPDATE => WebhookEntityUpdateTopic::NAME,
        WebhookConfigProvider::DELETE => WebhookEntityDeleteTopic::NAME,
        WebhookConfigProvider::CREATE => WebhookEntityCreateTopic::NAME,
    ];

    /**
     * @var WebhookConfigProvider
     */
    protected $webhookConfigProvider;

    /**
     * @var MessageProducerInterface
     */
    protected $producer;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \SplObjectStorage
     */
    protected $insertions;

    /**
     * @var \SplObjectStorage
     */
    protected $deletions;

    /**
     * @var \SplObjectStorage
     */
    protected $updates;

    /**
     * @var bool
     */
    protected $enabled = true;

    /**
     * EntityEventListener constructor.
     * @param WebhookConfigProvider $webhookEntityProvider
     * @param MessageProducerInterface $producer
     * @param LoggerInterface $logger
     */
    public function __construct(
        WebhookConfigProvider $webhookEntityProvider,
        MessageProducerInterface $producer,
        LoggerInterface $logger
    ) {
        $this->webhookConfigProvider = $webhookEntityProvider;
        $this->producer = $producer;
        $this->logger = $logger;

        $this->insertions = new \SplObjectStorage();
        $this->updates = new \SplObjectStorage();
        $this->deletions = new \SplObjectStorage();
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        if (!$this->isEnabled()) {
            return;
        }

        $em = $args->getEntityManager();
        $this->findWebhookManagedInsertions($em);
        $this->findWebhookManagedUpdates($em);
        $this->findWebhookManagedDeletions($em);
    }

    /**
     * @param PostFlushEventArgs $args
     * @throws \Oro\Component\MessageQueue\Transport\Exception\Exception
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if (!$this->isEnabled()) {
            return;
        }

        $em = $args->getEntityManager();
        try {
            $this->queueMessages($this->insertions, $em, WebhookConfigProvider::CREATE);
            $this->queueMessages($this->deletions, $em, WebhookConfigProvider::DELETE);
            $this->queueMessages($this->updates, $em, WebhookConfigProvider::UPDATE);
        } finally {
            $this->insertions->detach($em);
            $this->deletions->detach($em);
            $this->updates->detach($em);
        }
    }

    /**
     * @param EntityManager $em
     */
    public function findWebhookManagedInsertions(EntityManagerInterface $em)
    {
        $uow = $em->getUnitOfWork();
        $insertions = new \SplObjectStorage();
        $scheduledInsertions = $uow->getScheduledEntityInsertions();
        foreach ($scheduledInsertions as $entity) {
            if (!$this->webhookConfigProvider->isManaged(
                ClassUtils::getClass($entity),
                WebhookConfigProvider::CREATE
            )) {
                continue;
            }

            $insertions[$entity] = $uow->getEntityChangeSet($entity);
        }

        $this->stashChanges($this->insertions, $em, $insertions);
    }

    /**
     * @param EntityManager $em
     */
    protected function findWebhookManagedUpdates(EntityManagerInterface $em)
    {
        $uow = $em->getUnitOfWork();
        $updates = new \SplObjectStorage();
        $scheduledUpdates = $uow->getScheduledEntityUpdates();
        foreach ($scheduledUpdates as $entity) {
            if (!$this->webhookConfigProvider->isManaged(
                ClassUtils::getClass($entity),
                WebhookConfigProvider::UPDATE
            )) {
                continue;
            }

            $updates[$entity] = $uow->getEntityChangeSet($entity);
        }

        $this->stashChanges($this->updates, $em, $updates);
    }

    /**
     * @param EntityManager $em
     */
    protected function findWebhookManagedDeletions(EntityManagerInterface $em)
    {
        $uow = $em->getUnitOfWork();
        $deletions = new \SplObjectStorage();
        $scheduledDeletions = $uow->getScheduledEntityDeletions();
        foreach ($scheduledDeletions as $entity) {
            if (!$this->webhookConfigProvider->isManaged(
                ClassUtils::getClass($entity),
                WebhookConfigProvider::DELETE
            )) {
                continue;
            }

            $deletions[$entity] = $uow->getEntityChangeSet($entity);
        }

        $this->stashChanges($this->deletions, $em, $deletions);
    }

    /**
     * @param \SplObjectStorage $storage
     * @param EntityManager $em
     * @param \SplObjectStorage $changes
     */
    protected function stashChanges(\SplObjectStorage $storage, EntityManager $em, \SplObjectStorage $changes)
    {
        if ($changes->count() > 0) {
            if (!$storage->contains($em)) {
                $storage[$em] = $changes;
            } else {
                $previousChangesInCurrentTransaction = $storage[$em];
                $changes->addAll($previousChangesInCurrentTransaction);
                $storage[$em] = $changes;
            }
        }
    }

    /**
     * @param \SplObjectStorage $storage
     * @param EntityManagerInterface $em
     * @param string $event
     * @throws \Oro\Component\MessageQueue\Transport\Exception\Exception
     */
    protected function queueMessages(\SplObjectStorage $storage, EntityManagerInterface $em, string $event)
    {
        if (!$storage->contains($em)) {
            return;
        }

        foreach ($storage[$em] as $entity) {
            $changeSet = $storage[$em][$entity];
            $class = ClassUtils::getClass($entity);
            $metaData = $em->getClassMetadata($class);
            $channelIds = $this->webhookConfigProvider->getNotificationChannels($class, $event);

            // queue a job for every channel so they can be retried individually
            foreach ($channelIds as $channelId) {
                $this->producer->send(
                    self::EVENT_MAP[$event],
                    new Message(
                        [
                            'changeSet' => $changeSet,
                            'class' => $class,
                            'id' => $metaData->getIdentifierValues($entity),
                            'channelId' => $channelId
                        ],
                        MessagePriority::LOW
                    )
                );
            }
        }
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onFlush',
            'postFlush',
        ];
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled; //@Todo: Add check for webhook integrations of outgoing type
    }

    /**
     * @inheritDoc
     */
    public function setEnabled($enabled = true)
    {
        $this->enabled = $enabled;
    }
}
