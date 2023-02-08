# Mautic AAS Captcha Plugin

This Plugin brings AAS Captcha integration to mautic.

## Installation via .zip
Download the .zip file, extract it into the `plugins/` directory and rename the new directory to `MauticAAScaptchaBundle`.

Clear the cache via console command `php app/console cache:clear --env=prod` (might take a while) *OR* manually delete the `app/cache/prod` directory.

## Configuration
Navigate to the Plugins page and click "Install/Upgrade Plugins". You should now see a "AAS Captcha" plugin. Open it to configure api key, base url and cluster internal url.

## Usage in Mautic Form
Add "AAS Captcha" field to the Form and save changes.

## Kudos

Thanks to Konstantin and the reCaptcha Plugin. This plugin is heavily based on this one https://github.com/KonstantinCodes/mautic-recaptcha

