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
use pocketmine\form\FormIcon;
use pocketmine\form\MenuOption;
use pocketmine\Player;

class FormOpenMenuOption extends MenuOption implements IndividualHandlerMenuOption{
	/** @var WorldEditArt */
	private $plugin;
	/** @var string|IndexedForm */
	private $class;

	public function __construct(WorldEditArt $plugin, string $class, string $text, ?FormIcon $image = null){
		$this->plugin = $plugin;
		assert(is_subclass_of($class, IndexedForm::class));
		$this->class = $class;
		parent::__construct($text, $image);
	}

	public function onClick(Form $form, Player $player) : ?Form{
		$form = new ($this->class)($this->plugin, $player, $form);
		return $form;
	}
}
