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
use LegendsOfMCPE\WorldEditArt\Epsilon\IShape;
use LegendsOfMCPE\WorldEditArt\Epsilon\MathUtils;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\Session\SessionCommand;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\UserFormat;
use LegendsOfMCPE\WorldEditArt\Epsilon\WorldEditArt;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use sofe\libgeom\Shape;
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

	public function run(BuilderSession $session, array $args){
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
				$distance = floatval($args[1]);
				$from = $session->getLocation();
				$to = $from->add(MathUtils::yawPitchToVector($from->yaw, $from->pitch)->multiply($distance));
				$shape = new class($from->getLevel(), $from, $to) extends CuboidShape implements IShape{
					public function getBaseShape() : Shape{
						return $this;
					}
				};
				$session->setSelection($selName = $args[2] ?? $session->getDefaultSelectionName(), $shape);
				break;
			case "grow":
			case "g":
				$minus = new Vector3((float) $args[1], (float) $args[2], (float) $args[3]);
				$plus = new Vector3((float) $args[4], (float) $args[5], (float) $args[6]);
				$selName = $args[7] ?? $session->getDefaultSelectionName();
				$shape = $session->getSelection($selName);
				if($shape === null){
					goto grow_create_new_cuboid;
				}
				$shape = $shape->getBaseShape();
				if(!($shape instanceof CuboidShape)){
					goto grow_create_new_cuboid;
				}
				$min = $shape->getMin();
				$max = $shape->getMax();
				$shape->setFrom($min->subtract($minus))->setTo($max->add($plus));
				break;
				//@formatter:off
			grow_create_new_cuboid:
				//@formatter:on
				$loc = $session->getLocation();
				$shape = new class($loc->getLevel(), $loc, $loc) extends CuboidShape implements IShape{
					public function getBaseShape() : Shape{
						return $this;
					}
				};
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
				// HACK: Must be immediately followed by a setter call
				$from = $shape->getFrom();
				$to = $shape->getTo();
				$from->y = 0;
				$to->y = Level::Y_MAX;
				$shape->setFrom($from)->setTo($to);
				break;
			default:
				$this->sendUsage($session);
				return;
		}
		$session->msg(UserFormat::describeShape($this->getPlugin()->getServer(), $shape, UserFormat::FORMAT_USER_DEFINITION), BuilderSession::MSG_CLASS_SUCCESS, "Changed selection \"$selName\" to:");
	}
}
