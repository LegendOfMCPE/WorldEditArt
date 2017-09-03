<?php

/*
 *
 * WorldEditArt
 *
 * Copyright (C) 2017 SOFe
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 */

declare(strict_types=1);

namespace LegendsOfMCPE\WorldEditArt\Epsilon\Manipulation;

use pocketmine\math\Vector3;

class SerializableBlockStreamGetter implements \Serializable{
	private $callable;
	/** @var string (serialized array) */
	private $args;

	public function __construct(callable $callable, array $args){
		$this->callable = serialize($callable);
		$this->args = serialize($args);
	}

	/** @noinspection PhpInconsistentReturnPointsInspection
	 * @param Vector3 $vector
	 *
	 * @return \Generator
	 */
	public function getValue(Vector3 $vector) : \Generator{
		$c = unserialize($this->callable);
		return $c($vector, ...unserialize($this->args));
	}

	/**
	 * String representation of object
	 *
	 * @link  http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 * @since 5.1.0
	 */
	public function serialize() : string{
		return serialize([$this->callable, $this->args]);
	}

	/**
	 * Constructs the object
	 *
	 * @link  http://php.net/manual/en/serializable.unserialize.php
	 *
	 * @param string $serialized <p>
	 *                           The string representation of the object.
	 *                           </p>
	 *
	 * @return void
	 * @since 5.1.0
	 */
	public function unserialize($serialized) : void{
		[$this->callable, $this->args] = unserialize($serialized, false);
		$callable = unserialize($this->callable, true);
		$args = unserialize($this->args, true);
		// We are accepting all classes here because $serialized should not reasonably contain invalid data unless from other badly written plugins
	}
}
