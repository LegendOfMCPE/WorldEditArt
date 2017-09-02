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

use LegendsOfMCPE\WorldEditArt\Epsilon\Manipulation\Changer\Picker\RandomLinearBlockPicker;
use LegendsOfMCPE\WorldEditArt\Epsilon\Manipulation\Changer\Picker\RandomWeightedBlockPicker;
use LegendsOfMCPE\WorldEditArt\Epsilon\Manipulation\Changer\Picker\RepeatingLinearBlockPicker;
use LegendsOfMCPE\WorldEditArt\Epsilon\Manipulation\Changer\Picker\RepeatingWeightedBlockPicker;
use LegendsOfMCPE\WorldEditArt\Epsilon\Manipulation\Changer\Picker\SingleBlockPicker;

/**
 * An utility class providing several Generator methods for picking blocks.
 *
 * - Before each *action* of using the Generator: <pre>$generator->throw(new ResetBlockPickerException);</pre>
 * - Within an *action*, use a <code>foreach</code> loop to get as many blocks
 * - To *finalize/invalidate/close* a Generator when it is not used anymore: <pre>$generator->send(false);</pre>
 */
abstract class BlockPicker{
	/**
	 * @param string[] $args
	 * @param bool     $random
	 * @param string   &$error
	 *
	 * @return BlockPicker|null
	 */
	public static function parseArgs(array $args, bool $random, string &$error) : ?BlockPicker{
		/** @var WeightedBlockType[] $types */
		$types = [];
		/** @var WeightedBlockType $currentType */
		$currentType = null;
		$weighted = false;
		foreach($args as $arg){
			if($currentType !== null){
				if(is_numeric($arg)){
					$currentType->weight = (float) $arg;
					if($currentType->weight <= 0){
						$error = "Weight must be positive";
						return null;
					}
					$weighted = true;
					continue;
				}
				$types[] = $currentType;
			}
			$currentType = BlockType::parse($arg, $error, true);
			if($currentType === null){
				return null;
			}
		}
		if($currentType !== null){
			$types[] = $currentType;
		}

		if(count($types) === 0){
			$error = "Please provide at least one block type";
			return null;
		}
		if(count($types) === 1){
			return new SingleBlockPicker($types[0]);
		}
		return $random ? ($weighted ? new RandomWeightedBlockPicker($types) : new RandomLinearBlockPicker($types)) : ($weighted ? new RepeatingWeightedBlockPicker($types) : new RepeatingLinearBlockPicker($types));
	}

	public function reset() : void{
	}

	public abstract function feed() : ?BlockType;
}
