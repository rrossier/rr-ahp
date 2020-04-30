<?php

namespace Lattice\AHP;

class Candidate extends Node{

	private $profile;

	public function __construct($array = null){
		if(!is_null($array)){
			foreach ($array as $key => $value) {
				$this->$key = $value;
			}
		}
		$this->type = 'alternative';
	}

	public function setProfile($profile):self
	{
		$this->profile = $profile;

		return $this;
	}

	public function getProfile()
	{
		return $this->profile;
	}
}