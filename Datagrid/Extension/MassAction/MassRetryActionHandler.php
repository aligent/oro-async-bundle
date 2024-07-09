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

namespace Aligent\AsyncEventsBundle\Datagrid\Extension\MassAction;

use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerArgs;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponse;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponseInterface;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;

class MassRetryActionHandler implements MassActionHandlerInterface
{
    /**
     * @var MessageProducerInterface
     */
    protected $messageProducer;

    /**
     * MassRetryActionHandler constructor.
     * @param MessageProducerInterface $messageProducer
     */
    public function __construct(MessageProducerInterface $messageProducer)
    {
        $this->messageProducer = $messageProducer;
    }

    /**
     * Handle mass action
     *
     * @param MassActionHandlerArgs $args
     *
     * @return MassActionResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function handle(MassActionHandlerArgs $args): MassActionResponseInterface
    {
        $results = $args->getResults();
        $em = $results->getSource()->getEntityManager();
        $count = 0;
        $failed = 0;

        foreach ($results as $result) {
            try {
                $job = $result->getRootEntity();
                $this->messageProducer->send(
                    $job->getTopic(),
                    $job->getBody()
                );

                $em->remove($job);
                $count++;
            } catch (\Exception $e) {
                $failed++;
            }
        }

        $em->flush();

        return $count > 0 && $failed == 0
            ? new MassActionResponse(true, "$count Jobs resent")
            : new MassActionResponse(false, "$count Jobs resent, $failed jobs failed to be resent");
    }
}
