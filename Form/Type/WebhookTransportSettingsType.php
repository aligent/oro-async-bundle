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

namespace Aligent\AsyncEventsBundle\Form\Type;

use Aligent\AsyncEventsBundle\Entity\WebhookTransport;
use Aligent\AsyncEventsBundle\Provider\WebhookConfigProvider;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Oro\Bundle\FormBundle\Form\Type\OroChoiceType;
use Oro\Bundle\FormBundle\Form\Type\OroEncodedPlaceholderPasswordType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class WebhookTransportSettingsType extends AbstractType
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * WebhookTransportSettingsType constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'username',
                TextType::class,
                [
                    'required' => true,
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                ]
            )
            ->add(
                'password',
                OroEncodedPlaceholderPasswordType::class,
                [
                    'required' => true,
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                    'browser_autocomplete' => true,
                ]
            )
            ->add(
                'entity',
                OroChoiceType::class,
                [
                    'required' => true,
                    'choices' => $this->getEntityList()
                ]
            )
            ->add(
                'event',
                OroChoiceType::class,
                [
                    'required' => true,
                    'choices' => $this->getEventsList()
                ]
            )
            ->add(
                'method',
                ChoiceType::class,
                [
                    'required' => true,
                    'choices' => [
                        'POST' => 'POST',
                        'PUT' => 'PUT',
                        'PATCH' => 'PATCH',
                        'DELETE' => 'DELETE'
                    ]
                ]
            )
            ->add(
                'url',
                UrlType::class,
                [
                    'required' => true
                ]
            )
            ->add(
                'headers',
                CollectionType::class,
                [
                    'entry_type' => WebhookHeaderType::class,
                    'handle_primary' => false
                ]
            );
    }

    /**
     * @return array
     */
    protected function getEntityList()
    {
        $em = $this->registry->getManager();
        $entities = [];

        foreach ($em->getMetadataFactory()->getAllMetadata() as $metadata) {
            $class = $metadata->getName();
            $entities[$class] = $class;
        }

        return $entities;
    }

    /**
     * @return array
     */
    protected function getEventsList()
    {
        $events = [];
        $events[WebhookConfigProvider::CREATE] = WebhookConfigProvider::CREATE;
        $events[WebhookConfigProvider::UPDATE] = WebhookConfigProvider::UPDATE;
        $events[WebhookConfigProvider::DELETE] = WebhookConfigProvider::DELETE;

        return $events;
    }
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', WebhookTransport::class);
    }
}
