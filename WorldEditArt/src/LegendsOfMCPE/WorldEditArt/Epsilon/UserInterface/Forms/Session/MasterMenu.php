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
use LegendsOfMCPE\WorldEditArt\Epsilon\Session\PlayerBuilderSession;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Forms\CallbackMenuOption;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Forms\FormOpenMenuOption;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Forms\IndexedForm;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Forms\IndividualHandlerMenuForm;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Forms\Selection\ShowSelectionsForm;
use LegendsOfMCPE\WorldEditArt\Epsilon\WorldEditArt;
use pocketmine\form\Form;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class MasterMenu extends IndividualHandlerMenuForm implements IndexedForm{
	/** @var WorldEditArt */
	private $plugin;
	/** @var Player */
	private $player;

	public function __construct(WorldEditArt $plugin, Player $player, ?Form $parent = null){
		$options = [];
		$session = $plugin->getSessionsOf($player)[PlayerBuilderSession::SESSION_KEY] ?? null;
		if($session === null){
			if($player->hasPermission(Consts::PERM_SESSION_START)){
				if($plugin->getConfig()->get(Consts::CONFIG_SESSION_GLOBAL_PASSPHRASE)){
					$options[] = new CallbackMenuOption("Start WorldEditArt session", [$this, "execOpenSession"]);
				}else{
					$options[] = new FormOpenMenuOption($plugin, StartSessionForm::class, "Start WorldEditArt session");
				}
			}
		}else{
			$options[] = new FormOpenMenuOption($plugin, ShowSelectionsForm::class, "Selections");
			if($player->hasPermission(Consts::PERM_SESSION_START)){
				$options[] = new CallbackMenuOption("Close WorldEditArt session", [$this, "execCloseSession"]);
			}
		}
		if($player->hasPermission(Consts::PERM_STATUS)){
			$options[] = new FormOpenMenuOption($plugin, StatusForm::class, "Status and Info");
		}
		parent::__construct($plugin->getFullName(), "", ...$options);
		$this->plugin = $plugin;
		$this->player = $player;
	}

	public function execOpenSession(){
		$this->plugin->startPlayerSession($this->player);
		$this->player->sendMessage(TextFormat::GREEN . "Started builder session");
	}

	public function execCloseSession(){
		$this->plugin->closePlayerSession($this->player);
		$this->player->sendMessage(TextFormat::GREEN . "Your session has been closed");
	}
}
