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
use pocketmine\block\BlockIds;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;

class BlockType{
	const DAMAGE_ANY = -1;
	const DAMAGE_ROTATE_TOWARDS_CENTER = -2;
	const DAMAGE_ROTATE_AWAY_FROM_CENTER = -2;

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

	public function toBlock(Vector3 $hereToCenter) : Block{
		// TODO rotation support
		if($this->blockDamage === BlockType::DAMAGE_ANY){
			return Block::get($this->blockId, random_int(0, 15));
		}
		return $this->blockCache ?? ($this->blockCache = Block::get($this->blockId, $this->blockDamage));
	}

	public function matches(Block $block) : bool{
		return $block->getId() === $this->blockId && ($this->blockDamage === BlockType::DAMAGE_ANY || $this->blockDamage === $block->getId());
	}

	/**
	 * @param string $string
	 * @param string &$error
	 *
	 * @param bool   $weighted
	 *
	 * @return BlockType|null
	 */
	public static function parse(string $string, string &$error, bool $weighted = false){
		$parts = explode(":", str_replace([" ", "minecraft:"], ["_", ""], trim($string)), 2);

		$instance = $weighted ? new WeightedBlockType(0, 0, 1.0) : new BlockType(0, 0);

		if(strtoupper($parts[0]) === "AIR"){
			$instance->blockId = BlockIds::AIR;
		}elseif(defined(ItemIds::class . "::" . strtoupper($parts[0]))){
			$itemId = constant(ItemIds::class . "::" . strtoupper($parts[0]));
			$instance->blockId = $itemId < 256 ? $itemId : Item::get($itemId)->getBlock()->getId();
			if($instance->blockId === 0){
				$error = "$parts[0] cannot be converted into a block";
				return null;
			}
		}else{
			$error = "$parts[0] is an unknown item name";
			return null;
		}

		if(isset($parts[1])){
			$parts[1] = mb_strtoupper($parts[1]);
			if($parts[1] === "*" || $parts[1] === "RANDOM" || $parts[1] === "ANY"){
				$instance->blockDamage = BlockType::DAMAGE_ANY;
			}elseif($parts[1] === "IN" || $parts[1] === "INTO" || $parts[1] === "INWARD" || $parts[1] === "INWARDS" || $parts[1] === "TOWARD" || $parts[1] === "TOWARDS"){
				$instance->blockDamage = BlockType::DAMAGE_ROTATE_TOWARDS_CENTER;
			}elseif($parts[1] === "OUT" || $parts[1] === "OUTWARD" || $parts[1] === "OUTWARDS" || $parts[1] === "AWAY"){
				$instance->blockDamage = BlockType::DAMAGE_ROTATE_AWAY_FROM_CENTER;
			}elseif(is_numeric($parts[1])){
				$instance->blockDamage = ((int) $parts[1]) & 15;
			}else{
				$class = Block::$list[$instance->blockId];
				if(defined($class . "::" . strtoupper($parts[1]))){
					$instance->blockDamage = constant($class . "::" . strtoupper($parts[1]));
				}else{
					// TODO rotation support
					return null;
				}
			}
		}
		return $instance;
	}
}
