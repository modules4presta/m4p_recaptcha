{**
 * m4p_recaptcha
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 *}

<input type="hidden" name="{$m4pRecaptchaField}" value="" data-action="{$m4pRecaptchaAction}">
<p class="m4p-recaptcha-note">
  {l s='Protected by reCAPTCHA – the Google [1]Privacy Policy[/1] and [2]Terms of Service[/2] apply.'
     d='Modules.M4precaptcha.Shop'
     sprintf=[
       '[1]' => '<a href="https://policies.google.com/privacy" target="_blank" rel="noopener nofollow">',
       '[/1]' => '</a>',
       '[2]' => '<a href="https://policies.google.com/terms" target="_blank" rel="noopener nofollow">',
       '[/2]' => '</a>'
     ]}
</p>
