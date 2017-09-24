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
use LegendsOfMCPE\WorldEditArt\Epsilon\Manipulation\Changer\BlockChanger;
use LegendsOfMCPE\WorldEditArt\Epsilon\Manipulation\Changer\BlockPicker;
use LegendsOfMCPE\WorldEditArt\Epsilon\Manipulation\Changer\BlockType;
use LegendsOfMCPE\WorldEditArt\Epsilon\Manipulation\Changer\BlockTypeFeeder;
use LegendsOfMCPE\WorldEditArt\Epsilon\Manipulation\Changer\Picker\SingleBlockPicker;
use LegendsOfMCPE\WorldEditArt\Epsilon\Manipulation\SerializableBlockStreamGetter;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\Session\SessionCommand;
use LegendsOfMCPE\WorldEditArt\Epsilon\WorldEditArt;

class ReplaceCommand extends SessionCommand{
	public function __construct(WorldEditArt $plugin){
		parent::__construct($plugin, "/replace", "Replace some blocks in your selection to other blocks", /** @lang text */
			"//rep [s <selectionName>] [h[=<padding>[,<margin>]]] [r] <from blocks> [to] <to blocks>", ["/rep"], Consts::PERM_REPLACE);
	}

	/**
	 * @param BuilderSession $session
	 * @param array          $args
	 *
	 * @return void
	 */
	public function run(BuilderSession $session, array $args) : void{
		if(count($args) === 0){
			$this->sendUsage($session, BuilderSession::MSG_CLASS_INFO);
			return;
		}
		$selName = $session->getDefaultSelectionName();
		$random = true;
		$hollowConfig = null;
		while(true){
			$i = mb_strtolower($args[0]);
			if($i === "s"){
				array_shift($args);
				$selName = array_shift($args);
				continue;
			}

			if($i === "h"){
				$hollowConfig = [1.0, 0.0];
				array_shift($args);
				continue;
			}
			if(strpos($i, "h=") === 0 || strpos($i, "h:") === 0){
				$dimens = explode(",", substr($i, 2), 2);
				$padding = (float) $dimens[0];
				$margin = (float) ($dimens[1] ?? 0.0);
				$hollowConfig = [$padding, $margin];
				array_shift($args);
				continue;
			}

			if($i === "r"){
				array_shift($args);
				$random = false;
				continue;
			}

			break;
		}

		if(count($args) < 2){
			$this->sendUsage($session);
			return;
		}

		$error = null;
		$presets = $this->getPlugin()->getPresetManager();
		/** @var BlockTypeFeeder[] $fromTypes */
		/** @var BlockPicker[] $toTypes */
		if(count($args) === 2){
			$fromTypes = [BlockType::parse($presets, $args[0], $error)];
			if($fromTypes[0] === null){
				$session->msg("Error parsing <from type>: $error", BuilderSession::MSG_CLASS_ERROR);
				return;
			}
			$toType = BlockType::parse($presets, $args[1], $error);
			if($toType === null){
				$session->msg("Error parsing <to type>: $error", BuilderSession::MSG_CLASS_ERROR);
				return;
			}
			$toTypes = new SingleBlockPicker($toType);
		}else{
			$fromTypes = [];
			while(($arg = array_shift($args)) !== null){
				$arg = mb_strtolower($arg);
				if($arg === "to" || $arg === "with" || $arg === "as" || $arg === ">"){
					break;
				}
				$fromTypes[] = $type = BlockType::parse($presets, $arg, $error);
				if($type === null){
					$session->msg("Error parsing from type (\"$arg\"): $error", BuilderSession::MSG_CLASS_ERROR);
					return;
				}
			}
			if(count($fromTypes) === 0){
				$session->msg("<fromTypes> cannot be empty!");
				$this->sendUsage($session);
				return;
			}
			if(count($args) === 0){
				$session->msg(/** @lang text */
					'Ambiguous command! For multiple types, please use the syntax "//replace <fromTypes> as <toTypes>".', BuilderSession::MSG_CLASS_ERROR);
			}
			$toTypes = BlockPicker::parseArgs($this->getPlugin()->getPresetManager(), $args, $random, $error);
			if($toTypes === null){
				$session->msg("Error parsing <toTypes>: $error", BuilderSession::MSG_CLASS_ERROR);
				return;
			}
		}
		$changer = new BlockChanger($toTypes, $fromTypes);

		$selection = $session->getSelection($selName);
		if($selection === null){
			$session->msg("You don't have a selection called \"$selName\"!", BuilderSession::MSG_CLASS_ERROR);
			return;
		}
		if(!$selection->isComplete()){
			$session->msg("Your \"$selName\" selection is incomplete!", BuilderSession::MSG_CLASS_ERROR);
			return;
		}
		if(($level = $selection->getLevel($this->getPlugin()->getServer())) === null){
			$session->msg("Your \"$selName\" selection is in an unloaded world!", BuilderSession::MSG_CLASS_ERROR);
			return;
		}
		$myLevel = $session->getLocation()->getLevel();
		if($level !== $myLevel){
			assert($myLevel !== null);
			$session->msg("Reminder: Your \"$selName\" is in a different world (\"{$level->getFolderName()}\") from your current world (\"{$myLevel->getFolderName()}\").", BuilderSession::MSG_CLASS_WARN);
		}

		if($hollowConfig !== null){
			$stream = new SerializableBlockStreamGetter([$selection, "getHollowStream"], $hollowConfig);
			$size = $selection->getEstimatedSurfaceSize($hollowConfig[0], $hollowConfig[1]);
		}else{
			$stream = new SerializableBlockStreamGetter([$selection, "getSolidStream"], []);
			$size = $selection->getEstimatedSize();
		}

		$goodBlocks = $this->getPlugin()->quickExecute($level, $session, $stream, $changer, $selection->getCenter(), $badBlocks);
		$session->msg("Done! Changed $goodBlocks blocks", BuilderSession::MSG_CLASS_SUCCESS);
		if($badBlocks > 0){
			$session->msg("$badBlocks blocks should have been changed but could not be changed due to construction zone constraints. Please check your settings.", BuilderSession::MSG_CLASS_WARN);
		}
	}

	public function sendUsage(BuilderSession $session, int $class = BuilderSession::MSG_CLASS_ERROR){
		$session->msg(/** @lang text */
			<<<EOU
To change a single block type to another block type:
  //rep [s <selectionName>] [h <padding> <margin>] [r] <from> <to>
To change multiple block type(s) to multiple block type(s):
  //rep [s <selectionName>] [h <padding> <margin>] [r] <from blocks> to <to blocks>
h stands for hollow.
r stands for repeating; refer to <to blocks>'s description.
<from blocks> accepts a block type in each argument (separated by space).
<to blocks> accepts the same syntax as <blocks> in //set (run //set for details). 
See the documentation at {$this->getPlugin()->getDescription()->getWebsite()} for further details.
EOU
			, $class);
	}
}
