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

namespace LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\ConstructionZone;

use LegendsOfMCPE\WorldEditArt\Epsilon\BuilderSession;
use LegendsOfMCPE\WorldEditArt\Epsilon\ConstructionZone;
use LegendsOfMCPE\WorldEditArt\Epsilon\Consts;
use LegendsOfMCPE\WorldEditArt\Epsilon\Session\PlayerBuilderSession;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\Session\SessionCommand;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\UserFormat;
use LegendsOfMCPE\WorldEditArt\Epsilon\WorldEditArt;
use pocketmine\utils\TextFormat;

class ConstructionZoneCommand extends SessionCommand{
	public function __construct(WorldEditArt $plugin){
		parent::__construct($plugin, "/czone", "Use construction zones for building", "/cz lock|unlock|check [construction zone name] [edit|blocks|entry]", ["/cz"], implode(";", Consts::PERM_CZONE_COMMAND_ANY), [
			"add" => [
				[
					"name" => "action",
					"type" => "stringenum",
					"enum_values" => ["add", "change"],
				],
				[
					"name" => "constructionZoneName",
					"type" => "string",
				],
				[
					"name" => "selectionName",
					"type" => "string",
					"optional" => true,
				],
			], // done
			"remove" => [
				[
					"name" => "action",
					"type" => "stringenum",
					"enum_values" => ["remove", "rm", "delete", "del"],
				],
				[
					"name" => "constructionZoneName",
					"type" => "string",
				],
			],
			"bypass" => [
				[
					"name" => "action",
					"type" => "stringenum",
					"enum_values" => ["bypass"],
				],
				[
					"name" => "type",
					"type" => "stringenum",
					"enum_values" => ["lock", "zone"],
				],
				[
					"name" => "enableBypass",
					"type" => "bool",
				],
				[
					"name" => "player",
					"type" => "target",
					"optional" => true,
				],
			], // done
			"lock" => [
				[
					"name" => "action",
					"type" => "stringenum",
					"enum_values" => ["lock"],
				],
				[
					"name" => "constructionZoneName",
					"type" => "string",
					"optional" => true,
				],
				[
					"name" => "lockType",
					"type" => "stringenum",
					"enum_values" => ["edit", "blocks", "entry"],
					"optional" => true,
				],
			], // done
			"lock_here" => [
				[
					"name" => "action",
					"type" => "stringenum",
					"enum_values" => ["lock"],
				],
				[
					"name" => "constructionZoneName",
					"type" => "stringenum",
					"enum_values" => ["here"],
				],
				[
					"name" => "lockType",
					"type" => "stringenum",
					"enum_values" => ["edit", "blocks", "entry"],
				],
			], // done
			"other" => [
				[
					"name" => "action",
					"type" => "stringenum",
					"enum_values" => ["unlock", "view"],
					"optional" => true,
				],
				[
					"name" => "constructionZoneName",
					"type" => "string",
					"optional" => true,
				],
			], // done
		]);
	}

