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
use LegendsOfMCPE\WorldEditArt\Epsilon\LibgeomAdapter\ShapeDescriptor;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\Session\SessionCommand;
use LegendsOfMCPE\WorldEditArt\Epsilon\WorldEditArt;
use pocketmine\utils\TextFormat;

class ConstructionZoneCommand extends SessionCommand{
	public function __construct(WorldEditArt $plugin){
		parent::__construct($plugin, "/czone", "Use construction zones for building", "/cz lock|unlock|check [construction zone name] [edit|blocks|entry]", ["/cz"], implode(";", Consts::PERM_CZONE_BUILDER_MANAGE_ANY), [
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
				]
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
				]
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

	public function run(BuilderSession $session, array $args){
		if(!isset($args[0])){
			$args = ["view"];
		}

		$allZones = $this->getPlugin()->getConstructionZones();
		/** @var ConstructionZone[] $zones */
		$zones = [];
		if(isset($args[1]) && strtolower($args[1]) !== "here"){
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
		switch(strtolower($args[0])){
			case "lock":
				if(count($zones) > 1){
					$session->msg("You are standing in " . count($zones) . " zones! Which one do you wish to lock?", BuilderSession::MSG_CLASS_WARN);
					$session->msg(implode(", ", array_map(function(ConstructionZone $zone) : string{
						return $zone->getName() . ($zone->getLockingSession() === null ? "" : " (locked by {$zone->getLockingSession()->getOwner()->getName()})");
					}, $zones)), BuilderSession::MSG_CLASS_WARN);
					$session->msg("Please run the command with the name: //cz lock <zone> " . ($args[2] ?? ""), BuilderSession::MSG_CLASS_WARN);
					return;
				}elseif(count($zones) === 0){
					$session->msg("You are not standing in any zones! Please either run this command again when you are standing in a zone, or specify the zone you wish to lock: //cz lock <zone> " . ($args[2] ?? ""), BuilderSession::MSG_CLASS_WARN);
					return;
				}
				$zone = $zones[0];

				if($zone->getLockingSession() !== null){
					$session->msg("The construction zone {$zone->getName()} has already been locked by {$zone->getLockingSession()->getOwner()->getName()}", BuilderSession::MSG_CLASS_ERROR);
					return;
				}

				if(!isset(ConstructionZone::LOCK_STRING_TO_ID[$modeName = strtolower($args[2] ?? "edit")])){
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

			case "unlock":
				if(count($zones) > 1){
					$session->msg("You are standing in " . count($zones) . " zones! Which one do you wish to unlock?", BuilderSession::MSG_CLASS_WARN);
					$session->msg(implode(", ", array_map(function(ConstructionZone $zone) : string{
						return $zone->getName() . ($zone->getLockingSession() === null ? "" : " (locked by {$zone->getLockingSession()->getOwner()->getName()})");
					}, $zones)), BuilderSession::MSG_CLASS_WARN);
					$session->msg("Please run the command with the name: //cz unlock <zone>", BuilderSession::MSG_CLASS_WARN);
					return;
				}elseif(count($zones) === 0){
					$session->msg("You are not standing in any zones! Please either run this command again when you are standing in a zone, or specify the zone you wish to unlock: //cz unlock <zone>", BuilderSession::MSG_CLASS_WARN);
					return;
				}
				$zone = $zones[0];
				if($zone->getLockingSession()->getOwner() === $session->getOwner()){
					if(!$session->hasPermission(Consts::PERM_CZONE_BUILDER_UNLOCK_SELF)){
						$session->msg("You don't have permission to unlock construction zones!", BuilderSession::MSG_CLASS_ERROR);
						return;
					}
					$zone->unlock();
				}else{
					if(!$session->hasPermission(Consts::PERM_CZONE_BUILDER_UNLOCK_OTHER)){
						$session->msg("You don't have permission to unlock construction zones locked by others ({$zone->getLockingSession()->getOwner()->getName()})!", BuilderSession::MSG_CLASS_ERROR);
						return;
					}
					$zone->unlock();
				}
				$session->msg("Unlocked construction zone \"{$zone->getName()}\"", BuilderSession::MSG_CLASS_SUCCESS);
				return;
			case "view":
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
	}

	private function showZoneInfo(BuilderSession $session, ConstructionZone $zone){
		$session->msg(implode("\n", [
			"Range: " . ShapeDescriptor::describe($zone->getShape(), ShapeDescriptor::FORMAT_USER_RANGE),
			"State: " . TextFormat::GOLD . ($zone->getLockingSession() === null ? "Not locked" :
				sprintf("Locked by %s%s%s with mode %s\"%s\"",
					TextFormat::AQUA, $zone->getLockingSession()->getOwner()->getName(), TextFormat::GOLD,
					TextFormat::LIGHT_PURPLE, array_search($zone->getLockMode(), ConstructionZone::LOCK_STRING_TO_ID))),
		]), BuilderSession::MSG_CLASS_INFO, "Construction Zone \"" . $zone->getName() . "\"");
	}
}
