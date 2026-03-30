<?php

class Utilisateur
{
    public $id;
    public $email;
    public $mot_de_passe;

    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}