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

namespace LegendsOfMCPE\WorldEditArt\Epsilon\Manipulation\Changer;

class WeightedBlockType extends BlockType{
	/** @var float */
	public $weight;

	public function __construct(int $blockId, int $blockDamage, float $weight){
		parent::__construct($blockId, $blockDamage);
		$this->weight = $weight;
	}
}
