<?php

namespace Aeronautics\Spitfire\Controllers\Legislativo;

use Aeronautics\Spitfire\Controllers\AbstractController;

class PartidosCollection extends AbstractController
{
    public function get()
    {
        $partidos = $this->mapper->partido()->fetchAll();
        foreach ($partidos as &$partido)
            $partido->links = array(array(
                'title' => $partido->nome,
                'href'  => VIRTUAL_HOST . '/legislativo/TSE/partidos/'.strtoupper($partido->sigla) . VIRTUAL_EXTENSION
            ));

        return array('partidos' => $partidos);
    }
}
