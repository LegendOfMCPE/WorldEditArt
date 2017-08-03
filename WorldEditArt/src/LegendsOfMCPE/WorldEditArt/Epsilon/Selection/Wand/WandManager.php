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

use LegendsOfMCPE\WorldEditArt\Epsilon\WorldEditArt;

class WandManager{
	private $plugin;
	private $wands = [];

	public function __construct(WorldEditArt$plugin){
		$this->plugin = $plugin;
	}

	public function addWand(Wand $wand){
		$this->wands[$wand->getName()] = $wand;
		$this->plugin->getServer()->getCommandMap()->register("wand", new WandCommand($this->plugin, $wand));
	}
}
