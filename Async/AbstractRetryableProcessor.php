<?php
/**
 *
 *
 * @category  Aligent
 * @package
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2019 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\AsyncEventsBundle\Async;

use Aligent\AsyncEventsBundle\Entity\FailedJob;
use Aligent\AsyncEventsBundle\Exception\RetryableException;
use Doctrine\ORM\ORMException;
use Oro\Component\MessageQueue\Client\Config;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;

abstract class AbstractRetryableProcessor implements MessageProcessorInterface, RetryableProcessorInterface
{
    const PROPERTY_REDELIVER_COUNT = 'oro-redeliver-count';
    const MAX_RETRIES = 3;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * AbstractRetryableProcessor constructor.
     * @param LoggerInterface $logger
     * @param ManagerRegistry $registry
     */
    public function __construct(LoggerInterface $logger, ManagerRegistry $registry)
    {
        $this->logger = $logger;
        $this->registry = $registry;
    }

    /**
     * @param MessageInterface $message
     * @param SessionInterface $session
     *
     * @return string
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        try {
            return $this->execute($message);
        } catch (RetryableException $e) {
            $this->logger->error(
                $e->getMessage(),
                [
                    'topic' => $message->getProperty(Config::PARAMETER_TOPIC_NAME),
                    'headers'   => $message->getHeaders(),
                ]
            );

            $retryCount = $message->getProperty(static::PROPERTY_REDELIVER_COUNT) ?: 0;

            // First attempt is 0
            if ($retryCount < static::MAX_RETRIES) {
                return static::REQUEUE;
            }

            $this->handleFailure($message, $e);
        }

        return static::REJECT;
    }

    /**
     * Creates a FailedJob Entity with the contents of the message and the error that ocurred
     * @param MessageInterface $message
     * @param RetryableException $exception
     */
    protected function handleFailure(MessageInterface $message, RetryableException $exception)
    {
        // Fetch the wrapped exception if there is one
        if ($exception->getPrevious()) {
            $exception = $exception->getPrevious();
        }

        $em = $this->registry->getManager();
        $failedJob = new FailedJob(
            $message->getProperty(Config::PARAMETER_TOPIC_NAME),
            JSON::decode($message->getBody()),
            $exception
        );

        try {
            $em->persist($failedJob);
            $em->flush();
        } catch (ORMException $exception) {
            $this->logger->critical('Failed to persist the Failed Job');
        }
    }

    /**
     * @param MessageInterface $message
     * @return string
     * @throws RetryableException
     */
    abstract public function execute(MessageInterface $message): string;
}
