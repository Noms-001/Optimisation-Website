<?php

class Media
{
    public $id;
    public $src;
    public $alt;
    public $article; // article id ou object

    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}