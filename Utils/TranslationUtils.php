<?php

namespace App\Utils;

use App\Model\Locale;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TranslationUtils{
    private static $done = false;
    private static $values = [];
    private static $templates = [];
    const TRANSLATION_TEMPLATE_PATH = '../templates';
    const TWIG_CACHE_PATH = '../cache';
    private function __construct(){
        if(!self::$done){
            self::$values = [
                Locale::$fr_FR->getCode() => [
                    "Notification.0.Title" => "COCO",
                    "Notification.0.Body" => "Bonjour ! Vous avez un nouveau questionaire, prennez quelques minutes pour le remplir.",
                    "Notification.1.Title" => "COCO",
                    "Notification.1.Body" => "Il reste quelques questions en attente…",
                    'Email.ForgotPassword.Subject' => "Changement de mot de passe",
                    'Email.ForgotPassword.Text.Intro' => "Bonjour",
                    'Email.ForgotPassword.Text.Body' => "Vous pouvez changer votre mot de passe avec le token ci-dessous:",
                ],
                Locale::$nl_NL->getCode() => [
                    "Notification.0.Title" => "COCO",
                    "Notification.0.Body" => "Hoi! Neem alsjeblieft de tijd om de vragen in te vullen. ",
                    "Notification.1.Title" => "COCO",
                    "Notification.1.Body" => "Er zijn nog vragen die beantwoord dienen te worden…",
                    'Email.ForgotPassword.Subject' => "Changement de mot de passe",
                    'Email.ForgotPassword.Text.Intro' => "Bonjour",
                    'Email.ForgotPassword.Text.Body' => "Vous pouvez changer votre mot de passe avec le token ci-dessous:",
                ],
                Locale::$en_GB->getCode() => [
                    "Notification.0.Title" => "COCO",
                    "Notification.0.Body" => "Hi there! Please take time to fill in the questions.",
                    "Notification.1.Title" => "COCO",
                    "Notification.1.Body" => "There are still questions to be answered…",
                    'Email.ForgotPassword.Subject' => "Changement de mot de passe",
                    'Email.ForgotPassword.Text.Intro' => "Bonjour %username%,",
                    'Email.ForgotPassword.Text.Body' => "Vous pouvez changer votre mot de passe avec le token ci-dessous:",
                ]
            ];
            self::$templates = [
                'forgotPassword' => [
                    'template' => 'email/forgotPassword.html.twig',
                ]
            ];
            self::$done=true;
        }
    }

    public static function getTranslation(Locale $locale, string $property, array $parameters = []) :string
    {
        if(!self::$done){
            new TranslationUtils();
        }
        if(isset(self::$values[$locale->getCode()])){
            $translation = self::$values[$locale->getCode()][$property];
            foreach($parameters as $key => $value) {
                $translation = str_replace($key, $value, $translation);
            }
            return $translation;
        }
        return $property;
	}

	public static function getTemplateTranslation(string $template, array $parameters = [])
    {
        if(!self::$done){
            new TranslationUtils();
        }
        if(isset(self::$templates[$template])){
            $templateDesc = self::$templates[$template];
            $loader = new FilesystemLoader(self::TRANSLATION_TEMPLATE_PATH);
            $twig = new Environment($loader, ['cache' => self::TWIG_CACHE_PATH]);

            return $twig->render($templateDesc['template'], $parameters);
        }
        return $template;
    }
}
