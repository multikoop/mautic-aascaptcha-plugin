<?php

/*
 * @copyright   2018 Konstantin Scheumann. All rights reserved
 * @author      Konstantin Scheumann
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticAAScaptchaBundle\Integration;

use Mautic\PluginBundle\Integration\AbstractIntegration;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;

/**
 * Class AAScaptchaIntegration.
 */
class AAScaptchaIntegration extends AbstractIntegration
{
    const INTEGRATION_NAME = 'AAScaptcha';

    public function getName()
    {
        return self::INTEGRATION_NAME;
    }

    public function getDisplayName()
    {
        return 'Anti Automatisierungsservice';
    }

    public function getAuthenticationType()
    {
        return 'none';
    }

    public function getRequiredKeyFields()
    {
        return [
            'aas_api_key'   => 'mautic.integration.aascaptcha.api_key',
            'aas_base_url'   => 'mautic.integration.aascaptcha.base_url',
            'aas_base_cluster_local_url'   => 'mautic.integration.aascaptcha.base_cluster_local_url'
        ];
    }

    /**
     * @param FormBuilder|Form $builder
     * @param array            $data
     * @param string           $formArea
     */
    public function appendToForm(&$builder, $data, $formArea)
    {
        // keine weiteren aas optionen vorerst
        
    }
}
