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

	public static function yieldSingle(BlockType $type) : Generator{
		do{
			try{
				$continue = yield $type->toBlock();
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
				$continue = yield $types[array_rand($types)]->toBlock();
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
					yield $types[0]->toBlock();
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
					$continue = yield $type->toBlock();
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
						$continue = yield $type->toBlock();
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
