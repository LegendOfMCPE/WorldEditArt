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

use pocketmine\block\Block;

class BlockType{
	const DAMAGE_ANY = -1;

	/** @var int */
	public $blockId;
	/** @var int */
	public $blockDamage;

	/** @var Block */
	private $blockCache;

	public function __construct(int $blockId, int $blockDamage){
		$this->blockId = $blockId;
		$this->blockDamage = $blockDamage;
	}

	public function toBlock() : Block{
		return $this->blockCache ?? ($this->blockCache = Block::get($this->blockId, $this->blockDamage));
	}
}
