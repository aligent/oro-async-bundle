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

namespace Aligent\AsyncEventsBundle\Tests\Unit\DependencyInjection;

use Aligent\AsyncEventsBundle\DependencyInjection\AligentAsyncEventsExtension;
use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;

class AligentAsyncEventsExtensionTest extends ExtensionTestCase
{
    public function testLoad()
    {
        $this->loadExtension(new AligentAsyncEventsExtension());

        $expectedDefinitions = [
            \Aligent\AsyncEventsBundle\Security\WebhookAuthenticator::class,
            \Aligent\AsyncEventsBundle\Provider\WebhookIntegrationProvider::class,
            \Aligent\AsyncEventsBundle\EventListener\WebhookLoggingEventListener::class,
            \Aligent\AsyncEventsBundle\Integration\WebhookChannel::class,
            \Aligent\AsyncEventsBundle\Integration\WebhookTransport::class
        ];
        $this->assertDefinitionsLoaded($expectedDefinitions);
    }

    public function testGetAlias()
    {
        $extension = new AligentAsyncEventsExtension();

        $this->assertEquals('aligent_async_events', $extension->getAlias());
    }
}
