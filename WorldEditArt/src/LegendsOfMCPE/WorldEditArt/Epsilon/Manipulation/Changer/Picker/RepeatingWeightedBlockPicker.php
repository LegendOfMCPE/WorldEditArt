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

namespace LegendsOfMCPE\WorldEditArt\Epsilon\Manipulation\Changer\Picker;

use LegendsOfMCPE\WorldEditArt\Epsilon\Manipulation\Changer\BlockPicker;
use LegendsOfMCPE\WorldEditArt\Epsilon\Manipulation\Changer\BlockType;

class RepeatingWeightedBlockPicker extends BlockPicker{
	/** @var BlockType[] */
	private $types;
	/** @var int */
	private $i;

	/**
	 * @param BlockType[] $types
	 */
	public function __construct(array $types){
		$this->types = $types;
	}

	public function reset() : void{
		reset($this->types);
		$this->i = 0;
	}

	public function feed() : ?BlockType{
		$ret = null;

		$type = current($this->types);

		while(true){
			if($this->i++ < $type->weight){
				return $type;
			}

			$this->i = 0;
			$type = next($this->types);
			if($type === false){
				$type = reset($this->types);
			}
		}

		throw new \AssertionError("Code logic error");
	}
}
