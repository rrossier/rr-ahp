<?php

namespace Lattice\AHP;

abstract class Node{

	protected $name;
	protected $type;

	public function setName($name):self
	{
		$this->name = $name;

		return $this;
	}

	public function getName()
	{
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