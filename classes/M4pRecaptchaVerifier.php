<?php

declare(strict_types=1);

/**
 * m4precaptcha
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 */

class M4pRecaptchaVerifier
{
    public const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';
    public const FIELD = 'm4p_recaptcha_token';

    private string $secret;
    private float $minScore;

    public function __construct(string $secret, float $minScore)
    {
        $this->secret = $secret;
        $this->minScore = $minScore;
    }

    public static function fromConfiguration(): self
    {
        return new self(
            (string) Configuration::get('M4PRECAPTCHA_SECRET_KEY'),
            (float) Configuration::get('M4PRECAPTCHA_MIN_SCORE')
        );
    }

    public function isConfigured(): bool
    {
        return $this->secret !== '' && Configuration::get('M4PRECAPTCHA_SITE_KEY') !== '';
    }

    /**
     * A missing or unverifiable token fails closed; an unreachable Google fails open, so an outage
     * at their end cannot take the shop's forms down with it.
     */
    public function verify(string $token, string $action): bool
    {
        if (!$this->isConfigured()) {
            return true;
        }

        if ($token === '') {
            return false;
        }

        $response = $this->ask($token);
        if ($response === null) {
            return true;
        }

        if (empty($response['success'])) {
            return false;
        }

        if (isset($response['action']) && $response['action'] !== $action) {
            return false;
        }

        return !isset($response['score']) || (float) $response['score'] >= $this->minScore;
    }

    private function ask(string $token): ?array
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                // No remoteip: behind Cloudflare it would be the proxy's address, and Google treats it as optional.
                'content' => http_build_query(['secret' => $this->secret, 'response' => $token]),
                'timeout' => 5,
                'ignore_errors' => true,
            ],
        ]);

        $body = @Tools::file_get_contents(self::VERIFY_URL, false, $context);
        if ($body === false) {
            return null;
        }

        $decoded = json_decode((string) $body, true);

        return is_array($decoded) ? $decoded : null;
    }
}
