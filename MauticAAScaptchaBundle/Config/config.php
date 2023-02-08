<?php

/*
 * @copyright   
 * @author      
 * @license     
 */

return [
    'name'        => 'BA-AAScaptcha',
    'description' => 'AASCAPTCHA integration',
    'version'     => '1.11',
    'author'      => 'BA',

    'routes' => [

    ],

    'services' => [
        'events' => [
            'mautic.aascaptcha.event_listener.form_subscriber' => [
                'class'     => \MauticPlugin\MauticAAScaptchaBundle\EventListener\FormSubscriber::class,
                'arguments' => [
                    'event_dispatcher',
                    'mautic.helper.integration',
                    'mautic.aascaptcha.service.aascaptcha_client',
                    'mautic.lead.model.lead',
                    'translator',
                    'logger',
                    'request_stack'
                ],
            ],
        ],
        'models' => [

        ],
        'others'=>[
            'mautic.aascaptcha.service.aascaptcha_client' => [
                'class'     => \MauticPlugin\MauticAAScaptchaBundle\Service\AAScaptchaClient::class,
                'arguments' => [
                    'mautic.helper.integration',
                ],
            ],
        ],
        'integrations' => [
            'mautic.integration.aascaptcha' => [
                'class'     => \MauticPlugin\MauticAAScaptchaBundle\Integration\AAScaptchaIntegration::class,
                'arguments' => [
                    'event_dispatcher',
                    'mautic.helper.cache_storage',
                    'doctrine.orm.entity_manager',
                    'session',
                    'request_stack',
                    'router',
                    'translator',
                    'logger',
                    'mautic.helper.encryption',
                    'mautic.lead.model.lead',
                    'mautic.lead.model.company',
                    'mautic.helper.paths',
                    'mautic.core.model.notification',
                    'mautic.lead.model.field',
                    'mautic.plugin.model.integration_entity',
                    'mautic.lead.model.dnc',
                ],
            ],
        ],
    ],
    'parameters' => [

    ],
];
