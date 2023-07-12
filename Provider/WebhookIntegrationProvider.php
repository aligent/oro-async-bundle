<?php
/**
 *
 *
 * @category  Aligent
 * @package
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\AsyncEventsBundle\Provider;

use Aligent\AsyncEventsBundle\Entity\WebhookTransport as WebhookTransportSettings;
use Aligent\AsyncEventsBundle\Integration\WebhookTransport;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class WebhookIntegrationProvider
{
    protected ManagerRegistry $registry;
    protected WebhookTransport $transport;

    /**
     * WebhookIntegrationProvider constructor.
     * @param ManagerRegistry $registry
     * @param WebhookTransport $transport
     */
    public function __construct(
        ManagerRegistry $registry,
        WebhookTransport $transport
    ) {
        $this->registry = $registry;
        $this->transport = $transport;
    }

    /**
     * @param $username
     * @return object|null
     */
    public function getTransportByUsername($username): ?object
    {
        $repo = $this->getTransportRepo();

        return $repo->findOneBy(
            [
                'username' => $username
            ]
        );
    }

    /**
     * @return ObjectRepository
     */
    protected function getTransportRepo(): ObjectRepository
    {
        return $this->registry->getRepository(WebhookTransport::class);
    }

    /**
     * @param WebhookTransportSettings $transportEntity
     * @return WebhookTransport
     */
    public function initializeTransport(WebhookTransportSettings $transportEntity): WebhookTransport
    {
        $this->transport->init($transportEntity);
        return $this->transport;
    }

    /**
     * @return WebhookTransport
     */
    public function getTransport(): WebhookTransport
    {
        return $this->transport;
    }
}
