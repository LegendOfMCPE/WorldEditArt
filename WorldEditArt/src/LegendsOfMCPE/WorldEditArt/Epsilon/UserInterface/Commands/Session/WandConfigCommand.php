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

namespace LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\Session;

use LegendsOfMCPE\WorldEditArt\Epsilon\BuilderSession;
use LegendsOfMCPE\WorldEditArt\Epsilon\Consts;
use LegendsOfMCPE\WorldEditArt\Epsilon\Session\WandTrigger;
use LegendsOfMCPE\WorldEditArt\Epsilon\WorldEditArt;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;

class WandConfigCommand extends SessionCommand{
	public function __construct(WorldEditArt $plugin){
		parent::__construct($plugin, "/wand", "Configure wand", /** @lang text */
			"//wand OR //wand true|false OR //wand <wandName> OR //wand <wandName> <item> <clickType>", [],
			Consts::PERM_WAND_TOGGLE . ";" . Consts::PERM_WAND_CONFIGURE, [
				"toggle" => [
					[
						"name" => "enable",
						"type" => "bool",
					],
				],
				"configure" => [
					[
						"name" => "wandName",
						"type" => "stringenum",
						"enum_values" => array_keys($plugin->getWandManager()->getWands()),
						"optional" => true,
					],
					[
						"name" => "item",
						"type" => "stringenum",
						"enum_type" => "itemType",
						"optional" => true,
					],
					[
						"name" => "clickType",
						"type" => "stringenum",
						"enum_values" => ["l", "left", "leftClick", "r", "right", "rightClick", "*", "any"],
						"optional" => true,
					],
				],
			]);
	}

	public function run(BuilderSession $session, array $args){
		$wands = $this->getPlugin()->getWandManager()->getWands();
		if(!isset($args[0])){
			// list
			if(!$session->hasPermission(Consts::PERM_WAND_CONFIGURE)){
				$session->msg("You don't have permission to configure wand triggers", BuilderSession::MSG_CLASS_ERROR);
				return;
			}
			$session->msg(implode(", ", array_keys($wands)), BuilderSession::MSG_CLASS_INFO, "Wands Types");
			$messages = [];
			foreach($session->getWandTriggers() as $trigger){
				if($session->hasPermission($wands[$trigger->wandName]->getPermission())){
					$messages[] = sprintf("%s%s %s%s => %s%s",
						TextFormat::AQUA,
						$trigger->actionType === PlayerInteractEvent::LEFT_CLICK_BLOCK ? "Left-click " : "Right-click ",
						TextFormat::LIGHT_PURPLE,
						Item::get($trigger->itemId)->getName(),
						TextFormat::BLUE,
						$trigger->wandName);
				}
			}
			if(count($messages) === 0){
				$session->msg("You don't have permission to access any wands", BuilderSession::MSG_CLASS_ERROR);
				return;
			}
			$session->msg(implode("\n", $messages), BuilderSession::MSG_CLASS_INFO, "Items => Wands");
			return;
		}

		$arg0 = mb_strtolower($args[0]);
		if($arg0 === "true" || $arg0 === "false"){
			if(!$session->hasPermission(Consts::PERM_WAND_TOGGLE)){
				$session->msg("You don't have permission to toggle wands", BuilderSession::MSG_CLASS_ERROR);
				return;
			}
			// toggle
			$session->setWandEnabled($enabled = $arg0 === "true");
			$session->msg($enabled ? "Enabled wands." : "Disabled wands.", BuilderSession::MSG_CLASS_SUCCESS);
			return;
		}

		if(!$session->hasPermission(Consts::PERM_WAND_CONFIGURE)){
			$session->msg("You don't have permission to configure wand triggers", BuilderSession::MSG_CLASS_ERROR);
			return;
		}
		if(!isset($wands[$arg0])){
			$session->msg("There isn't a wand called $arg0. Use //wand to see available wands.", BuilderSession::MSG_CLASS_ERROR);
			return;
		}
		if(!$session->hasPermission($wands[$arg0]->getPermission())){
			$session->msg("You don't have permission to access the $arg0 wand", BuilderSession::MSG_CLASS_ERROR);
			return;
		}

		if(!isset($args[1])){
			// show available triggers
			$cnt = 0;
			foreach($session->getWandTriggers() as $trigger){
				if($trigger->wandName !== $arg0){
					continue;
				}
				++$cnt;
				$session->msg(sprintf("%s%s %s%s => %s%s",
					TextFormat::AQUA,
					$trigger->actionType === PlayerInteractEvent::LEFT_CLICK_BLOCK ? "Left-Click" : "Right-Click",
					TextFormat::LIGHT_PURPLE,
					Item::get($trigger->itemId)->getName(),
					TextFormat::BLUE,
					$trigger->wandName));
			}
			if($cnt === 0){
				$session->msg("You don't have any items that will trigger the $arg0 wand!", BuilderSession::MSG_CLASS_ERROR);
			}
			return;
		}

		if(!isset($args[2])){
			$this->sendUsage($session);
			return;
		}

		/** @var Item[] $items */
		$items = Item::fromString($itemName = $args[1], true);

		$left = false;
		$right = false;
		switch($args[2]){
			case "l":
			case "left":
			case "leftClick":
				$left = true;
				break;
			case "r":
			case "right":
			case "rightClick":
				$right = true;
				break;
			case "*":
			case "any":
				$left = $right = true;
				break;
			default:
				$session->msg("Unknown click type $args[2].", BuilderSession::MSG_CLASS_ERROR);
				return;
		}

		$triggers = [];
		foreach($items as $item){
			if($left){
				$triggers[] = new WandTrigger($arg0, $item->getId(), PlayerInteractEvent::LEFT_CLICK_BLOCK);
			}
			if($right){
				$triggers[] = new WandTrigger($arg0, $item->getId(), PlayerInteractEvent::RIGHT_CLICK_BLOCK);
			}
		}
		foreach($triggers as $trigger){
			$session->addWandTrigger($trigger);
			$session->msg(sprintf("Added trigger: %s%s %s%s => %s%s",
				TextFormat::AQUA,
				$trigger->actionType === PlayerInteractEvent::LEFT_CLICK_BLOCK ? "Left-click " : "Right-click ",
				TextFormat::LIGHT_PURPLE,
				Item::get($trigger->itemId)->getName(),
				TextFormat::BLUE,
				$trigger->wandName), BuilderSession::MSG_CLASS_SUCCESS);
		}
	}
}
