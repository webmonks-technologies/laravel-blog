<?php namespace WebMonksBlog\Captcha;

/**
 * Trait UsesCaptcha
 *
 * For instantiating the config("webmonksblog.captcha.captcha_type") object.
 *
 * @package WebMonksBlog\Captcha
 */
trait UsesCaptcha
{
    /**
     * Return either null (if captcha is not enabled), or the captcha object (which should implement CaptchaInterface interface / extend the CaptchaAbstract class)
     * @return null|CaptchaAbstract
     */
    private function getCaptchaObject()
    {
        if (!config("webmonksblog.captcha.captcha_enabled")) {
            return null;
        }

        // else: captcha is enabled
        /** @var string $captcha_class */
        $captcha_class = config("webmonksblog.captcha.captcha_type");
        return new $captcha_class;
    }

}