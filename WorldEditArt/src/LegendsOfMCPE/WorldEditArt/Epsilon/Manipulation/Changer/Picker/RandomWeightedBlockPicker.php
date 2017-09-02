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

class RandomWeightedBlockPicker extends BlockPicker{
	private $sum = 0.0;
	/** @var BlockPicker[] */
	private $types;

	/**
	 * @param BlockType[] $types
	 */
	public function __construct(array $types){
		assert($types !== []);
		foreach($types as $type){
			$this->sum += $type->weight;
			assert($type->weight > 0);
		}
		$this->types = $types;
	}

	public function reset() : void{

	}

	public function feed() : ?BlockType{
		$rand = random_int(0, PHP_INT_MAX - 1);
		$rand *= $this->sum / PHP_INT_MAX;
		foreach($this->types as $type){
			$rand -= $type->weight;
			if($rand < 0){
				return $type;
			}
		}
		throw new \AssertionError("Code logic error");
	}
}
