<?php

class Auteur
{
    public $id;
    public $nom;
    public $email;
    public $role;

    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}