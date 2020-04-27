<?php

namespace Lattice\AHP;

class Candidate{

	private $name;
	private $profile;
	private $type = 'alternative';

	public function __construct($array = null){
		if(!is_null($array)){
			foreach ($array as $key => $value) {
				$this->$key = $value;
			}
		}
	}

	public function setName($name):self
	{
		$this->name = $name;

		return $this;
	}

	public function getName(){
		return $this->name;
	}

	public function setType($type):self
	{
		$this->type = $type;

		return $this;
	}

	public function getType(){
		return $this->type;
	}
}