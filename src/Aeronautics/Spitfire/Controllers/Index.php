<?php

namespace Aeronautics\Spitfire\Controllers;

class Index extends AbstractController
{
    public function get()
    {
        return array(
            'poderes' => array(
                'title' => 'Dados da União',
                'links' => array(
                    array(
                        'title' => 'Poder Legislativo',
                        'href'  => VIRTUAL_HOST . '/legislativo/partidos' . VIRTUAL_EXTENSION
                    )
                )
            )
        );
    }
}
