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

use pocketmine\block\Block;

class BlockChanger{
	/** @var BlockType[] */
	private $fromTypes;
	/** @var BlockPicker */
	private $toTypes;

	/**
	 * BlockChanger constructor.
	 *
	 * @param BlockPicker $picker
	 * @param BlockType[] $fromTypes
	 */
	public function __construct(BlockPicker $picker, array $fromTypes){
		$this->fromTypes = $fromTypes;
		$this->toTypes = $picker;
	}

	public function reset() : void{
		$this->toTypes->reset();
	}

	public function change(Block $from) : ?BlockType{
		if(count($this->fromTypes) === 0){
			return $this->toTypes->feed();
		}
		foreach($this->fromTypes as $type){
			if($type->matches($from)){
				return $this->toTypes->feed();
			}
		}
		return null;
	}
}
