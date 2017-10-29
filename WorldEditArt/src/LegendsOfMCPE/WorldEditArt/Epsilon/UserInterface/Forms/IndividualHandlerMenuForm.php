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

use LegendsOfMCPE\WorldEditArt\Epsilon\Utils\WEAUtils;
use pocketmine\form\Form;
use pocketmine\form\MenuForm;
use pocketmine\Player;

class IndividualHandlerMenuForm extends MenuForm{
	/**
	 * CallbackMenuForm constructor.
	 * @param string                        $title
	 * @param string                        $text
	 * @param IndividualHandlerMenuOption[] $options
	 */
	public function __construct(string $title, string $text, array $options){
		WEAUtils::validateArrayType($options, IndividualHandlerMenuOption::class);
		parent::__construct($title, $text, ...$options);
	}

	public function onSubmit(Player $player) : ?Form{
		$option = $this->getSelectedOption();
		assert($option instanceof IndividualHandlerMenuOption);
		return $option->onClick($this, $player);
	}
}
