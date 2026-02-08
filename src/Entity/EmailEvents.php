<?php
namespace App\Entity;

class EmailEvents
{
    // user
    const USER_INSCRIPTION = 'user:inscription';
    const USER_INSCRIPTION_NOTIFICATION = 'user:inscription:admin';
    const USER_DESINSCRIPTION = 'user:desinscription';
    const USER_RESET_PASSWORD = 'user:reset-password';

    // request
    const REQUEST_CREATION = 'demande:creation';
    const REQUEST_CLOTURATION = 'demande:cloturation';
    const REQUEST_VALIDATION = 'demande:validation';
    const REQUEST_RENVOIE = 'demande:renvoie';
    const REQUEST_ARCHIVAGE = 'demande:archivage';
    const REQUEST_RELANCE = 'demande:relance';

    // user <-> request
    const USER_CREATION_REQUEST_REPLACEMENT = 'user:creation:remplacement';
    const USER_CREATION_REQUEST_INSTALLATION = 'user:creation:installation';

    // response
    const REQUEST_REPONSE_DEMANDEUR = 'demande:renponse:demandeur';
    const REQUEST_REPONSE_COORDONNEE = 'demande:renponse:coordonnee';
    const REQUEST_REPONSE_ADMIN = 'demande:renponse:admin';

    // contact
    const CONTACT_CREATION = 'contact:creation';

    public static function all(): array
    {
        return [
            self::USER_INSCRIPTION,
            self::USER_INSCRIPTION_NOTIFICATION,
            self::USER_DESINSCRIPTION,
            self::USER_RESET_PASSWORD,

            self::REQUEST_CREATION,
            self::REQUEST_CLOTURATION,
            self::REQUEST_VALIDATION,
            self::REQUEST_RENVOIE,
            self::REQUEST_ARCHIVAGE,
            self::REQUEST_RELANCE,

            self::USER_CREATION_REQUEST_REPLACEMENT,
            self::USER_CREATION_REQUEST_INSTALLATION,

            self::REQUEST_REPONSE_DEMANDEUR,
            self::REQUEST_REPONSE_COORDONNEE,
            self::REQUEST_REPONSE_ADMIN,

            self::CONTACT_CREATION,
        ];
    }
}