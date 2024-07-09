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

namespace Aligent\AsyncEventsBundle\Tests\Unit\Form\Type;

use Aligent\AsyncEventsBundle\Entity\WebhookTransport;
use Aligent\AsyncEventsBundle\Form\Type\WebhookTransportSettingsType;
use Aligent\AsyncEventsBundle\Provider\WebhookCustomEventsProviderInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Oro\Bundle\FormBundle\Form\Type\OroChoiceType;
use Oro\Bundle\FormBundle\Form\Type\OroEncodedPlaceholderPasswordType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class WebhookTransportSettingsTypeTest extends TestCase
{
    /**
     * @var WebhookTransportSettingsType
     */
    private $type;

    protected function setUp(): void
    {
        $doctrine = $this->createMock(ManagerRegistry::class);
        $objectManager = $this->createMock(ObjectManager::class);
        $metadataFactory = $this->createMock(ClassMetadataFactory::class);
        /** @var ClassMetadata[] */
        $metadata = $this->createMock(ClassMetadata::class);

        $doctrine->expects($this->any())
            ->method('getManager')
            ->willReturn($objectManager);

        $objectManager->expects($this->any())
            ->method('getMetadataFactory')
            ->willReturn($metadataFactory);

        $metadataFactory->expects($this->any())
            ->method('getAllMetadata')
            ->willReturn($metadata);

        $translator = $this->createMock(TranslatorInterface::class);
        $customEventsProvider = $this->createMock(WebhookCustomEventsProviderInterface::class);

        $this->type = new WebhookTransportSettingsType($doctrine, $translator, $customEventsProvider);
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder(FormBuilderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $expectedFields = [
            'username' => TextType::class,
            'password' => OroEncodedPlaceholderPasswordType::class,
            'entity' => OroChoiceType::class,
            'event' => OroChoiceType::class,
            'method' => ChoiceType::class,
            'url' => UrlType::class,
            'headers' => CollectionType::class
        ];

        $builder->expects($this->exactly(count($expectedFields)))
            ->method('add')
            ->will($this->returnSelf());

        $builder->expects($this->exactly(count($expectedFields)))
            ->method('add')
            ->withConsecutive(
                ...array_map(
                    fn ($fieldName, $formType) => [$fieldName, $formType],
                    array_keys($expectedFields),
                    $expectedFields
                )
            )
            ->will($this->returnSelf());

        $this->type->buildForm($builder, []);
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $resolver
            ->expects($this->once())
            ->method('setDefault')
            ->with('data_class', WebhookTransport::class);
        $this->type->configureOptions($resolver);
    }
}
