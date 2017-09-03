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
use LegendsOfMCPE\WorldEditArt\Epsilon\WorldEditArt;
use sofe\libgeom\shapes\EllipsoidShape;

class SphereCommand extends SessionCommand{
	public function __construct(WorldEditArt $plugin){
		parent::__construct($plugin, "/sphere", "Manage a sphere selection", "//sph <x-radius> [y-radius] [z-radius]", ["/sph"], Consts::PERM_SELECT_SET_SPHERE, [
			"default" => [
				[
					"name" => "xRadius",
					"type" => "float",
				],
				[
					"name" => "yRadius",
					"type" => "float",
					"optional" => true,
				],
				[
					"name" => "zRadius",
					"type" => "float",
					"optional" => true,
				],
			],
		]);
	}

	public function run(BuilderSession $session, array $args) : void{
		$selName = $session->getDefaultSelectionName();
		$new = false;
		while(isset($args[0])){
			$arg = mb_strtolower($args[0]);
			if($arg === "s"){
				array_shift($args);
				$selName = array_shift($args);
				continue;
			}
			if($arg === "n" || $arg === "new"){
				array_shift($args);
				$new = true;
				continue;
			}
			break; // parse_args
		}
		if(count($args) === 1){
			if(!is_numeric($args[0])){
				$this->sendUsage($session);
				return;
			}
			$radii = array_fill(0, 3, (float) $args[0]);
		}elseif(count($args) === 3){
			if(!is_numeric($args[0]) || !is_numeric($args[1]) || !is_numeric($args[2])){
				$this->sendUsage($session);
				return;
			}
			$radii = array_map("floatval", $args);
		}else{
			$this->sendUsage($session);
			return;
		}
		if($new ||
			($sel = $session->getSelection($selName)) === null or
			!(($base = $sel->getBaseShape()) instanceof EllipsoidShape)){
			$sel = new ShapeWrapper(new EllipsoidShape($session->getLocation()->getLevel(), $session->getLocation(), $radii[0], $radii[1], $radii[2]));
			$session->setSelection($selName, $sel);
			$session->msg(UserFormat::describeShape($this->getPlugin()->getServer(), $sel, UserFormat::FORMAT_USER_DEFINITION), BuilderSession::MSG_CLASS_SUCCESS, "Your selection has been changed");
			return;
		}

		/** @var EllipsoidShape $base */
		$base->setRadiusX($radii[0]);
		$base->setRadiusY($radii[1]);
		$base->setRadiusZ($radii[2]);
		$session->msg(UserFormat::describeShape($this->getPlugin()->getServer(), $sel, UserFormat::FORMAT_USER_DEFINITION),
			BuilderSession::MSG_CLASS_SUCCESS, "Your selection has been changed to:");
	}
}
