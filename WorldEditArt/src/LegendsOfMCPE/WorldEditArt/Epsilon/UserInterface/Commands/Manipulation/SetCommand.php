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
use LegendsOfMCPE\WorldEditArt\Epsilon\Manipulation\Changer\BlockPicker;
use LegendsOfMCPE\WorldEditArt\Epsilon\Manipulation\Synchronizer\Cassette;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\Session\SessionCommand;
use LegendsOfMCPE\WorldEditArt\Epsilon\WorldEditArt;

class SetCommand extends SessionCommand{
	public function __construct(WorldEditArt $plugin){
		parent::__construct($plugin, "/set", "Sets all blocks in an area", $usage = /** @lang text */
			"//set [s <selectionName>]  [h [padding] [margin]] <blocks>", ["/s"], Consts::PERM_SET, [
			"namedWeighted" => [
				[
					"name" => $usage,
					"type" => "rawtext",
				],
			],
		]);
	}

	public function run(BuilderSession $session, array $args){
		$hollow = false;
		/** @var float|null $padding
		 * @var float|null $margin
		 */
		$padding = $margin = null;
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
		if($arg0 === "h"){
			$hollow = true;
			if(!isset($args[2])){
				$this->sendUsage($session);
				return;
			}
			$padding = (float) $args[1];
			$margin = (float) $args[2];
			$args = array_slice($args, 3);
			goto start_parse;
		}
		if($arg0 === "r"){
			array_shift($args);
			$random = false;
			goto start_parse;
		}

		$picker = BlockPicker::parseArgs($args, $random, $error);
		if($picker === null){
			$session->msg($error, BuilderSession::MSG_CLASS_ERROR);
			$this->sendBlocksUsage($session);
			return;
		}

		$shape = $session->getSelection($selName);
		if($shape === null){
			$session->msg("You don't have a selection called \"$selName\"", BuilderSession::MSG_CLASS_ERROR);
			return;
		}
		if($shape->isComplete()){
			$session->msg("Your \"$selName\" selection is incomplete!", BuilderSession::MSG_CLASS_ERROR);
			return;
		}

		$stream = [$shape, $hollow ? "getHollowStream" : "getSolidStream"];
		$size = $hollow ? $shape->getEstimatedSurfaceSize($padding, $margin) : $shape->getEstimatedSize();
		$chunks = $shape->getChunksInvolved();
		$shouldUseAsync = $size / count($chunks);

		// TODO implement synchronization logic
	}

	private function sendBlocksUsage(BuilderSession $session, int $class = BuilderSession::MSG_CLASS_ERROR){
		/** @noinspection UnnecessaryParenthesesInspection */
		$session->msg((/** @lang text */
			"Usage for <blocks>:\n") .
			"Write all desired block types separated by spaces, e.g.\n" .
			"stone cobblestone wood glowstone glass bedrock\n" .
			"The default weighting (relative probability to get chosen) for each block type is 1.\n" .
			"If you want to specify weighting, write it behind the block type, e.g.\n" .
			"stone 4 glowstone wood 3 glass 2\n" .
			"If you chose the \"r\" (repeating) mode,\n",
			$class, /** @lang text */
			"Usage for <blocks>\n");
	}
}
