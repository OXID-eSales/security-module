<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

$sLangName = 'Deutsch';

$aLang = [
    'charset' => 'UTF-8',

    'ERROR_PASSWORD_MIN_LENGTH'                => 'Das Passwort muss mindestens %d Zeichen lang sein.',
    'ERROR_PASSWORD_MISSING_DIGIT'             => 'Das Passwort enthält keine Ziffer.',
    'ERROR_PASSWORD_MISSING_LOWER_CASE'        => 'Das Passwort enthält keine Kleinbuchstaben.',
    'ERROR_PASSWORD_MISSING_UPPER_CASE'        => 'Das Passwort enthält keine Großbuchstaben.',
    'ERROR_PASSWORD_MISSING_SPECIAL_CHARACTER' => 'Das Passwort enthält keine Sonderzeichen.',

    'CAPTCHA_INPUT_LABEL'                      => 'Geben Sie den Text des Bildes ein',
    'CAPTCHA_IMAGE_ALT_TEXT'                   => 'Bild mit CAPTCHA-Code',
    'CAPTCHA_RELOAD'                           => 'Aktualisiere Captcha',
    'CAPTCHA_PLAY_AUDIO'                       => 'Captcha-Audio abspielen',
    'ERROR_EXPIRED_CAPTCHA'                    => 'CAPTCHA ist abgelaufen.',
    'ERROR_INVALID_CAPTCHA'                    => 'CAPTCHA Texteingabe ist nicht korrekt.',
    'ERROR_EMPTY_CAPTCHA'                      => 'CAPTCHA Eingabefeld bitte mit dem Bildtext füllen.',
    'FORM_VALIDATION_FAILED'                   => 'Unerwarteter Fehler aufgetreten. Bitte aktualisieren Sie die Seite und versuchen Sie es erneut.',

    'PASSWORD_REQUIREMENTS'              => 'Passwort-Anforderungen',
    'PASSWORD_MIN_LENGTH'                => 'Mindestens %d Zeichen.',
    'PASSWORD_CONTAIN_DIGIT'             => 'Mindestens eine Zahl.',
    'PASSWORD_CONTAIN_LOWER_CASE'        => 'Mindestens einen Kleinbuchstaben.',
    'PASSWORD_CONTAIN_UPPER_CASE'        => 'Mindestens einen Großbuchstaben.',
    'PASSWORD_CONTAIN_SPECIAL_CHARACTER' => 'Mindestens ein Sonderzeichen.',

    'ERROR_PASSWORD_STRENGTH_0' => "Sehr schwach",
    'ERROR_PASSWORD_STRENGTH_1' => "Schwach",
    'ERROR_PASSWORD_STRENGTH_2' => "Medium",
    'ERROR_PASSWORD_STRENGTH_3' => "Stark",
    'ERROR_PASSWORD_STRENGTH_4' => "Sehr Stark",

    'GENERATE_STRONG_PASSWORD' => 'Starkes Passwort generieren',

    'SIGN_IN_PROVIDER' => 'Anmelden mit %s',

    'OTP_EMAIL_SUBJECT' => 'Ihr OXID eShop Verifizierungscode',
    'OTP_EMAIL_BODY'    => "Hallo,\n\n"
        . "jemand (hoffentlich Sie) versucht, sich in Ihrem OXID eShop-Konto anzumelden.\n\n"
        . "Ihr Verifizierungscode lautet: %s\n\n"
        . "Dieser Code läuft in %d Minuten ab und kann nur einmal verwendet werden.\n\n"
        . "Falls Sie sich nicht anmelden wollten, können Sie diese E-Mail ignorieren – zur Sicherheit"
        . " empfehlen wir jedoch, Ihr Passwort zu ändern.\n\n"
        . "Ihr OXID eShop-Team.",

    'TWO_FACTOR_AUTHENTICATION_TITLE'       => 'Zwei-Faktor-Authentifizierung',
    'TWO_FACTOR_AUTHENTICATION_DESCRIPTION' => 'Ein Code wurde an Ihre E-Mail-Adresse gesendet. Bitte geben Sie ihn unten ein, um fortzufahren.',

    'RESEND_CODE'           => 'Code erneut senden',
    'RESEND_CODE_SENDING'   => 'Wird gesendet…',
    'RESEND_CODE_ERROR'     => 'Code konnte nicht erneut gesendet werden.',
    'RESEND_CODE_COUNTDOWN' => 'Erneut senden in %ds',

    'ERROR_INVALID_CODE'           => 'Der Bestätigungscode ist ungültig. Bitte versuchen Sie es erneut.',
    'ERROR_CODE_TIME_EXPIRED'      => 'Der Bestätigungscode ist abgelaufen. Bitte fordern Sie einen neuen Code an.',
    'ERROR_ATTEMPT_LIMIT_EXCEEDED' => 'Zu viele fehlgeschlagene Versuche. Bitte melden Sie sich erneut an.',
    'ERROR_SESSION_EXPIRED'        => 'Ihre Sitzung ist abgelaufen. Bitte melden Sie sich erneut an.',

    'OE_SECURITY_REMAINING_ATTEMPTS' => 'Verbleibende Versuche',
    'OE_SECURITY_ENTER_CODE'         => 'Bestätigungscode',
    'OE_SECURITY_LOG_IN_AGAIN'       => 'Erneut anmelden',

    'OE_SECURITY_EXTERNAL_AUTH_PASSWORD_INFO' => 'Sie sind mit einem externen Anbieter angemeldet. Die Passwortverwaltung ist für dieses Konto nicht verfügbar.',
    'OE_SECURITY_RESET_PASSWORD'              => 'Passwort zurücksetzen',

    'OE_SECURITY_SECURITY_TITLE'                  => 'Sicherheit',
    'OE_SECURITY_TWO_FACTOR_SETTINGS_TITLE'       => 'Zwei-Faktor-Authentifizierung',
    'OE_SECURITY_TWO_FACTOR_SETTINGS_DESCRIPTION' => 'Fügen Sie Ihrem Konto eine zusätzliche Sicherheitsebene hinzu. Wenn aktiviert, müssen Sie bei jeder Anmeldung einen Bestätigungscode eingeben, der an Ihre E-Mail gesendet wird.',
    'OE_SECURITY_TWO_FACTOR_ENABLE'               => 'Zwei-Faktor-Authentifizierung aktivieren',
    'OE_SECURITY_TWO_FA_SETTINGS_SAVED'           => 'Die Einstellungen für die Zwei-Faktor-Authentifizierung wurden gespeichert.',

    'ERROR_EMAIL_NOT_VERIFIED'                 => 'Bitte bestätigen Sie zuerst Ihre E-Mail-Adresse. Wir haben Ihnen dazu einen Link gesendet.',
    'ERROR_EMAIL_ALREADY_VERIFIED'             => 'Ihre E-Mail-Adresse ist bereits bestätigt. Sie können sich anmelden.',
    'ERROR_EMAIL_VERIFICATION_RESEND_COOLDOWN' => 'Eine neue E-Mail kann in %d Minute(n) angefordert werden.',
    'ERROR_EMAIL_VERIFICATION_LINK_EXPIRED'    => 'Der Bestätigungslink ist abgelaufen.',
    'ERROR_EMAIL_VERIFICATION_LINK_INVALID'    => 'Der Bestätigungslink ist ungültig oder wurde bereits verwendet.',

    'ERROR_EMAIL_VERIFICATION_MAIL_NOT_SENT'   => 'Die Bestätigungs-E-Mail konnte nicht gesendet werden. Bitte versuchen Sie es später erneut.',

    'EMAIL_VERIFICATION_DATE_FORMAT'   => 'd.m.Y H:i',
    'EMAIL_VERIFICATION_EMAIL_BUTTON'  => 'E-Mail-Adresse bestätigen',
    'EMAIL_VERIFICATION_EMAIL_SUBJECT' => 'Bitte bestätigen Sie Ihre E-Mail-Adresse',
    'EMAIL_VERIFICATION_EMAIL_BODY'    => "Hallo %1\$s,\n\n"
        . "vielen Dank für Ihre Registrierung bei %2\$s.\n\n"
        . "Bitte bestätigen Sie Ihre E-Mail-Adresse, indem Sie den folgenden Link öffnen:\n\n"
        . "%3\$s\n\n"
        . "Der Link ist gültig bis %4\$s und kann nur einmal verwendet werden. Erst nach der Bestätigung"
        . " können Sie sich in Ihrem Kundenkonto anmelden.\n\n"
        . "Falls Sie sich nicht registriert haben, können Sie diese E-Mail ignorieren.\n\n"
        . "Ihr %2\$s-Team.",

    'OE_SECURITY_EMAIL_VERIFICATION_TITLE'            => 'E-Mail-Adresse bestätigen',
    'OE_SECURITY_EMAIL_VERIFICATION_SENT'             => 'Wir haben Ihnen eine E-Mail mit einem Bestätigungslink gesendet. Bitte öffnen Sie ihn, um Ihr Konto zu aktivieren; der Link ist %d Stunden gültig.',
    'OE_SECURITY_EMAIL_VERIFICATION_PENDING'          => 'Ihre E-Mail-Adresse ist noch nicht bestätigt. Bitte öffnen Sie den Link in der E-Mail, die wir Ihnen gesendet haben. Danach können Sie sich anmelden.',
    'OE_SECURITY_EMAIL_VERIFICATION_VERIFIED'         => 'Vielen Dank! Ihre E-Mail-Adresse ist bestätigt und Sie können sich jetzt anmelden.',
    'OE_SECURITY_EMAIL_VERIFICATION_ALREADY_VERIFIED' => 'Ihre E-Mail-Adresse ist bereits bestätigt. Sie können sich anmelden.',
    'OE_SECURITY_EMAIL_VERIFICATION_EXPIRED'          => 'Dieser Bestätigungslink ist abgelaufen. Fordern Sie unten eine neue E-Mail an; der alte Link wird damit ungültig.',
    'OE_SECURITY_EMAIL_VERIFICATION_INVALID'          => 'Dieser Bestätigungslink ist ungültig oder wurde bereits verwendet.',
    'OE_SECURITY_EMAIL_VERIFICATION_RESEND'           => 'Neue Bestätigungs-E-Mail senden',
    'OE_SECURITY_EMAIL_VERIFICATION_TO_LOGIN'         => 'Anmelden',
];
