<?php

namespace Aligent\AsyncEventsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class WebhookHeaderType
 *
 * @category  Aligent
 * @package   Aligent\WebhookBundle\Form\Type
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @link      http://www.aligent.com.au/
 */
class WebhookHeaderType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'header',
            TextType::class,
            [
                'required' => true,

            ]
        )->add(
            'value',
            TextType::class,
            [
                'required' => true
            ]
        );
    }
}
