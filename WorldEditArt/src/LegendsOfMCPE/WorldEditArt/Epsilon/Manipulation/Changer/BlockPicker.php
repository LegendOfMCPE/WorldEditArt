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

use Generator;

/**
 * An utility class providing several Generator methods for picking blocks.
 *
 * - Before each *action* of using the Generator: <pre>$generator->throw(new ResetBlockPickerException);</pre>
 * - Within an *action*, use a <code>foreach</code> loop to get as many blocks
 * - To *finalize/invalidate/close* a Generator when it is not used anymore: <pre>$generator->send(false);</pre>
 */
final class BlockPicker{
	private function __construct(){
	}

	/**
	 * @param array  $args
	 * @param bool   $random
	 * @param string &$error
	 *
	 * @return array|null
	 */
	public static function parseArgs(array $args, bool $random, string &$error){
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
			return [[BlockPicker::class, "yieldSingle"], [$types[0]]];
		}
		$func = $random ?
			($weighted ? "yieldRandomWeighted" : "yieldRandomLinear") :
			($weighted ? "yieldRepeatingWeighted" : "yieldRepeatingLinear");
		return [[BlockPicker::class, $func], $types];
	}

	public static function yieldSingle(BlockType $type) : Generator{
		do{
			try{
				$continue = yield $type;
				if($continue === false){
					return;
				}
			}catch(ResetBlockPickerException $e){
			}
		}while(true);
	}

	public static function yieldRandomLinear(BlockType ...$types) : Generator{
		do{
			try{
				$continue = yield $types[array_rand($types)];
				if($continue === false){
					return;
				}
			}catch(ResetBlockPickerException $e){
			}
		}while(true);
	}

	public static function yieldRandomWeighted(WeightedBlockType ...$types) : Generator{
		assert($types !== []);
		$sum = 0.0;
		foreach($types as $type){
			$sum += $type->weight;
			assert($type->weight > 0);
		}
		do{
			$rand = random_int(0, PHP_EOL);
			try{
				if($rand === PHP_EOL){
					yield $types[0];
					continue;
				}
				$rand *= $sum / PHP_EOL;
				foreach($types as $type){
					$rand -= $type->weight;
					if($rand < 0){
						$continue = yield $type;
						if(!$continue){
							return;
						}

						break;
					}
				}
			}catch(ResetBlockPickerException $e){
			}
		}while(true);
	}

	public static function yieldRepeatingLinear(BlockType ...$types) : Generator{
		do{
			try{
				foreach($types as $type){
					$continue = yield $type;
					if($continue === false){
						return;
					}
				}
			}catch(ResetBlockPickerException $e){
			}
		}while(true);
	}

	public static function yieldRepeatingWeighted(WeightedBlockType ...$types) : Generator{
		do{
			try{
				foreach($types as $type){
					for($i = 0; $i < $type->weight; ++$i){
						$continue = yield $type;
						if($continue === false){
							return;
						}
					}
				}
			}catch(ResetBlockPickerException $e){
			}
		}while(true);
	}
}
