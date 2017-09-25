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

use LegendsOfMCPE\WorldEditArt\Epsilon\Utils\WEAUtils;
use pocketmine\block\Block;

class BlockChanger{
	/** @var BlockTypeFeeder[] */
	private $fromTypes;
	/** @var BlockPicker */
	private $toTypes;
	/** @var bool */
	private $invert;

	/**
	 * BlockChanger constructor.
	 *
	 * @param BlockPicker       $picker
	 * @param BlockTypeFeeder[] $fromTypes
	 * @param bool              $invert
	 */
	public function __construct(BlockPicker $picker, array $fromTypes, bool $invert){
		$this->fromTypes = $fromTypes;
		assert(WEAUtils::validateArrayType($fromTypes, BlockTypeFeeder::class));
		$this->toTypes = $picker;
		$this->invert = $invert;
	}

	public function reset() : void{
		$this->toTypes->reset();
	}

	public function change(Block $from) : ?BlockType{
		/** @var BlockTypeFeeder $type */
		foreach($this->fromTypes as $type){
			foreach(BlockType::getAllTypes($type) as $blockType){
				$matches = $blockType->matches($from);
				if($matches){
					return $this->invert ? null : // matches, should not replace
						$this->toTypes->feed(); // matches, should replace
				}
			}
		}
		return $this->invert ? $this->toTypes->feed() : // no matches, should replace
			null; // no matches, should not replace
	}
}
