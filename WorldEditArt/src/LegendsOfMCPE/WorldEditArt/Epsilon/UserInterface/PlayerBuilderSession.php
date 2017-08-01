<?php

/*
 *
 * WorldEditArt-Epsilon
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

namespace LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface;

use LegendsOfMCPE\WorldEditArt\Epsilon\BuilderSession;
use LegendsOfMCPE\WorldEditArt\Epsilon\WorldEditArt;
use pocketmine\command\CommandSender;
use pocketmine\level\Location;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class PlayerBuilderSession extends BuilderSession{
	const SESSION_KEY = "std";

	/** @var Player */
	private $player;

	public function __construct(WorldEditArt $plugin, Player $player){
		parent::__construct($plugin);
		$this->player = $player;
	}

	public function getPlayer() : Player{
		return $this->player;
	}

	public function getOwner() : CommandSender{
		return $this->player;
	}

	public function getUniqueId() : string{
		return "player;" . mb_strtolower($this->player->getName());
	}

	protected function getRealLocation() : Location{
		return $this->player->asLocation();
	}

	public function msg(string $message, int $class = BuilderSession::MSG_CLASS_INFO, string $title = null){
		$color = BuilderSession::MSG_CLASS_COLOR_MAP[$class];
		if($class === BuilderSession::MSG_CLASS_LOADING || $class === BuilderSession::MSG_CLASS_UPDATE){
			if(isset($title)){
				$this->player->sendPopup($color . $title, $color . $message);
			}else{
				$this->player->sendPopup($color . $message);
			}
		}elseif($class === BuilderSession::MSG_CLASS_SUCCESS){
			$this->player->sendTip((isset($title) ? (TextFormat::BOLD . $color . $message . TextFormat::RESET . "\n") : "") . $color . $message);
		}else{
			if(isset($title)){
				$this->player->sendMessage(TextFormat::BOLD . $color . $title);
			}
			$this->player->sendMessage($color . $message);
		}
	}
}
