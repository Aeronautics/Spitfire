<?php

namespace Aeronautics\Spitfire\Controllers;

class Legislativo extends AbstractController
{
    public function get($sigla)
    {
        $esfera = $this->mapper->esfera(array("sigla" => $sigla))->fetch();
        switch ($sigla) {
            case 'TSE':
                $esfera->links = array(
                    array(
                        'title' => 'Partidos',
                        'href'  => VIRTUAL_HOST . '/legislativo/TSE/partidos' . VIRTUAL_EXTENSION
                    )
                );
                break;
        }

        return array('esfera' => $esfera);
    }
}
