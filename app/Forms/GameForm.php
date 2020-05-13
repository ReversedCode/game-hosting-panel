<?php

namespace App\Forms;

use Kris\LaravelFormBuilder\Form;

class GameForm extends Form
{
    public function buildForm()
    {
        $this->add('cover', 'text', $this->params('Image cover used for the game'));
    }

    protected function params($text)
    {
        return [
            'help_block' => [
                'text' => $text,
                'tag'  => 'small',
                'attr' => ['class' => 'form-text text-muted'],
            ],
        ];
    }
}