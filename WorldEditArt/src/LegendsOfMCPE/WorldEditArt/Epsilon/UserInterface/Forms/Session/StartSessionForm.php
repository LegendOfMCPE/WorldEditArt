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

use LegendsOfMCPE\WorldEditArt\Epsilon\Consts;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Forms\IndexedForm;
use LegendsOfMCPE\WorldEditArt\Epsilon\WorldEditArt;
use pocketmine\form\CustomForm;
use pocketmine\form\element\Input;
use pocketmine\form\Form;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class StartSessionForm extends CustomForm implements IndexedForm{
	/** @var WorldEditArt */
	private $plugin;

	/** @var Input */
	private $passphraseField;

	public function __construct(WorldEditArt $plugin, Player $player, ?Form $parent = null){
		$this->plugin = $plugin;
		$elements = [
			$this->passphraseField = new Input("WorldEditArt session global passphrase", "Leave empty if there is none")
		];
		parent::__construct("Start WorldEditArt session", ...$elements);
	}

	public function onSubmit(Player $player) : ?Form{
		if($this->passphraseField->getValue() === (string) $this->plugin->getConfig()->get(Consts::CONFIG_SESSION_GLOBAL_PASSPHRASE)){
			$this->plugin->startPlayerSession($player);
			$player->sendMessage(TextFormat::GREEN . "Started builder session");
		}else{
			$player->sendMessage(TextFormat::RED . "Wrong passphrase");
		}
		return null;
	}
}
