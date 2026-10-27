<?php

declare(strict_types=1);

/**
 * m4p_recaptcha
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/classes/M4pRecaptchaVerifier.php';

class M4p_Recaptcha extends Module
{
    public const ACTION_CONTACT = 'contact';
    public const ACTION_LOGIN = 'login';
    public const ACTION_REGISTRATION = 'registration';
    public const ACTION_REVIEW = 'review';

    private const SWITCHES = [
        self::ACTION_CONTACT => 'M4PRECAPTCHA_ON_CONTACT',
        self::ACTION_LOGIN => 'M4PRECAPTCHA_ON_LOGIN',
        self::ACTION_REGISTRATION => 'M4PRECAPTCHA_ON_REGISTRATION',
        self::ACTION_REVIEW => 'M4PRECAPTCHA_ON_REVIEW',
    ];

    private const PAGES = [
        'contact' => self::ACTION_CONTACT,
        'authentication' => self::ACTION_LOGIN,
        'registration' => self::ACTION_REGISTRATION,
        'product' => self::ACTION_REVIEW,
    ];

    private const SETTINGS = [
        'M4PRECAPTCHA_SITE_KEY' => '',
        'M4PRECAPTCHA_SECRET_KEY' => '',
        'M4PRECAPTCHA_MIN_SCORE' => '0.5',
        'M4PRECAPTCHA_ON_CONTACT' => '1',
        'M4PRECAPTCHA_ON_LOGIN' => '1',
        'M4PRECAPTCHA_ON_REGISTRATION' => '1',
        'M4PRECAPTCHA_ON_REVIEW' => '1',
        'M4PRECAPTCHA_REVIEW_MODULE' => 'productcomments',
    ];

    public function __construct()
    {
        $this->name = 'm4p_recaptcha';
        $this->tab = 'front_office_features';
        $this->version = '1.1.0';
        $this->author = 'Modules4Presta';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('reCAPTCHA v3', [], 'Modules.M4precaptcha.Admin');
        $this->description = $this->trans(
            'Protects the contact, login, registration and product review forms with reCAPTCHA v3.',
            [],
            'Modules.M4precaptcha.Admin'
        );
    }

    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    public function install(): bool
    {
        foreach (self::SETTINGS as $key => $default) {
            Configuration::updateValue($key, $default);
        }

        return parent::install()
            && $this->registerHook('actionFrontControllerSetMedia')
            && $this->registerHook('actionFrontControllerInitBefore')
            && $this->registerHook('actionSubmitAccountBefore')
            && $this->registerHook('displayContactCaptcha')
            && $this->registerHook('displayPaCaptcha')
            && $this->registerHook('displayCustomerAccountForm')
            && $this->registerHook('displayM4pRecaptcha');
    }

    public function uninstall(): bool
    {
        foreach (array_keys(self::SETTINGS) as $key) {
            Configuration::deleteByName($key);
        }

        return parent::uninstall();
    }

    public function getContent(): string
    {
        $output = '';

        if (Tools::isSubmit('submitM4pRecaptcha')) {
            Configuration::updateValue('M4PRECAPTCHA_SITE_KEY', trim((string) Tools::getValue('M4PRECAPTCHA_SITE_KEY')));
            Configuration::updateValue('M4PRECAPTCHA_SECRET_KEY', trim((string) Tools::getValue('M4PRECAPTCHA_SECRET_KEY')));
            Configuration::updateValue('M4PRECAPTCHA_MIN_SCORE', (string) (float) Tools::getValue('M4PRECAPTCHA_MIN_SCORE'));
            $reviewModule = (string) Tools::getValue('M4PRECAPTCHA_REVIEW_MODULE');
            Configuration::updateValue(
                'M4PRECAPTCHA_REVIEW_MODULE',
                Validate::isModuleName($reviewModule) ? $reviewModule : ''
            );
            foreach (self::SWITCHES as $key) {
                Configuration::updateValue($key, (int) (bool) Tools::getValue($key));
            }
            $output .= $this->displayConfirmation($this->trans('Settings saved.', [], 'Admin.Notifications.Success'));
        }

        return $output . $this->renderForm();
    }

    private function renderForm(): string
    {
        $switch = [
            ['id' => 'on', 'value' => 1, 'label' => $this->trans('Yes', [], 'Admin.Global')],
            ['id' => 'off', 'value' => 0, 'label' => $this->trans('No', [], 'Admin.Global')],
        ];
        $labels = [
            self::ACTION_CONTACT => $this->trans('Protect the contact form', [], 'Modules.M4precaptcha.Admin'),
            self::ACTION_LOGIN => $this->trans('Protect the login form', [], 'Modules.M4precaptcha.Admin'),
            self::ACTION_REGISTRATION => $this->trans('Protect registration', [], 'Modules.M4precaptcha.Admin'),
            self::ACTION_REVIEW => $this->trans('Protect product reviews', [], 'Modules.M4precaptcha.Admin'),
        ];

        $inputs = [
            [
                'type' => 'text',
                'label' => $this->trans('Site key', [], 'Modules.M4precaptcha.Admin'),
                'name' => 'M4PRECAPTCHA_SITE_KEY',
                'desc' => $this->trans(
                    'Google Cloud → reCAPTCHA → key ID of a score-based website key (v3).',
                    [],
                    'Modules.M4precaptcha.Admin'
                ),
            ],
            [
                'type' => 'text',
                'label' => $this->trans('Secret key', [], 'Modules.M4precaptcha.Admin'),
                'name' => 'M4PRECAPTCHA_SECRET_KEY',
                'desc' => $this->trans('The legacy secret key of the same key.', [], 'Modules.M4precaptcha.Admin'),
            ],
            [
                'type' => 'text',
                'label' => $this->trans('Minimum score', [], 'Modules.M4precaptcha.Admin'),
                'name' => 'M4PRECAPTCHA_MIN_SCORE',
                'desc' => $this->trans(
                    '0.0 is certainly a bot, 1.0 certainly a human. Google recommends 0.5.',
                    [],
                    'Modules.M4precaptcha.Admin'
                ),
            ],
            [
                'type' => 'text',
                'label' => $this->trans('Reviews module', [], 'Modules.M4precaptcha.Admin'),
                'name' => 'M4PRECAPTCHA_REVIEW_MODULE',
                'desc' => $this->trans(
                    'Technical name of the module that saves product reviews. Leave it empty to check nothing but the forms above.',
                    [],
                    'Modules.M4precaptcha.Admin'
                ),
            ],
        ];
        foreach (self::SWITCHES as $action => $key) {
            $inputs[] = ['type' => 'switch', 'label' => $labels[$action], 'name' => $key, 'is_bool' => true, 'values' => $switch];
        }

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitM4pRecaptcha';
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->tpl_vars = ['fields_value' => array_combine(
            array_keys(self::SETTINGS),
            array_map(static fn ($key) => Configuration::get($key), array_keys(self::SETTINGS))
        )];

        return $helper->generateForm([[
            'form' => [
                'legend' => ['title' => $this->trans('reCAPTCHA v3', [], 'Modules.M4precaptcha.Admin'), 'icon' => 'icon-shield'],
                'input' => $inputs,
                'submit' => ['title' => $this->trans('Save', [], 'Admin.Actions')],
            ],
        ]]);
    }

    /**
     * Only the module script is enqueued; it loads Google's api.js on the first focus in a protected form.
     */
    public function hookActionFrontControllerSetMedia(): void
    {
        $action = $this->actionForCurrentPage();
        if ($action === null) {
            return;
        }

        $this->context->controller->registerJavascript(
            'm4p_recaptcha',
            'modules/' . $this->name . '/views/js/m4p_recaptcha.js',
            ['position' => 'bottom', 'priority' => 200]
        );
        $this->context->controller->registerStylesheet(
            'm4p_recaptcha',
            'modules/' . $this->name . '/views/css/m4p_recaptcha.css',
            ['media' => 'all', 'priority' => 200]
        );

        Media::addJsDef([
            'm4pRecaptcha' => [
                'siteKey' => (string) Configuration::get('M4PRECAPTCHA_SITE_KEY'),
                'field' => M4pRecaptchaVerifier::FIELD,
            ],
        ]);
    }

    /**
     * Rejects contact, login and review submissions without a valid token before their controllers run.
     */
    public function hookActionFrontControllerInitBefore(array $params): void
    {
        $controller = $params['controller'] ?? null;
        if (!$controller instanceof FrontController) {
            return;
        }

        if ($controller instanceof ModuleFrontController) {
            $reviewModule = (string) Configuration::get('M4PRECAPTCHA_REVIEW_MODULE');
            if ('' !== $reviewModule
                && ($controller->module->name ?? '') === $reviewModule
                && Tools::getValue('action') === 'add_comment'
                && !$this->passes(self::ACTION_REVIEW)
            ) {
                // The shape productcomments and its forks render as their own validation error.
                exit(json_encode(['result' => false, 'errors' => [$this->failureMessage()]]));
            }

            return;
        }

        $submits = [self::ACTION_CONTACT => 'submitMessage', self::ACTION_LOGIN => 'submitLogin'];
        $action = self::PAGES[$controller->php_self ?? ''] ?? null;
        if (!isset($submits[$action]) || !Tools::isSubmit($submits[$action]) || $this->passes($action)) {
            return;
        }

        // Without the submit flag the controller renders the form again, keeping the entered values.
        unset($_POST[$submits[$action]], $_GET[$submits[$action]]);
        $controller->errors[] = $this->failureMessage();
    }

    public function hookActionSubmitAccountBefore(): bool
    {
        if (($this->context->controller->php_self ?? '') !== 'registration' || $this->passes(self::ACTION_REGISTRATION)) {
            return true;
        }

        $this->context->controller->errors[] = $this->failureMessage();

        return false;
    }

    public function hookDisplayContactCaptcha(): string
    {
        return $this->renderField(self::ACTION_CONTACT);
    }

    public function hookDisplayPaCaptcha(array $params): string
    {
        return ($params['posTo'] ?? '') === 'login' ? $this->renderField(self::ACTION_LOGIN) : '';
    }

    public function hookDisplayCustomerAccountForm(): string
    {
        return $this->renderField(self::ACTION_REGISTRATION);
    }

    public function hookDisplayM4pRecaptcha(array $params): string
    {
        return $this->renderField((string) ($params['action'] ?? ''));
    }

    public function failureMessage(): string
    {
        return $this->trans(
            'We could not confirm that you are not a robot. Please reload the page and try again.',
            [],
            'Modules.M4precaptcha.Shop'
        );
    }

    private function renderField(string $action): string
    {
        if ($this->actionForCurrentPage() !== $action) {
            return '';
        }

        $this->smarty->assign([
            'm4pRecaptchaField' => M4pRecaptchaVerifier::FIELD,
            'm4pRecaptchaAction' => $action,
        ]);

        return $this->fetch('module:' . $this->name . '/views/templates/hook/field.tpl');
    }

    private function passes(string $action): bool
    {
        return !$this->protects($action)
            || M4pRecaptchaVerifier::fromConfiguration()->verify((string) Tools::getValue(M4pRecaptchaVerifier::FIELD), $action);
    }

    private function protects(string $action): bool
    {
        return isset(self::SWITCHES[$action])
            && (bool) Configuration::get(self::SWITCHES[$action])
            && M4pRecaptchaVerifier::fromConfiguration()->isConfigured();
    }

    private function actionForCurrentPage(): ?string
    {
        $action = self::PAGES[$this->context->controller->php_self ?? ''] ?? null;

        return $action !== null && $this->protects($action) ? $action : null;
    }
}
