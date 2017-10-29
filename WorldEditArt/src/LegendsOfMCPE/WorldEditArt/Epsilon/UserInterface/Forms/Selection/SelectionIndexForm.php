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

namespace LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Forms\Selection;

use LegendsOfMCPE\WorldEditArt\Epsilon\Consts;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Forms\FormOpenMenuOption;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Forms\IndexedForm;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Forms\IndividualHandlerMenuForm;
use LegendsOfMCPE\WorldEditArt\Epsilon\WorldEditArt;
use pocketmine\form\Form;
use pocketmine\Player;

class SelectionIndexForm extends IndividualHandlerMenuForm implements IndexedForm{
	public function __construct(WorldEditArt $plugin, Player $player, ?Form$parent = null){
		$options = [];
		if($player->hasPermission(Consts::PERM_SELECT_SHOW)){
			$options[] = new FormOpenMenuOption($plugin, ShowSelectionsForm::class, "Show All Selections");
		}
		parent::__construct("Manage Selections", "", $options);
	}
}
