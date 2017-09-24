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

namespace LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\Selection\Edit;

use LegendsOfMCPE\WorldEditArt\Epsilon\BuilderSession;
use LegendsOfMCPE\WorldEditArt\Epsilon\Consts;
use LegendsOfMCPE\WorldEditArt\Epsilon\LibgeomAdapter\ShapeWrapper;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\Session\SessionCommand;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\UserFormat;
use LegendsOfMCPE\WorldEditArt\Epsilon\Utils\WEAUtils;
use LegendsOfMCPE\WorldEditArt\Epsilon\WorldEditArt;
use pocketmine\math\Vector3;
use sofe\libgeom\shapes\CuboidShape;

class CuboidCommand extends SessionCommand{
	public function __construct(WorldEditArt $plugin){
		parent::__construct($plugin, "/cuboid", "Manage a cuboid selection", "/cub shoot <distance> [selectionName] OR /cub grow <-x> <-y> <-z> <+x> <+y> <+z> [selectionName] OR /cub skybed [selectionName]", ["/cub"], Consts::PERM_SELECT_SET_CUBOID, [
			"shoot" => [
				[
					"name" => "action",
					"type" => "stringenum",
					"enum_values" => ["shoot", "s"],
				],
				[
					"name" => "distance",
					"type" => "float",
				],
				[
					"name" => "selectionName",
					"type" => "string",
					"optional" => true,
				],
			],
			"grow" => [
				[
					"name" => "action",
					"type" => "stringenum",
					"enum_values" => ["grow", "g"],
				],
				[
					"name" => "new",
					"type" => "stringenum",
					"enum_values" => ["new"],
					"optional" => true,
				],
				[
					"name" => "minus",
					"type" => "blockpos",
				],
				[
					"name" => "plus",
					"type" => "blockpos",
				],
				[
					"name" => "selectionName",
					"type" => "string",
					"optional" => true,
				],
			],
			"skybed" => [
				[
					"name" => "action",
					"type" => "stringenum",
					"enum_values" => ["skybed", "sb"],
				],
				[
					"name" => "selectionName",
					"type" => "string",
					"optional" => true,
				],
			],
		]);
	}

	public function run(BuilderSession $session, array $args) : void{
		if(!isset($args[0])){
			$this->sendUsage($session);
			return;
		}
		switch(mb_strtolower($args[0])){
			case "shoot":
			case "s":
				if(!isset($args[1])){
					$this->sendUsage($session);
					return;
				}
				$distance = (float) $args[1];
				$from = $session->getLocation();
				$to = $from->add(WEAUtils::yawPitchToVector($from->yaw, $from->pitch)->multiply($distance));
				$shape = new ShapeWrapper(new CuboidShape($from->getLevel(), $from, $to));
				$session->setSelection($selName = $args[2] ?? $session->getDefaultSelectionName(), $shape);
				break;
			case "grow":
			case "g":
				if($new = (mb_strtolower($args[1]) === "new")){
					array_shift($args);
				}
				$minus = new Vector3((float) $args[1], (float) $args[2], (float) $args[3]);
				$plus = new Vector3((float) $args[4], (float) $args[5], (float) $args[6]);
				$selName = $args[7] ?? $session->getDefaultSelectionName();
				$shape = $session->getSelection($selName);
				if($new || $shape === null){
					goto grow_create_new_cuboid;
				}
				$shape = $shape->getBaseShape();
				if(!($shape instanceof CuboidShape)){
					$session->msg("Your prior \"$selName\" selection was not a cuboid. A new cuboid growing from your location will be created.", BuilderSession::MSG_CLASS_WARN);
					goto grow_create_new_cuboid;
				}
				if($shape->getLevel($this->getPlugin()->getServer()) === null){
					$session->msg("Your prior \"$selName\" selection was in an unloaded level, so your selection is reset.", BuilderSession::MSG_CLASS_WARN);
					goto grow_create_new_cuboid;
				}
				if(!$shape->isComplete()){
					$session->msg("Your prior \"$selName\" selection is incomplete, so a new cuboid growing from your location will be created.", BuilderSession::MSG_CLASS_WARN);
					goto grow_create_new_cuboid;
				}
				$myLevel = $session->getLocation()->getLevel();
				assert($myLevel !== null);
				if($shape->getLevelName() !== $myLevel->getFolderName()){
					$session->msg("Reminder: Your \"$selName\" selection is in world \"{$shape->getLevelName()}\", " .
						"not your current world (\"{$myLevel->getFolderName()}\")!",
						BuilderSession::MSG_CLASS_WARN);
				}
				$min = $shape->getMin();
				$max = $shape->getMax();
				/** @noinspection NullPointerExceptionInspection */
				$shape->setFrom($min->subtract($minus))->setTo($max->add($plus));
				break;
				//@formatter:off
			grow_create_new_cuboid:
				//@formatter:on
				$loc = $session->getLocation();
				$shape = new ShapeWrapper(new CuboidShape($loc->getLevel(), $loc->subtract($minus), $loc->add($plus)));
				$session->setSelection($selName, $shape);
				break;
			case "skybed":
			case "sb":
				$selName = $args[1] ?? $session->getDefaultSelectionName();
				$shape = $session->getSelection($selName);
				if($shape === null){
					$session->msg("No selection named $selName", BuilderSession::MSG_CLASS_ERROR);
					return;
				}
				$shape = $shape->getBaseShape();
				if(!($shape instanceof CuboidShape)){
					$session->msg("Selection $selName is not a cuboid", BuilderSession::MSG_CLASS_ERROR);
					return;
				}
				$level = $shape->getLevel($this->getPlugin()->getServer());
				if($level === null){
					$session->msg("Your \"$selName\" selection is in an unloaded level, so //cub skybed cannot be executed.", BuilderSession::MSG_CLASS_ERROR);
					return;
				}
				$myLevel = $session->getLocation()->getLevel();
				if($level !== $myLevel){
					assert($myLevel !== null);
					$session->msg("Reminder: Your \"$selName\" selection is in a different world (\"{$level->getFolderName()}\") from your current world ({$myLevel->getFolderName()})", BuilderSession::MSG_CLASS_WARN);
				}
				$from = $shape->getFrom();
				$to = $shape->getTo();
				// HACK: Must be immediately followed by a setter call
				$from->y = 0;
				$to->y = $shape->getLevel($this->getPlugin()->getServer())->getWorldHeight();
				$shape->setFrom($from)->setTo($to);
				break;
			default:
				$this->sendUsage($session);
				return;
		}
		$session->msg(UserFormat::describeShape($this->getPlugin()->getServer(), $shape, UserFormat::FORMAT_USER_DEFINITION), BuilderSession::MSG_CLASS_SUCCESS, "Changed selection \"$selName\" to:");
	}
}
