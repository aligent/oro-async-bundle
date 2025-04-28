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

namespace Aligent\AsyncEventsBundle\Controller;

use Aligent\AsyncEventsBundle\Entity\FailedJob;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Transport\Exception\Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class FailedJobController extends AbstractController
{

    protected MessageProducerInterface $messageProducer;
    protected ManagerRegistry $doctrine;

    /**
     * @param MessageProducerInterface $messageProducer
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(MessageProducerInterface $messageProducer, ManagerRegistry $managerRegistry)
    {
        $this->messageProducer = $messageProducer;
        $this->doctrine = $managerRegistry;
    }

    /**
     * @Route(name="aligent_failed_jobs_index")
     */
    #[Acl(id: 'failed_jobs', type: 'action', label: 'RetryableJobs')]
    #[Template]
    public function indexAction()
    {
        return [];
    }

    /**
     * View Failed Job
     *
     * @Route("/view/{id}", name="aligent_failed_jobs_view")
     * @param FailedJob $job
     * @return array
     */
    #[AclAncestor('failed_jobs')]
    #[Template]
    public function viewAction(FailedJob $job)
    {
        return  [
            'entity' => $job
        ];
    }

    /**
     * Delete Failed Job
     *
     * @Route("/remove/{id}", name="aligent_failed_jobs_delete")
     * @param FailedJob $job
     * @return JsonResponse
     */
    #[AclAncestor('failed_jobs')]
    public function deleteAction(FailedJob $job)
    {
        try {
            $em = $this->doctrine->getManager();
            $em->remove($job);
            $em->flush();
        } catch (Exception $exception) {
            return new JsonResponse(['successful' => false]);
        }

        return new JsonResponse(['successful' => true]);
    }

    /**
     * Retry Failed jobs
     *
     * @Route("/retry/{id}", name="aligent_failed_jobs_retry")
     * @param FailedJob $job
     * @return JsonResponse
     */
    #[AclAncestor('failed_jobs')]
    public function retryAction(FailedJob $job)
    {
        try {
            $this->messageProducer->send(
                $job->getTopic(),
                $job->getBody()
            );

            $em = $this->doctrine->getManager();
            $em->remove($job);
            $em->flush();
        } catch (Exception $exception) {
            return new JsonResponse(['successful' => false]);
        }

        return new JsonResponse(['successful' => true]);
    }
}
