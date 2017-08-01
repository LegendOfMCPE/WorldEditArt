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

namespace LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\Selection;

use LegendsOfMCPE\WorldEditArt\Epsilon\BuilderSession;
use LegendsOfMCPE\WorldEditArt\Epsilon\Consts;
use LegendsOfMCPE\WorldEditArt\Epsilon\IShape;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\Session\SessionCommand;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\UserFormat;
use LegendsOfMCPE\WorldEditArt\Epsilon\WorldEditArt;
use pocketmine\utils\TextFormat;

class ShowSelectionCommand extends SessionCommand{
	public function __construct(WorldEditArt $plugin){
		parent::__construct($plugin, "/sels", "Show your selections", "//sels [name]", ["/sellist"], Consts::PERM_SELECT_SHOW, [
			"default" => [
				[
					"name" => "selectionName",
					"type" => "string",
					"optional" => true,
				]
			]
		]);
	}

	public function run(BuilderSession $session, array $args){
		if(isset($args[0])){
			if(!$session->hasSelection($name = $args[0])){
				$session->msg("You don't have a selection called $name. Use //sels to show all selections", BuilderSession::MSG_CLASS_ERROR);
				return;
			}
			$this->showSelection($session, $name, $session->getSelection($name));
			return;
		}
		$sels = $session->getSelections();
		foreach($sels as $name => $sel){
			$this->showSelection($session, $name, $sel);
		}
	}

	private function showSelection(BuilderSession $session, string $name, IShape $shape, int $class = BuilderSession::MSG_CLASS_INFO){
		$session->msg(UserFormat::describeShape($shape, UserFormat::FORMAT_USER_DEFINITION), $class, "Selection " . TextFormat::AQUA . "\"$name\"");
	}
}
