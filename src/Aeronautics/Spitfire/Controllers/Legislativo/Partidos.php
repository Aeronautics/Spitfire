<?php

namespace Aeronautics\Spitfire\Controllers\Legislativo;

use Aeronautics\Spitfire\Controllers\AbstractController;

class Partidos extends AbstractController
{
    public function get($legislativoSigla, $sigla)
    {
        $partido = $this->mapper->partido(array("sigla" => $sigla))->fetch();
        $partido->links = array(
            array(
                'title' => 'Políticos do '.$sigla,
                'href'  => VIRTUAL_HOST . "/legislativo/$legislativoSigla/partidos/$sigla/politicos" . VIRTUAL_EXTENSION
            )
        );

        return array('partido' => $partido);
    }
}
