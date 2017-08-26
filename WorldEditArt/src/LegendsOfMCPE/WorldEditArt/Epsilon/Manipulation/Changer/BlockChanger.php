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
use pocketmine\math\Vector3;

class BlockChanger{
	/** @var BlockType[] */
	private $fromTypes;
	/** @var \Generator */
	private $toTypes;

	public function __construct(array $toTypes, Vector3 $center, BlockType ...$fromTypes){
		$this->fromTypes = $fromTypes;
		$c = $toTypes[0];
		$this->toTypes = $c($toTypes[1]);
	}

	public function change(Block $from){
		foreach($this->fromTypes as $type){
			if($type->matches($from)){
				$current = $this->toTypes->current();
				$this->toTypes->next();
				return $current;
			}
		}
		return null;
	}
}
