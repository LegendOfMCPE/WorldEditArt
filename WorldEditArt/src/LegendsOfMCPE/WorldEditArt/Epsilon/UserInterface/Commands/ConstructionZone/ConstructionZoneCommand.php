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
					"enum_values" => ["add"],
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
			],
			"remove" => [
				[
					"name" => "action",
					"type" => "stringenum",
					"enum_values" => ["remove", "delete", "del"],
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
			],
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
			],
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
			],
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
			],
		]);
	}

	public function run(BuilderSession $session, array $args) : void{
		if(!isset($args[0])){
			$args = ["view"];
		}

		$action = mb_strtolower($args[0]);
		if($action === "lock" || $action === "unlock" || $action === "view"){
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
			}elseif($action === "unlock"){
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
				if($zone->getLockingSession($ownerName) === spl_object_hash($session->getOwner())){
					if(!$session->hasPermission(Consts::PERM_CZONE_BUILDER_UNLOCK_SELF)){
						$session->msg("You don't have permission to unlock construction zones!", BuilderSession::MSG_CLASS_ERROR);
						return;
					}
					$zone->unlock();
				}else{
					if(!$session->hasPermission(Consts::PERM_CZONE_ADMIN_UNLOCK_OTHER)){
						$session->msg("You don't have permission to unlock construction zones locked by others ($ownerName)!", BuilderSession::MSG_CLASS_ERROR);
						return;
					}
					$zone->unlock();
				}
				$session->msg("Unlocked construction zone \"{$zone->getName()}\"", BuilderSession::MSG_CLASS_SUCCESS);
				return;
			}elseif($action === "view"){
				if(isset($args[1])){
					$this->showZoneInfo($session, $zones[0]);
				}else{
					$session->msg("You are standing in " . count($zones) . " zones.");
					foreach($zones as $zone){
						$this->showZoneInfo($session, $zone);
					}
				}
				return;
			}
		}elseif($action === "bypass"){
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
				if($targetPlayer===null){
					$session->msg("Player $args[3] not found", BuilderSession::MSG_CLASS_ERROR);
					return;
				}$targets=$this->getPlugin()->getSessionsOf($targetPlayer);
				if($targets === []){
					$session->msg("Player $args[3] does not have a builder session started", BuilderSession::MSG_CLASS_ERROR);
					return;
				}
				$target = $targets[PlayerBuilderSession::SESSION_KEY]; // TODO minion sessions
			}
			if($isLock){
				$target->setBypassLock($bool);
				$session->msg(($bool ? "Allowed" : "Disallowed") . " {$target->getOwner()->getName()} to ignore whether construction zones are locked", BuilderSession::MSG_CLASS_SUCCESS);
			}else{
				$target->setBypassZone($bool);
				$session->msg(($bool ? "Allowed" : "Disallowed") . " {$target->getOwner()->getName()} to edit outside construction zones", BuilderSession::MSG_CLASS_SUCCESS);
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
