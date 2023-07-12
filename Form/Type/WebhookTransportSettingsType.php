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
use Aligent\AsyncEventsBundle\Provider\WebhookCustomEventsProviderInterface;
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
use Symfony\Contracts\Translation\TranslatorInterface;

class WebhookTransportSettingsType extends AbstractType
{
    protected ManagerRegistry $registry;
    protected TranslatorInterface $translator;
    protected WebhookCustomEventsProviderInterface $customEventsProvider;

    /**
     * @param ManagerRegistry $registry
     * @param TranslatorInterface $translator
     * @param WebhookCustomEventsProviderInterface $customEventsProvider
     */
    public function __construct(
        ManagerRegistry $registry,
        TranslatorInterface $translator,
        WebhookCustomEventsProviderInterface $customEventsProvider
    ) {
        $this->registry = $registry;
        $this->translator = $translator;
        $this->customEventsProvider = $customEventsProvider;
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
                    'choices' => $this->getEventsList(),
                    'choice_label' => function ($choice, $key) {
                        return $this->translator->trans($key);
                    }
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
        /** @var array<string, string> $events */
        $events = [];
        // [ translation for the event's name  =>  event key (event key is saved in the db and is 16 char long ]
        $events['aligent.async.transport.form.event.create'] = WebhookConfigProvider::CREATE;
        $events['aligent.async.transport.form.event.update'] = WebhookConfigProvider::UPDATE;
        $events['aligent.async.transport.form.event.delete'] = WebhookConfigProvider::DELETE;

        foreach ($this->customEventsProvider->getCustomEvents() as $customEvent) {
            $events[$customEvent->getTranslationName()] = $customEvent->getKey();
        }

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
