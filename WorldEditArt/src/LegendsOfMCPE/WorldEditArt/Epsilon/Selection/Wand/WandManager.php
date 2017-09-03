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

namespace LegendsOfMCPE\WorldEditArt\Epsilon\Selection\Wand;

use LegendsOfMCPE\WorldEditArt\Epsilon\Selection\Wand\Impl\CuboidPosWand;
use LegendsOfMCPE\WorldEditArt\Epsilon\Selection\Wand\Impl\CylinderBaseCenterWand;
use LegendsOfMCPE\WorldEditArt\Epsilon\Selection\Wand\Impl\CylinderCircumProjectionWand;
use LegendsOfMCPE\WorldEditArt\Epsilon\Selection\Wand\Impl\CylinderCircumRotateWand;
use LegendsOfMCPE\WorldEditArt\Epsilon\Selection\Wand\Impl\CylinderShiftWand;
use LegendsOfMCPE\WorldEditArt\Epsilon\Selection\Wand\Impl\CylinderTopCenterWand;
use LegendsOfMCPE\WorldEditArt\Epsilon\Selection\Wand\Impl\SphereCenterWand;
use LegendsOfMCPE\WorldEditArt\Epsilon\Selection\Wand\Impl\SphereRadiusWand;
use LegendsOfMCPE\WorldEditArt\Epsilon\WorldEditArt;

class WandManager{
	private $plugin;
	private $wands = [];
	private $cmds = [];

	public function __construct(WorldEditArt $plugin){
		$this->plugin = $plugin;

		$this->addWand(new CuboidPosWand(true));
		$this->addWand(new CuboidPosWand(false));
		$this->addWand(new CylinderShiftWand);
		$this->addWand(new CylinderTopCenterWand);
		$this->addWand(new CylinderBaseCenterWand);
		$this->addWand(new CylinderCircumProjectionWand(true, true));
		$this->addWand(new CylinderCircumProjectionWand(true, false));
		$this->addWand(new CylinderCircumProjectionWand(false, true));
		$this->addWand(new CylinderCircumProjectionWand(false, false));
		$this->addWand(new CylinderCircumRotateWand(true, true));
		$this->addWand(new CylinderCircumRotateWand(true, false));
		$this->addWand(new CylinderCircumRotateWand(false, true));
		$this->addWand(new CylinderCircumRotateWand(false, false));
		$this->addWand(new SphereCenterWand);
		$this->addWand(new SphereRadiusWand(SphereRadiusWand::AXIS_ALL));
		$this->addWand(new SphereRadiusWand(SphereRadiusWand::AXIS_X));
		$this->addWand(new SphereRadiusWand(SphereRadiusWand::AXIS_Y));
		$this->addWand(new SphereRadiusWand(SphereRadiusWand::AXIS_Z));
	}

	public function addWand(Wand $wand) : void{
		$this->wands[$wand->getName()] = $wand;
		$this->plugin->getServer()->getCommandMap()->register("wand",
			$this->cmds[] = new WandCommand($this->plugin, $wand));
	}

	public function getCommands() : array{
		return $this->cmds;
	}

	public function getWand(string $wandName) : Wand{
		return $this->wands[$wandName];
	}

	/**
	 * @return Wand[]
	 */
	public function getWands() : array{
		return $this->wands;
	}
}
