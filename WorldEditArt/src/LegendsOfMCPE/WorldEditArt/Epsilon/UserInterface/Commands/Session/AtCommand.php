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

use falkirks\simplewarp\api\SimpleWarpAPI;
use LegendsOfMCPE\WorldEditArt\Epsilon\BuilderSession;
use LegendsOfMCPE\WorldEditArt\Epsilon\Consts;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\UserFormat;
use LegendsOfMCPE\WorldEditArt\Epsilon\WorldEditArt;
use pocketmine\level\Location;
use pocketmine\math\Vector3;
use pocketmine\utils\TextFormat;

class AtCommand extends SessionCommand{
	/** @var SessionCommand[] */
	private $sesCommands = [];
	/** @var SimpleWarpAPI|void */
	private $swApi;

	/**
	 * @param WorldEditArt     $plugin
	 * @param SessionCommand[] $sesCommands
	 */
	public function __construct(WorldEditArt $plugin, array $sesCommands){
		$backFormats = [];
		foreach($sesCommands as $command){
			assert($command->getName(){0} === "/");
			$this->sesCommands[substr($command->getName(), 1)] = $command;
			foreach($command->getAliases() as $alias){
				assert($alias{0} === "/");
				$this->sesCommands[substr($alias, 1)] = $command;
			}

			foreach($command->getFormats() as $formatName => $format){
				$aliases = [substr($command->getName(), 1)];
				foreach($command->getAliases() as $alias){
					$aliases[] = substr($alias, 1);
				}
				array_unshift($format, [
					"name" => "command",
					"type" => "stringenum",
					"enum_values" => $aliases,
				]);
				$backFormats[ucfirst($command->getName()) . ucfirst($formatName)] = $format;
			}
		}

		if($plugin->getServer()->getPluginManager()->getPlugin("SimpleWarp") !== null){
			$this->swApi = SimpleWarpAPI::getInstance($plugin);
		}

		$frontFormats = [
			"byPos" => [
				[
					"name" => "position",
					"type" => "blockpos",
				],
			],
			"bySpawn" => [
				[
					"name" => "type",
					"type" => "stringenum",
					"enum_values" => ["spawn", "s"],
				],
				[
					"name" => "worldName",
					"type" => "string",
				],
			],
			"byBookmark" => [
				[
					"name" => "type",
					"type" => "stringenum",
					"enum_values" => ["bookmark", "bm", "b"],
				],
				[
					"name" => "bookmark",
					"type" => "string",
				],
			],
			"byPlayer" => [
				[
					"name" => "type",
					"type" => "stringenum",
					"enum_values" => ["player", "p"],
				],
				[
					"name" => "player",
					"type" => "target",
				],
			],
		];
		if(isset($this->swApi)){
			$frontFormats["byWarp"] = [
				[
					"name" => "type",
					"type" => "stringenum",
					"enum_values" => ["warp", "w"],
				],
				[
					"name" => "warp",
					"type" => "string",
				],
			];
		}
		$formats = [];
		foreach($frontFormats as $frontName => $frontFormat){
			foreach($backFormats as $backName => $backFormat){
				$formats[$frontName . $backName] = array_merge($frontFormats, $backFormats);
			}
		}
		$warp = isset($this->swApi) ? "|{w <warp>}}" : "";
		parent::__construct($plugin, "/at", "Execute a session command mocking your location", /** @lang text */
			"//at {<x> <y> <z>}|{s <world>}|{b <bookmark>}|{p <player>}{$warp} <command...>", ["/@"], implode(";", Consts::PERM_AT_ANY), $formats);
	}

