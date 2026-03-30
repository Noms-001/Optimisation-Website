<?php

class Util
{
    public static function slugify($text)
    {
        // Convertir en minuscule
        $text = strtolower($text);

        // Remplacer les accents
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);

        // Sécuriser si iconv retourne false
        if ($text === false) {
            return '';
        }

        // Supprimer caractères non alphanumériques
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);

        // Supprimer tirets multiples
        $text = trim($text, '-');

        return $text;
    }
}
