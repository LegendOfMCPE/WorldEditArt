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

namespace LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Forms;

use LegendsOfMCPE\WorldEditArt\Epsilon\WorldEditArt;
use pocketmine\form\Form;
use pocketmine\Player;

interface IndexedForm{
	public function __construct(WorldEditArt $plugin, Player $player, ?Form $parent = null);
}
