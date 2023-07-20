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

namespace Aligent\AsyncEventsBundle\Integration;

use Aligent\AsyncEventsBundle\Form\Type\WebhookTransportSettingsType;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Oro\Bundle\SecurityBundle\Encoder\SymmetricCrypterInterface;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface;

class WebhookTransport implements TransportInterface
{
    protected Client $client;
    protected Channel $channel;
    protected LoggerInterface $logger;
    protected SymmetricCrypterInterface $encoder;

    /**
     * AntavoRestTransport constructor.
     * @param SymmetricCrypterInterface $encoder
     * @param LoggerInterface $logger
     */
    public function __construct(SymmetricCrypterInterface $encoder, LoggerInterface $logger)
    {
        $this->encoder = $encoder;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function init(Transport $transportEntity): void
    {
        $settings = $transportEntity->getSettingsBag();
        $this->channel = $transportEntity->getChannel();

        // Decode the Secret key
        $settings->set(
            'password',
            $this->encoder->decryptData($settings->get('password'))
        );

        $headers = array_column($settings->get('headers'), 'value', 'header');

        $this->client = new Client(
            [
                'base_uri' => $settings->get('url'),
                'allow_redirects' => false,
                'auth' => [
                    $settings->get('username'),
                    $settings->get('password')
                ],
                'headers' => $headers
            ]
        );
    }

    /**
     * @param string $method
     * @param array<string, mixed> $payload
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function sendWebhookEvent(string $method = 'POST', array $payload = []): ResponseInterface
    {
        return $this->client->request(
            $method,
            '',
            [
                'json' => $payload
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return 'aligent.async.transport.label';
    }

    /**
     * @inheritDoc
     */
    public function getSettingsFormType(): string
    {
        return WebhookTransportSettingsType::class;
    }

    /**
     * @inheritDoc
     */
    public function getSettingsEntityFQCN(): string
    {
        return \Aligent\AsyncEventsBundle\Entity\WebhookTransport::class;
    }

    /**
     * @return Channel
     */
    public function getChannel(): Channel
    {
        return $this->channel;
    }
}
