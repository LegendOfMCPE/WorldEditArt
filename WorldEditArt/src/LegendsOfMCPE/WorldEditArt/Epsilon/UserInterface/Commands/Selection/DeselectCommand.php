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

class DeselectCommand extends SessionCommand{
	public function __construct(WorldEditArt $plugin){
		parent::__construct($plugin, "/desel", "Deselect a selection", "//desel [name]", ["/deselect"], Consts::PERM_SELECT_DESEL, [
			"default" => [
				[
					"name" => "selectionName",
					"type" => "string",
					"optional" => true
				]
			]
		]);
	}

	public function run(BuilderSession $session, array $args){
		if(!$session->hasSelection($name = $args[0] ?? BuilderSession::DEFAULT_SELECTION_NAME)){
			$session->msg("You don't have a selection called \"$name\"", BuilderSession::MSG_CLASS_ERROR);
			return;
		}
		$session->removeSelection($name);
		$session->msg("Removed selection \"$name\"", BuilderSession::MSG_CLASS_SUCCESS);
	}
}
