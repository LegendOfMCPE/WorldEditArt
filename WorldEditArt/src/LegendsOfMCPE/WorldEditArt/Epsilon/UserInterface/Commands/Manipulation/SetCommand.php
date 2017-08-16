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

namespace LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\Manipulation;

use LegendsOfMCPE\WorldEditArt\Epsilon\BuilderSession;
use LegendsOfMCPE\WorldEditArt\Epsilon\Consts;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\Session\SessionCommand;
use LegendsOfMCPE\WorldEditArt\Epsilon\WorldEditArt;
use pocketmine\item\Item;

class SetCommand extends SessionCommand{
	public function __construct(WorldEditArt $plugin){
		parent::__construct($plugin, "/set", "Sets all blocks in an area", /** @lang text */
			"//set [s <selectionName>]  [hollow? true|false] <blocks>", ["/s"], Consts::PERM_SET, [
				"unnamedWeighted" => [
					[
						"name" => "hollow",
						"type" => "bool",
						"optional" => true,
					],
					[
						"name" => "repeating",
						"type" => "bool",
						"optional" => true,
					],
					[
						"name" => "block1",
						"type" => "stringenum",
						"enum_type" => "blockType",
					],
					[
						"name" => "weight1",
						"type" => "float",
						"optional" => true,
					],
					[
						"name" => "block2",
						"type" => "stringenum",
						"enum_type" => "blockType",
						"optional" => true,
					],
					[
						"name" => "weight2",
						"type" => "float",
						"optional" => true,
					],
					[
						"name" => "block3",
						"type" => "stringenum",
						"enum_type" => "blockType",
						"optional" => true,
					],
					[
						"name" => "weight3",
						"type" => "float",
						"optional" => true,
					],
					[
						"name" => "...",
						"type" => "stringenum",
						"enum_type" => "blockType",
						"optional" => true,
					],
					[
						"name" => "....",
						"type" => "float",
						"optional" => true,
					],
				],
				"namedWeighted" => [
					[
						"name" => "s",
						"type" => "stringenum",
						"enum_values" => ["s"],
					],
					[
						"name" => "repeating",
						"type" => "bool",
						"optional" => true,
					],
					[
						"name" => "selectionName",
						"type" => "string",
					],
					[
						"name" => "hollow",
						"type" => "bool",
						"optional" => true,
					],
					[
						"name" => "block1",
						"type" => "stringenum",
						"enum_type" => "blockType",
					],
					[
						"name" => "weight1",
						"type" => "float",
						"optional" => true,
					],
					[
						"name" => "block2",
						"type" => "stringenum",
						"enum_type" => "blockType",
						"optional" => true,
					],
					[
						"name" => "weight2",
						"type" => "float",
						"optional" => true,
					],
					[
						"name" => "block3",
						"type" => "stringenum",
						"enum_type" => "blockType",
						"optional" => true,
					],
					[
						"name" => "weight3",
						"type" => "float",
						"optional" => true,
					],
					[
						"name" => "...",
						"type" => "stringenum",
						"enum_type" => "blockType",
						"optional" => true,
					],
					[
						"name" => "....",
						"type" => "float",
						"optional" => true,
					],
				],
				"unnamedUnweighted" => [
					[
						"name" => "hollow",
						"type" => "bool",
						"optional" => true,
					],
					[
						"name" => "repeating",
						"type" => "bool",
						"optional" => true,
					],
					[
						"name" => "block1",
						"type" => "stringenum",
						"enum_type" => "blockType",
					],
					[
						"name" => "block2",
						"type" => "stringenum",
						"enum_type" => "blockType",
						"optional" => true,
					],
					[
						"name" => "block3",
						"type" => "stringenum",
						"enum_type" => "blockType",
						"optional" => true,
					],
					[
						"name" => "...",
						"type" => "stringenum",
						"enum_type" => "blockType",
						"optional" => true,
					],
				],
				"namedUnweighted" => [
					[
						"name" => "s",
						"type" => "stringenum",
						"enum_values" => ["s"],
					],
					[
						"name" => "repeating",
						"type" => "bool",
						"optional" => true,
					],
					[
						"name" => "selectionName",
						"type" => "string",
					],
					[
						"name" => "hollow",
						"type" => "bool",
						"optional" => true,
					],
					[
						"name" => "block1",
						"type" => "stringenum",
						"enum_type" => "blockType",
					],
					[
						"name" => "block2",
						"type" => "stringenum",
						"enum_type" => "blockType",
						"optional" => true,
					],
					[
						"name" => "block3",
						"type" => "stringenum",
						"enum_type" => "blockType",
						"optional" => true,
					],
					[
						"name" => "...",
						"type" => "stringenum",
						"enum_type" => "blockType",
						"optional" => true,
					],
				],
			]);
	}

	public function run(BuilderSession $session, array $args){
		$hollow = false;
		$random = true;
		$selName = $session->getDefaultSelectionName();
		start_parse:
		if(!isset($args[0])){
			$this->sendUsage($session);
			return;
		}
		$arg0 = mb_strtolower($args[0]);
		if($arg0 === "s"){
			if(!isset($args[1])){
				$this->sendUsage($session);
				return;
			}
			$selName = $args[1];
			$args = array_slice($args, 2);
			goto start_parse;
		}
		if($arg0 === "true"){
			array_shift($args);
			$hollow = true;
			goto start_parse;
		}
		if($arg0 === "false"){
			array_shift($args);
			goto start_parse;
		}
		if($arg0 === "r"){
			array_shift($args);
			$random = false;
			goto start_parse;
		}

		$this->parseBlockList($args, $random);
	}

	private function parseBlockList(array $args, bool $random) : \Generator{
		$types = [];
		$lastBlock = null;
		foreach($args as $arg){
			if($lastBlock === null){
				$blockName = mb_strtolower(str_replace([" ", "minecraft:"], ["_", ""], trim($arg)));
				// TODO parsing
			}
		}
	}

	private function sendBlocksUsage(BuilderSession $session, int $class = BuilderSession::MSG_CLASS_ERROR){
		$session->msg("Write all desired block types separated by spaces, e.g.\n" .
			"stone cobblestone wood glowstone glass bedrock\n" .
			"The default weighting (relative probability to get chosen) for each block type is 1." .
			"If you want to specify weighting, write it behind the block type, e.g." .
			"stone 4 glowstone wood 3 glass 2" .
			"If you chose the \"r\" (repeating) mode, ",
			$class, "Usage for <blocks>");
	}
}
