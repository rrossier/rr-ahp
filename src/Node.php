<?php

namespace Lattice\AHP;

class Node
{

	private $name;
	private $value;

	public function __construct($name, $value)
	{
		$this->name = $name;
		$this->value = $value;
	}

	public function setName($name):self
	{
		$this->name = $name;

		return $this;
	}

	public function getName()
	{
		return $this->name;
	}

	public function setValue($value):self
	{
		$this->value = $value;

		return $this;
	}

	public function getValue()
	{
		return $this->value;
	}
}