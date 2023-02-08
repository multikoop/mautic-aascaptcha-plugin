<?php

/*
 * @copyright   
 * @author      
 * @license     
 */

namespace MauticPlugin\MauticAAScaptchaBundle\EventListener;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\CoreBundle\Factory\ModelFactory;
use Mautic\FormBundle\Event\FormBuilderEvent;
use Mautic\FormBundle\Event\ValidationEvent;
use Mautic\FormBundle\FormEvents;
use Mautic\LeadBundle\Event\LeadEvent;
use Mautic\LeadBundle\LeadEvents;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticAAScaptchaBundle\Form\Type\AAScaptchaType;
use MauticPlugin\MauticAAScaptchaBundle\Integration\AASCaptchaIntegration;
use MauticPlugin\MauticAAScaptchaBundle\AAScaptchaEvents;
use MauticPlugin\MauticAAScaptchaBundle\Service\AAScaptchaClient;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Mautic\PluginBundle\Integration\AbstractIntegration;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Psr\Log\LoggerInterface;

class FormSubscriber implements EventSubscriberInterface
{
    const MODEL_NAME_KEY_LEAD = 'lead.lead';

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var AAScaptchaClient
     */
    protected $aascaptchaClient;

    /**
     * @var string
     */
    protected $aasApiKey;

    /**
     * @var string
     */
    protected $aasBaseUrl;

    /**
     * @var boolean
     */
    
    private $captchaIsConfigured = false;

    /**
     * @var LeadModel
     */
    private $leadModel;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string|null
     */
    private $version;

    /**
     * @var LoggerInterface
     */
    private $logger;

     /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * 
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        IntegrationHelper $integrationHelper,
        AAScaptchaClient $aascaptchaClient,
        LeadModel $leadModel,
        TranslatorInterface $translator,
        LoggerInterface $logger,
        RequestStack $requestStack
    ) {
        $this->requestStack     = $requestStack;
        $this->logger         = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->aascaptchaClient = $aascaptchaClient;
        $integrationObject     = $integrationHelper->getIntegrationObject(AASCaptchaIntegration::INTEGRATION_NAME);
        
        if ($integrationObject instanceof AbstractIntegration) {
            $keys            = $integrationObject->getKeys();
            $this->aasApiKey = isset($keys['aas_api_key']) ? $keys['aas_api_key'] : null;
            $this->aasBaseUrl = isset($keys['aas_base_url']) ? $keys['aas_base_url'] : null;
            $aasClusterLocalUrl = isset($keys['aas_base_cluster_local_url']) ? $keys['aas_base_cluster_local_url'] : null;

            if ($this->aasApiKey && $this->aasBaseUrl && $aasClusterLocalUrl) {
                $this->captchaIsConfigured = true;
            }
        }
        $this->leadModel = $leadModel;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::FORM_ON_BUILD         => ['onFormBuild', 0],
            AAScaptchaEvents::ON_FORM_VALIDATE => ['onFormValidate', 0],
        ];
    }

    /**
     * @param FormBuilderEvent $event
     *
     * @throws \Mautic\CoreBundle\Exception\BadConfigurationException
     */
    public function onFormBuild(FormBuilderEvent $event)
    {
        if (!$this->captchaIsConfigured) {
            return;
        }

        $event->addFormField('plugin.aascaptcha', [
            'label'          => 'mautic.plugin.actions.aascaptcha',
            'formType'       => AAScaptchaType::class,
            'template'       => 'MauticAAScaptchaBundle:Integration:aascaptcha.html.php',            
            'builderOptions' => [
                'addLeadFieldList' => false,
                'addIsRequired'    => false,
                'addDefaultValue'  => false,
                'addSaveResult'    => true
            ],
            //'formTypeOptions' => ['compound' => true],
            'aas_api_key' => $this->aasApiKey,
            'aas_base_url'  => $this->aasBaseUrl,
        ]);

        $event->addValidator('plugin.aascaptcha.validator', [
            'eventName' => AAScaptchaEvents::ON_FORM_VALIDATE,
            'fieldType' => 'plugin.aascaptcha',
        ]);
    }

    /**
     * @param ValidationEvent $event
     */
    public function onFormValidate(ValidationEvent $event)
    {
        if (!$this->captchaIsConfigured) {
            return;
        }

        $fieldAlias = $event->getField()->getAlias(); // name des input elements der captcha antwort
        

        //$valueAnswer = $this->requestStack->getCurrentRequest()->get('mauticform')[$fieldAlias];
        $valueChallengeId = $this->requestStack->getCurrentRequest()->get('mauticform')[$fieldAlias.'_challengeid'];
        $valueSessionId = $this->requestStack->getCurrentRequest()->get('mauticform')[$fieldAlias.'_sessionid'];


        //$this->logger->info("event.getValue_() ".$event->getValue());        

        if ($this->aascaptchaClient->verify($event->getValue(), $valueChallengeId, $valueSessionId, $event->getField())) {
            return;
        }

        $event->failedValidation($this->translator === null ? 'Captcha answer was not correct.' : $this->translator->trans('mautic.integration.aascaptcha.failure_message'));
        //$event->failedValidation($valueChallengeId." ".$valueSessionId);
        //$event->failedValidation(serialize($event->getField()->getAlias()));
        //$event->failedValidation($strFields);
        
        

        $this->eventDispatcher->addListener(LeadEvents::LEAD_POST_SAVE, function (LeadEvent $event) {
            if ($event->isNew()){
                $this->leadModel->deleteEntity($event->getLead());
            }
        }, -255);
    }
}
