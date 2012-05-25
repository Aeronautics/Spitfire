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
						'href'  => VIRTUAL_HOST . '/legislativo' . VIRTUAL_EXTENSION
					),
					array(
						'title' => 'Poder Judiciário',
						'href'=> VIRTUAL_HOST . '/judiciario'. VIRTUAL_EXTENSION
					),
					array(
						'title' => 'Poder Executivo',
						'href'=> VIRTUAL_HOST . '/executivo'. VIRTUAL_EXTENSION
					),
					array(
						'title' => 'Estados',
						'href'=> VIRTUAL_HOST . '/estados'. VIRTUAL_EXTENSION
					),
				)
			)
		);
	}
}