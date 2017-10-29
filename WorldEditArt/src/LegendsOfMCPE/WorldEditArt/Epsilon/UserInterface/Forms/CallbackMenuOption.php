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

use pocketmine\form\Form;
use pocketmine\form\FormIcon;
use pocketmine\form\MenuOption;
use pocketmine\Player;

class CallbackMenuOption extends MenuOption implements IndividualHandlerMenuOption{
	/** @var callable */
	private $callback;

	public function __construct(string $text, callable $callback, ?FormIcon $image = null){
		parent::__construct($text, $image);
		$this->callback = $callback;
	}

	public function onClick(Form $form, Player $player) : ?Form{
		return ($this->callback)($player, $form);
	}
}
