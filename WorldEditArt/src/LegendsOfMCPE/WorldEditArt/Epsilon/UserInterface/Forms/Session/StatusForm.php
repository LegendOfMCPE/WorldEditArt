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

namespace LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Forms\Session;

use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Forms\IndexedForm;
use LegendsOfMCPE\WorldEditArt\Epsilon\WorldEditArt;
use pocketmine\form\CustomForm;
use pocketmine\form\element\Label;
use pocketmine\form\Form;
use pocketmine\Player;

class StatusForm extends CustomForm implements IndexedForm{
	public function __construct(WorldEditArt $plugin, Player $player, ?Form $form = null){
		parent::__construct("WorldEditArt Info",
			new Label("Version: " . $plugin->getFullName() . " by " . implode(", ", $plugin->getDescription()->getAuthors())),
			new Label("Find more information at " . $plugin->getDescription()->getWebsite()));
	}
}
