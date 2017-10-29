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

use LegendsOfMCPE\WorldEditArt\Epsilon\Session\PlayerBuilderSession;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Forms\IndexedForm;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\UserFormat;
use LegendsOfMCPE\WorldEditArt\Epsilon\WorldEditArt;
use pocketmine\form\CustomForm;
use pocketmine\form\element\Label;
use pocketmine\form\Form;
use pocketmine\form\FormIcon;
use pocketmine\Player;

class ShowSelectionsForm extends CustomForm implements IndexedForm{
	public function __construct(WorldEditArt $plugin, Player $player, ?Form $parent = null){
		$session = $plugin->getSessionsOf($player)[PlayerBuilderSession::SESSION_KEY];
		if($session === null){
			parent::__construct("Your Selections", new Label("Please start your WorldEditArt session first"));
			return;
		}
		$elements = [];
		foreach($session->getSelections() as $selName => $shape){
			$elements[] = new Label($selName . ": " . UserFormat::describeShape($plugin->getServer(), $shape, UserFormat::FORMAT_USER_DEFINITION));// TODO implement preview
		}
		parent::__construct("Your Selections", ...$elements);
	}
}
