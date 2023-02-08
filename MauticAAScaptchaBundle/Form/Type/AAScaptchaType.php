<?php

/*
 * @copyright   
 * @author      
 * @license     
 */

namespace MauticPlugin\MauticAAScaptchaBundle\Form\Type;

use Mautic\CoreBundle\Form\Type\FormButtonsType;
use Mautic\CoreBundle\Form\Type\YesNoButtonGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AAScaptchaType.
 */
class AAScaptchaType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    /*public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'multiple'          => true,
            'choices' => [
                'mautic.recaptcha.v2' => 'sessId',
                'mautic.recaptcha.v3' => 'challengeId'
            ],
            'data' => []
        ]);
    }*/

    /*public function getParent(){
        return ChoiceType::class;
    }*/


    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {       

        
        $builder->add(
            'buttons',
            FormButtonsType::class,
            [
                'apply_text'     => false,
                'save_text'      => 'mautic.core.form.submit',
                'cancel_onclick' => 'javascript:void(0);',
                'cancel_attr'    => [
                    'data-dismiss' => 'modal',
                ],
            ]
        );

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }

    }


    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'aascaptcha';
    }
}
