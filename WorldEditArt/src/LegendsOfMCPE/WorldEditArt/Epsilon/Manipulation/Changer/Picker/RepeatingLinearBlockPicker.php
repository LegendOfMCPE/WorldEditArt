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

class RepeatingLinearBlockPicker extends BlockPicker{
	/** @var BlockType[] */
	private $types;

	/**
	 * @param BlockType[] $types
	 */
	public function __construct(array $types){
		$this->types = $types;
	}

	public function reset() : void{
		reset($this->types);
	}

	public function feed() : ?BlockType{
		$ret = current($this->types);
		if(next($this->types) === false){
			reset($this->types);
		}
		return $ret;
	}
}
