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

namespace LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\Session;

use LegendsOfMCPE\WorldEditArt\Epsilon\BuilderSession;
use LegendsOfMCPE\WorldEditArt\Epsilon\Session\PlayerBuilderSession;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\WorldEditArtCommand;
use pocketmine\command\CommandSender;
use pocketmine\Player;

/**
 * SessionCommand is the superclass of all commands executing actions based on a builder session.
 */
abstract class SessionCommand extends WorldEditArtCommand{
	public function execute(CommandSender $sender, string $commandLabel, array $args) : void{
		if(!$this->testPermission($sender)){
			return;
		}
		if(!($sender instanceof Player)){
			$sender->sendMessage("Please either run this command in-game, or use //mi to execute this command.");
			return;
		}
		$sessions = $this->getPlugin()->getSessionsOf($sender);
		if(!isset($sessions[PlayerBuilderSession::SESSION_KEY])){
			$sender->sendMessage("Please start a builder session with `//session start` to use this command.");
			return;
		}
		$args = array_filter($args, function($value){
			return $value !== "";
		});
		$this->run($sessions[PlayerBuilderSession::SESSION_KEY], $args);
	}

	/**
	 * @param BuilderSession $session
	 * @param array          $args
	 *
	 * @return void
	 */
	public abstract function run(BuilderSession $session, array $args) : void;

	public function sendUsage(BuilderSession $session, int $class = BuilderSession::MSG_CLASS_ERROR) : void{
		$session->msg("Usage: " . $this->getUsage(), $class);
	}
}
