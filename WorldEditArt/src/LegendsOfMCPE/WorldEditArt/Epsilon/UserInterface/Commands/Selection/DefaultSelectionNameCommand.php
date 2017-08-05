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
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\Session\SessionCommand;
use LegendsOfMCPE\WorldEditArt\Epsilon\WorldEditArt;

class DefaultSelectionNameCommand extends SessionCommand{
	public function __construct(WorldEditArt $plugin){
		parent::__construct($plugin, "/selname", "Change your default selection", "/seln [defaultSelectionName]", ["/seln"], Consts::PERM_SELECT_DEFAULT, [
			"default" => [
				[
					"type" => "string",
					"name" => "defaultSelectionName",
					"optional" => true,
				],
			],
		]);
	}

	public function run(BuilderSession $session, array $args){
		if(isset($args[0])){
			$session->setDefaultSelectionName($args[0]);
			$session->msg("Set your default selection to $args[0]", BuilderSession::MSG_CLASS_SUCCESS);
		}else{
			$session->msg("Your default selection is $args[0]");
		}
	}
}
