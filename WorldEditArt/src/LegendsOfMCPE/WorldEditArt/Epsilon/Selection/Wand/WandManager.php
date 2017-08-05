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
use LegendsOfMCPE\WorldEditArt\Epsilon\WorldEditArt;

class WandManager{
	private $plugin;
	private $wands = [];
	private $cmds = [];

	public function __construct(WorldEditArt $plugin){
		$this->plugin = $plugin;

		$this->addWand(new CuboidPosWand(true));
		$this->addWand(new CuboidPosWand(false));
	}

	public function addWand(Wand $wand){
		$this->wands[$wand->getName()] = $wand;
		$this->plugin->getServer()->getCommandMap()->register("wand",
			$this->cmds[] = new WandCommand($this->plugin, $wand));
	}

	public function getCommands() : array{
		return $this->cmds;
	}
}