	public function run(BuilderSession $session, array $args) : void{
		if(!isset($args[0])){
			$args = ["view"];
		}

		$action = mb_strtolower($args[0]);
		if(in_array($action, ["lock", "unlock", "view"], true)){
			$allZones = $this->getPlugin()->getConstructionZoneManager()->getConstructionZones();
			/** @var ConstructionZone[] $zones */
			$zones = [];
			if(isset($args[1]) && mb_strtolower($args[1]) !== "here"){
				if(!isset($allZones[mb_strtolower($args[1])])){
					$session->msg("No such zone called $args[1]", BuilderSession::MSG_CLASS_ERROR);
					return;
				}
				$zones[] = $allZones[mb_strtolower($args[1])];
			}else{
				foreach($allZones as $zone){
					if($zone->getShape()->isInside($session->getLocation())){
						$zones[] = $zone;
					}
				}
			}

			if($action === "lock"){
				if(count($zones) > 1){
					$session->msg("You are standing in " . count($zones) . " zones! Which one do you wish to lock?", BuilderSession::MSG_CLASS_WARN);
					$session->msg(implode(", ", array_map(function(ConstructionZone $zone) : string{
						return $zone->getName() . ($zone->getLockingSession($ownerName) === null ? "" : " (locked by $ownerName)");
					}, $zones)), BuilderSession::MSG_CLASS_WARN);
					$session->msg("Please run the command with the name: //cz lock <zone> " . ($args[2] ?? ""), BuilderSession::MSG_CLASS_WARN);
					return;
				}
				if(count($zones) === 0){
					$session->msg("You are not standing in any zones! Please either run this command again when you are standing in a zone, or specify the zone you wish to lock: //cz lock <zone> " . ($args[2] ?? ""), BuilderSession::MSG_CLASS_WARN);
					return;
				}

				$zone = $zones[0];

				if($zone->getLockingSession($ownerName) !== null){
					$session->msg("The construction zone {$zone->getName()} has already been locked by $ownerName", BuilderSession::MSG_CLASS_ERROR);
					return;
				}
				if(!isset(ConstructionZone::LOCK_STRING_TO_ID[$modeName = mb_strtolower($args[2] ?? "edit")])){
					$session->msg("Unknown lock type \"$modeName\"! Possible values: edit (default), blocks, entry", BuilderSession::MSG_CLASS_ERROR);
					return;
				}
				$modeId = ConstructionZone::LOCK_STRING_TO_ID[$modeName];
				if(!$session->hasPermission(ConstructionZone::LOCK_ID_TO_PERM[$modeId])){
					$session->msg("You don't have permission to use the \"$modeName\" lock mode", BuilderSession::MSG_CLASS_ERROR);
					return;
				}

				$zone->lock($session, $modeId);
				$session->msg("Locked construction zone \"{$zone->getName()}\" with mode \"$modeName\"");
				return;
			}

			if($action === "unlock"){
				if(count($zones) > 1){
					$session->msg("You are standing in " . count($zones) . " zones! Which one do you wish to unlock?", BuilderSession::MSG_CLASS_WARN);
					$session->msg(implode(", ", array_map(function(ConstructionZone $zone) : string{
						return $zone->getName() . ($zone->getLockingSession($ownerName) === null ? "" : " (locked by $ownerName)");
					}, $zones)), BuilderSession::MSG_CLASS_WARN);
					$session->msg("Please run the command with the name: //cz unlock <zone>", BuilderSession::MSG_CLASS_WARN);
					return;
				}
				if(count($zones) === 0){
					$session->msg("You are not standing in any zones! Please either run this command again when you are standing in a zone, or specify the zone you wish to unlock: //cz unlock <zone>", BuilderSession::MSG_CLASS_WARN);
					return;
				}

				$zone = $zones[0];
				$self = $zone->getLockingSession($ownerName) === spl_object_hash($session->getOwner());
				if(!$session->hasPermission($self ?
					Consts::PERM_CZONE_BUILDER_UNLOCK_SELF : Consts::PERM_CZONE_ADMIN_UNLOCK_OTHER)){
					$session->msg("You don't have permission to unlock construction zones locked by " .
						($self ? "yourself!" : "others!"), BuilderSession::MSG_CLASS_ERROR);
					return;
				}
				$zone->unlock();
				$session->msg("Unlocked construction zone \"{$zone->getName()}\"", BuilderSession::MSG_CLASS_SUCCESS);
				return;
			}

			if($action === "view"){
				if(isset($args[1])){
					$this->showZoneInfo($session, $zones[0]);
					return;
				}
				$session->msg("You are standing in " . count($zones) . " zones.");
				foreach($zones as $zone){
					$this->showZoneInfo($session, $zone);
				}
				return;
			}
		}

		if($action === "bypass"){
			if(!isset($args[2])){
				$this->sendUsage($session);
				return;
			}
			$type = mb_strtolower($args[1]);
			$isLock = false;
			if($type === "lock"){
				$isLock = true;
			}elseif($type !== "zone"){
				$this->sendUsage($session);
				return;
			}
			$bool = in_array(mb_strtolower($args[2]), ["true", "yes", "on", "1"], true);
			$target = $session;
			if(isset($args[3])){
				$targetPlayer = $this->getPlugin()->getServer()->getPlayer($args[3]);
				if($targetPlayer === null){
					$session->msg("Player $args[3] not found", BuilderSession::MSG_CLASS_ERROR);
					return;
				}
				$targets = $this->getPlugin()->getSessionsOf($targetPlayer);
				if($targets === []){
					$session->msg("Player $args[3] does not have a builder session started", BuilderSession::MSG_CLASS_ERROR);
					return;
				}
				$target = $targets[PlayerBuilderSession::SESSION_KEY]; // TODO minion sessions
			}
			if($isLock){
				if(!$session->hasPermission(Consts::PERM_CZONE_ADMIN_LOCK_BYPASS)){
					$session->msg("You don't have permission to use this command", BuilderSession::MSG_CLASS_ERROR);
					return;
				}
				$target->setBypassLock($bool);
				$session->msg(($bool ? "Allowed" : "Disallowed") . " {$target->getOwner()->getName()} to ignore whether construction zones are locked", BuilderSession::MSG_CLASS_SUCCESS);
			}else{
				if(!$session->hasPermission(Consts::PERM_CZONE_ADMIN_BYPASS)){
					$session->msg("You don't have permission to use this command", BuilderSession::MSG_CLASS_ERROR);
					return;
				}
				$target->setBypassZone($bool);
				$session->msg(($bool ? "Allowed" : "Disallowed") . " {$target->getOwner()->getName()} to edit outside construction zones", BuilderSession::MSG_CLASS_SUCCESS);
			}
			return;
		}

		if(in_array($action, ["add", "change", "ch"], true)){
			if(!$session->hasPermission(Consts::PERM_CZONE_ADMIN_CHANGE)){
				$session->msg("You don't have permission to use this command", BuilderSession::MSG_CLASS_ERROR);
				return;
			}
			if(!isset($args[1])){
				$this->sendUsage($session);
				return;
			}
			$name = $args[1];
			$lowName = mb_strtolower($name);
			if($lowName === "here"){
				$session->msg("\"here\" is not an acceptable name for construction zones!", BuilderSession::MSG_CLASS_ERROR);
				return;
			}
			$selName = $args[2] ?? $session->getDefaultSelectionName();
			if(!$session->hasSelection($selName)){
				$session->msg("Your \"$selName\" selection has not been set!", BuilderSession::MSG_CLASS_ERROR);
				return;
			}
			$shape = $session->getSelection($selName);
			assert($shape !== null);
			if(!$shape->isComplete()){
				$session->msg("Your \"$selName\" selection is not complete!", BuilderSession::MSG_CLASS_ERROR);
				return;
			}

			if($action === "add"){
				$mgr = $this->getPlugin()->getConstructionZoneManager();
				if($mgr->getConstructionZone($name) !== null){
					$session->msg("There is already a construction zone called \"$name\"! Use \"//cz change\" instead if you want to change the shape of a construction zone.", BuilderSession::MSG_CLASS_ERROR);
					return;
				}
				$zone = new ConstructionZone($name, $shape);
				$mgr->add($zone);
				$session->msg("Created construction zone \"$name\" based on your selection \"$selName\"", BuilderSession::MSG_CLASS_SUCCESS);
			}else{
				$zone = $this->getPlugin()->getConstructionZoneManager()->getConstructionZone($name);
				if($zone === null){
					$session->msg("There isn't a construction zone called \"$name\"! Use \"//cz add\" instead if you want to add a construction zone.", BuilderSession::MSG_CLASS_ERROR);
					return;
				}
				$zone->setShape($shape);
				$session->msg("Changed shape of construction zone \"{$zone->getName()}\" based on your selection \"$selName\"", BuilderSession::MSG_CLASS_SUCCESS);
				// TODO present shape of selection
			}
			return;
		}

		if($action === "rename"){
			if(!$session->hasPermission(Consts::PERM_CZONE_ADMIN_CHANGE)){
				$session->msg("You don't have permission to use this command", BuilderSession::MSG_CLASS_ERROR);
				return;
			}
			if(!isset($args[2])){
				$this->sendUsage($session);
				return;
			}
			[, $old, $new] = $args;
			$mgr = $this->getPlugin()->getConstructionZoneManager();
			$zone = $mgr->getConstructionZone($old);
			if($zone === null){
				$session->msg("There isn't a construction zone called \"$old\".", BuilderSession::MSG_CLASS_ERROR);
				return;
			}
			$mgr->rename($zone, $new);
			$session->msg("Renamed construction zone \"$old\" to \"$new\"", BuilderSession::MSG_CLASS_SUCCESS);
			return;
		}

		if(in_array($action, ["remove", "rm", "del", "delete"], true)){
			if(!$session->hasPermission(Consts::PERM_CZONE_ADMIN_CHANGE)){
				$session->msg("You don't have permission to use this command", BuilderSession::MSG_CLASS_ERROR);
				return;
			}
			if(!isset($args[1])){
				$this->sendUsage($session);
				return;
			}
			if(($zone = $this->getPlugin()->getConstructionZoneManager()->remove($args[1])) !== null){
				$session->msg("Removed the construction zone \"{$zone->getName()}", BuilderSession::MSG_CLASS_SUCCESS);
			}else{
				$session->msg("There isn't a construction zone called \"$args[1]\"!", BuilderSession::MSG_CLASS_ERROR);
			}
			return;
		}
	}

	private function showZoneInfo(BuilderSession $session, ConstructionZone $zone) : void{
		$session->msg(implode("\n", [
			"Range: " . UserFormat::describeShape($this->getPlugin()->getServer(), $zone->getShape(), UserFormat::FORMAT_USER_RANGE),
			"State: " . TextFormat::GOLD . ($zone->getLockingSession($ownerName) === null) ? "Not locked" :
				sprintf("Locked by %s%s%s with mode %s\"%s\"",
					TextFormat::AQUA, $ownerName, TextFormat::GOLD,
					TextFormat::LIGHT_PURPLE, array_search($zone->getLockMode(), ConstructionZone::LOCK_STRING_TO_ID, true)),
		]), BuilderSession::MSG_CLASS_INFO, "Construction Zone \"" . $zone->getName() . "\"");
	}
}
