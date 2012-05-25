<?php

namespace Aeronautics\Spitfire\Controllers;

class LegislativoCollection extends AbstractController
{
	public function get() 
	{
		$esferas = $this->mapper->esfera(array("limite"=>"Uniao"))->fetchAll();
		foreach ($esferas as &$esfera) 
			$esfera->links = array(array(
				'title' => $esfera->nome,
				'href'  => VIRTUAL_HOST . '/legislativo/'.strtoupper($esfera->sigla) . VIRTUAL_EXTENSION
			));
		return array('esferas' => $esferas);
	}
}