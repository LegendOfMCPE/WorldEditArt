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
use LegendsOfMCPE\WorldEditArt\Epsilon\Manipulation\Changer\BlockTypeFeeder;

class SingleBlockPicker extends BlockPicker{
	/** @var BlockTypeFeeder */
	private $blockType;

	public function __construct(BlockTypeFeeder $blockType){
		$this->blockType = $blockType;
	}

	public function feed() : BlockType{
		return $this->blockType->feed();
	}

	public function getAllTypes() : array{
		return BlockType::getAllTypes($this->blockType);
	}
}
