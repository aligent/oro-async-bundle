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

namespace Aligent\AsyncEventsBundle\Tests\Unit\Entity;

use Aligent\AsyncEventsBundle\Entity\WebhookTransport;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

class WebhookTransportTest extends TestCase
{
    public function testGettersAndSetters()
    {
        $entity = new WebhookTransport();

        $this->assertEquals(
            null,
            $entity->getUsername()
        );
        $this->assertEquals(
            null,
            $entity->getPassword()
        );
        $this->assertEquals(
            null,
            $entity->getChannel()
        );
        $this->assertEquals(
            new ParameterBag(
                [
                    'username'  => null,
                    'password'  => null,
                    'url'       => null,
                    'entity'    => null,
                    'event'     => null,
                    'method'    => null,
                    'headers'   => []
                ]
            ),
            $entity->getSettingsBag()
        );

        $username = 'TestUser';
        $password = 'TestPassword';
        $channel = new Channel();
        $entity->setUsername($username);
        $entity->setPassword($password);
        $entity->setChannel($channel);

        $this->assertEquals(
            $username,
            $entity->getUsername()
        );
        $this->assertEquals(
            $password,
            $entity->getPassword()
        );
        $this->assertSame(
            $channel,
            $entity->getChannel()
        );
        $this->assertEquals(
            new ParameterBag(
                [
                    'username'  => $username,
                    'password'  => $password,
                    'url'       => null,
                    'entity'    => null,
                    'event'     => null,
                    'method'    => null,
                    'headers'   => []
                ]
            ),
            $entity->getSettingsBag()
        );
    }
}
