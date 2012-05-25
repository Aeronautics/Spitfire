<?php

namespace Aeronautics\Spitfire\Controllers\Legislativo\Partidos;

use Aeronautics\Spitfire\Controllers\AbstractController;

class PoliticosCollection extends AbstractController
{
	public function get($legislativoSigla, $sigla) 
	{
		$politicos = $this->mapper
		                  ->politico
		                  ->politico_partido
		                  ->partido(array('sigla' => $sigla))
		                  ->fetchAll();
		return array('politicos' => $politicos);
	}
}