	public function run(BuilderSession $session, array $args) : void{
		if(!isset($args[2])){
			$this->sendUsage($session);
			return;
		}

		$sessLoc = $session->getLocation();
		$at = $this->toValidCoords(array_slice($args, 0, 2), $sessLoc, $usesAbs, $usesRel);
		if($at !== null){
			$args = array_slice($args, 3);
			if($usesAbs && !$session->hasPermission(Consts::PERM_AT_ABSOLUTE)){
				$session->msg("You don't have permission to use //at with absolute coordinates", BuilderSession::MSG_CLASS_ERROR);
				return;
			}
			if($usesRel && !$session->hasPermission(Consts::PERM_AT_RELATIVE)){
				$session->msg("You don't have permission to use //at with relative coordinates", BuilderSession::MSG_CLASS_ERROR);
				return;
			}
		}else{
			switch(mb_strtolower(array_shift($args))){
				case "s":
				case "spawn":
					if(!$session->hasPermission(Consts::PERM_AT_SPAWN)){
						$session->msg("You don't have permission to use //at spawn");
					}
					$level = $this->getPlugin()->getServer()->getLevelByName($worldName = array_shift($args));
					if($level === null){
						$session->msg("No world called \"$worldName\"", BuilderSession::MSG_CLASS_ERROR);
						return;
					}
					$at = Location::fromObject($level->getSpawnLocation(), $level, $sessLoc->yaw, $sessLoc->pitch);
					break;
				case "b":
				case "bm":
				case "bookmark":
					if(!$session->hasPermission(Consts::PERM_AT_BOOKMARK)){
						$session->msg("You don't have permission to use //at bookmark", BuilderSession::MSG_CLASS_ERROR);
						return;
					}
					$at = $session->getBookmark($bmName = array_shift($args));
					if($at === null){
						$session->msg("No bookmark named \"$bmName\"", BuilderSession::MSG_CLASS_ERROR);
						return;
					}
					break;
				case "p":
				case "player":
					if(!$session->hasPermission(Consts::PERM_AT_PLAYER)){
						$session->msg("You don't have permission to use //at player", BuilderSession::MSG_CLASS_ERROR);
						return;
					}
					$player = $session->getPlugin()->getServer()->getPlayerExact($pname = array_shift($args));
					if($player === null){
						$session->msg("No player called \"$pname\"", BuilderSession::MSG_CLASS_ERROR);
						return;
					}
					$at = $player->getLocation();
					break;
				case "w":
				case "warp":
					if(!isset($this->swApi)){
						$session->msg("Warps are not supported on this server", BuilderSession::MSG_CLASS_ERROR);
						return;
					}
					if(!$session->hasPermission(Consts::PERM_AT_WARP)){
						$session->msg("You don't have permission to use //at warp", BuilderSession::MSG_CLASS_ERROR);
						return;
					}
					if(!isset($this->swApi->getWarpManager()[$warpName = array_shift($args)])){
						$session->msg("No warp named \"$warpName\"", BuilderSession::MSG_CLASS_ERROR);
						return;
					}
					$warp = $this->swApi->getWarp($warpName);
					if(!$warp->canUse($session->getOwner())){
						$session->msg("You don't have permission to use this warp", BuilderSession::MSG_CLASS_ERROR);
						return;
					}
					$dest = $warp->getDestination();
					if(!$dest->isInternal()){
						$session->msg("You may only use internal warps with //at!", BuilderSession::MSG_CLASS_ERROR);
						return;
					}
					$at = Location::fromObject($dest->getPosition(), null, $sessLoc->yaw, $sessLoc->pitch); // param$level = null => fetch from param$pos
					/** @var Location $at */
					break;
				default:
					$this->sendUsage($session);
					return;
			}
		}

		if(!isset($args[0])){
			$this->sendUsage($session);
			return;
		}
		$cmdName = ltrim(array_shift($args), "/");
		if(isset($this->sesCommands[$cmdName])){
			$cmd = $this->sesCommands[$cmdName];
			$color = BuilderSession::MSG_CLASS_COLOR_MAP[BuilderSession::MSG_CLASS_LOADING];
			$session->msg("Executing command " . TextFormat::AQUA . "//$cmdName " . implode(" ", $args) . " {$color}at " .
				UserFormat::formatLocation($at, $color), BuilderSession::MSG_CLASS_LOADING);
			$session->executeAtLocation($at, function() use ($session, $cmd, $args){
				$cmd->run($session, $args);
			});
		}else{
			$session->msg("The command //{$cmdName} cannot be used with //at or does not exist.", BuilderSession::MSG_CLASS_ERROR);
			$this->sendUsage($session);
		}
	}

	/**
	 * @param string[] $coords size=3
	 * @param Vector3  $rel
	 * @param bool     &$usesAbsolute
	 * @param bool     &$usesRelative
	 *
	 * @return null|Vector3
	 */
	private function toValidCoords(array $coords, Vector3 $rel, bool &$usesAbsolute, bool &$usesRelative) : ?Vector3{
		assert(count($coords) === 3);
		$usesAbsolute = false;
		$usesRelative = false;
		$v = $rel->asVector3();
		foreach($coords as $i => $coord){
			if(!is_numeric($coord) and ($coord{0} !== "~" or !is_numeric(substr($coord, 1)))){
				return null;
			}
			$fieldName = chr(ord("x") + $i);
			if($coord{0} === "~"){
				$coord = substr($coord, 1);
				$v->{$fieldName} += (float) $coord;
				$usesRelative = true;
			}else{
				$v->{$fieldName} = (float) $coord;
				$usesAbsolute = true;
			}
		}
		return $v;
	}
}
