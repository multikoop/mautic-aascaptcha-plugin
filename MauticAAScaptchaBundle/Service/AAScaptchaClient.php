<?php

/*
 * @copyright   2018 Konstantin Scheumann. All rights reserved
 * @author      Konstantin Scheumann
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticAAScaptchaBundle\Service;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use Mautic\CoreBundle\Helper\ArrayHelper;
use Mautic\FormBundle\Entity\Field;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticAAScaptchaBundle\Integration\AAScaptchaIntegration;
use Mautic\PluginBundle\Integration\AbstractIntegration;

class AAScaptchaClient
{

    /**
     * @var string
     */
    protected $aasApiKey;

    /**
     * @var string
     */
    protected $aasClusterLocalUrl;

    /**
     * FormSubscriber constructor.
     *
     * @param IntegrationHelper $integrationHelper
     */
    public function __construct(IntegrationHelper $integrationHelper)
    {
        $integrationObject = $integrationHelper->getIntegrationObject(AAScaptchaIntegration::INTEGRATION_NAME);

        if ($integrationObject instanceof AbstractIntegration) {
            $keys            = $integrationObject->getKeys();
            $this->aasApiKey   = isset($keys['aas_api_key']) ? $keys['aas_api_key'] : null;
            $this->aasClusterLocalUrl = isset($keys['aas_base_cluster_local_url']) ? $keys['aas_base_cluster_local_url'] : null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [];
    }

    public function getUserIpAddr() { 
        if(!empty($_SERVER['HTTP_CLIENT_IP'])){ 
            $ip = $_SERVER['HTTP_CLIENT_IP']; 
        } elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
             $ip = $_SERVER['HTTP_X_FORWARDED_FOR']; 
        }else{
             $ip = $_SERVER['REMOTE_ADDR']; 
        } return $ip; 
    }

    /**
     *  Verify 
     * @return bool
     */
    public function verify($response, $aasAssignmentId, $aasSessionId, Field $field)
    {
                
        $client   = new GuzzleClient(['timeout' => 10]);
        try {
            
            $response = $client->post(
                $this->aasClusterLocalUrl."/pc/v1/assignment/".$aasAssignmentId,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'X-API-Key' => $this->aasApiKey  // TODO remove in BA context
                    ],
                    'body' => json_encode([
                        'formId' => 'DIKUKO_MAUTIC',
                        'answer' => $response,
                        'userIpAddress' => $this->getUserIpAddr(),
                        'formProtectionLevel' => 'ALWAYS_CAPTCHA',
                        'userAgent' => $_SERVER['HTTP_USER_AGENT'],
                        'sessionId' => $aasSessionId
                    ])
                ]
            );       
            return true;
        } catch (RequestException $e){
            error_log($e);
            return false;
        }        

        return false;
    }
}
