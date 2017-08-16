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

namespace LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\MultiLang;

use pocketmine\command\CommandSender;

class LangProvider{
	/** @var CommandSender */
	private $sender;
	/** @var LangConfig */
	private $config;

	public function __construct(CommandSender $sender, LangConfig $config){
		$this->sender = $sender;
		$this->config = $config;
	}

	public function getSender() : CommandSender{
		return $this->sender;
	}

	public function getConfig() : LangConfig{
		return $this->config;
	}


	public function generic_noPermission(){

	}
}
