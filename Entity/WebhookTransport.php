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

namespace Aligent\AsyncEventsBundle\Entity;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Symfony\Component\HttpFoundation\ParameterBag;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class WebhookTransport
 *
 * @ORM\Entity
 * @Config()
 */
class WebhookTransport extends Transport
{
    /**
     * @var string
     * @ORM\Column(type="string", name="wh_api_user", nullable=true)
     */
    protected $username;

    /**
     * @var string
     * @ORM\Column(type="string", name="wh_api_key", nullable=true)
     */
    protected $password;

    /**
     * @var string
     * @ORM\Column(type="string", name="wh_api_url", nullable=true)
     */
    protected $url;

    /**
     * @var string
     * @ORM\Column(type="string", name="wh_entity_class", nullable=true)
     */
    protected $entity;

    /**
     * @var string
     * @ORM\Column(type="string", name="wh_event", nullable=true)
     */
    protected $event;

    /**
     * @var string
     * @ORM\Column(type="string", name="wh_method", nullable=true)
     */
    protected $method;

    /**
     * @var array
     * @ORM\Column(type="json", name="wh_headers", nullable=true)
     */
    protected $headers = [];

    /**
     * @return string
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return WebhookTransport
     */
    public function setUsername(string $username): WebhookTransport
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return WebhookTransport
     */
    public function setPassword(string $password): WebhookTransport
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return WebhookTransport
     */
    public function setUrl(string $url): WebhookTransport
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getEntity(): ?string
    {
        return $this->entity;
    }

    /**
     * @param string $entity
     * @return WebhookTransport
     */
    public function setEntity(string $entity): WebhookTransport
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return string
     */
    public function getEvent(): ?string
    {
        return $this->event;
    }

    /**
     * @param string $event
     * @return WebhookTransport
     */
    public function setEvent(string $event): WebhookTransport
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return WebhookTransport
     */
    public function setMethod(string $method): WebhookTransport
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders(): ?array
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     * @return WebhookTransport
     */
    public function setHeaders(array $headers): WebhookTransport
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @param array $header
     * @return WebhookTransport
     */
    public function addHeader(array $header)
    {
        $this->headers[] = $header;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSettingsBag()
    {
        return new ParameterBag(
            [
                'username'  => $this->getUsername(),
                'password'  => $this->getPassword(),
                'url'       => $this->getUrl(),
                'entity'    => $this->getEntity(),
                'event'     => $this->getEvent(),
                'method'    => $this->getMethod(),
                'headers'   => $this->getHeaders()
            ]
        );
    }
}
