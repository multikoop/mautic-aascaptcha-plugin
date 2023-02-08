<?php

$defaultInputClass = (isset($inputClass)) ? $inputClass : 'input';
$containerType     = 'div-wrapper';

include __DIR__.'/../../../../app/bundles/FormBundle/Views/Field/field_helper.php';

$action   = $app->getRequest()->get('objectAction');
$settings = $field['properties'];
// AAS Properties
$aasApiKey = $field['customParameters']['aas_api_key'];
$aasBaseUrl = $field['customParameters']['aas_base_url'];
$aasFormId = "DIKUKO_MAUTIC";
$aasFormProtectionLevel = "ALWAYS_CAPTCHA";

$containerId = 'mauticform'.$formName.'_'.$id;
$formName    = str_replace('_', '', $formName);

$hashedFormName = md5($formName);
$formButtons = (!empty($inForm)) ? $view->render(
    'MauticFormBundle:Builder:actions.html.php',
    [
        'deleted'        => false,
        'id'             => $id,
        'formId'         => $formId,
        'formName'       => $formName,
        'disallowDelete' => false,
    ]
) : '';
// zeige Label, falls showLabel auf JA steht
$label = (!$field['showLabel'])
    ? ''
    : <<<HTML
<label $labelAttr>{$view->escape($field['label'])}</label>
HTML;

$jsElement = <<<JSELEMENT
	<script type="text/javascript">    
        window["_aas"] = window["_aas"] || {};
        _aas["host"] = window.location.hostname;
        _aas["baseUrl"] = "{$base_url}";
        _aas["formName"] = "{$formName}";        
        _aas["clientUserAgent"] = window.navigator.userAgent;
               
        
        async function getAASCaptcha_{$hashedFormName}() {
            //document.getElementById("mauticform_input_aasform_emailadresse").value = "ak@enpit.de";
            //document.getElementById("mauticform_input_aasform_ba_captcha").value = "asdf33";

            
            //document.getElementById("aas_captcha_{$hashedFormName}_challengeid").value = "challengeId4711";
            //document.getElementById("aas_captcha_{$hashedFormName}_sessionid").value = "sessionId4711";


            fetch('{$aasBaseUrl}/pc/v1/assignment', {
                method: 'POST',
                body: JSON.stringify({
                    'formId': "{$aasFormId}",
                    'formProtectionLevel': "{$aasFormProtectionLevel}"
                }),
                headers: {
                    'Content-type': 'application/json; charset=UTF-8',
                    'Accept': 'application/json',
                    'X-API-Key':  '{$aasApiKey}'
                }
            }).then(function (response) {
                if (response.ok) {
                    return response.json();
                }
                return Promise.reject(response);
            }).then(function (data) {
                window["_aas"] = window["_aas"] || {};
                _aas["challenge_id"] = data["challengeId"];
                _aas["session_id"] = data["sessionId"];

                let inputChallengeId = document.createElement("input");
                inputChallengeId.setAttribute("type", "hidden");
                inputChallengeId.setAttribute("name", "mauticform[{$field['alias']}_challengeid]");
                inputChallengeId.setAttribute("value", data["challengeId"]);
                
                let inputSessionId = document.createElement("input");
                inputSessionId.setAttribute("type", "hidden");
                inputSessionId.setAttribute("name", "mauticform[{$field['alias']}_sessionid]");
                inputSessionId.setAttribute("value", data["sessionId"]);

                //append to form element that you want .
                document.getElementById("{$containerId}").appendChild(inputChallengeId);
                document.getElementById("{$containerId}").appendChild(inputSessionId);

                //document.getElementById("aas_captcha_{$hashedFormName}_challengeid").value = data["challengeId"];
                //document.getElementById("aas_captcha_{$hashedFormName}_sessionid").value = data["sessionId"];

                let url = '{$aasBaseUrl}/ct/v1/captcha/' + _aas["challenge_id"] + '?type=image&languageIso639Code=de';
                
                console.info(url);
                let img = document.getElementById("aas_captcha_{$hashedFormName}");
                img.setAttribute("src",url);
                img.style.visibility="visible";
                img.style.margin="15px 0px";
            }).catch(function (error) {
                console.error(error);
            });
        
        }
 
        window.onload = (e) => {
            window["_aas"] = window["_aas"] || {};
            getAASCaptcha_{$hashedFormName}();
        }    
</script>
JSELEMENT;

$html = <<<HTML
    {$jsElement}            
    <div $containerAttr>
        <img id="aas_captcha_{$hashedFormName}" src="#" style="visibility:hidden;">
        {$label}
        <!--input type="hidden" id="aas_captcha_{$hashedFormName}_challengeid" name="mauticform[{$field['alias']}_challengeid]">
        <input type="hidden" id="aas_captcha_{$hashedFormName}_sessionid" name="mauticform[{$field['alias']}_sessionid]"-->
        <input $inputAttr>
        <span class="mauticform-errormsg" style="display: none;"></span>
    </div>
HTML;
?>


<?php
echo $html;
?>

