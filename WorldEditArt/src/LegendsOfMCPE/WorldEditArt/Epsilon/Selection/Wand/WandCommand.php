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

namespace LegendsOfMCPE\WorldEditArt\Epsilon\Selection\Wand;

use LegendsOfMCPE\WorldEditArt\Epsilon\BuilderSession;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\Session\SessionCommand;
use LegendsOfMCPE\WorldEditArt\Epsilon\WorldEditArt;

class WandCommand extends SessionCommand{
	/**
	 * @var Wand
	 */
	private $wand;

	public function __construct(WorldEditArt $plugin, Wand $wand){
		$this->wand = $wand;
		parent::__construct($plugin, "/{$wand->getName()}", "Click the {$wand->getName()} wand at your position", "//{$wand->getName()} [selectionName]", array_map(function($alias){
			return "/$alias";
		}, $wand->getAliases()), $wand->getPermission(), [
			"default" => [
				[
					"type" => "string",
					"name" => "selectionName",
					"optional" => true,
				],
			],
		]);
	}

	public function run(BuilderSession $session, array $args){
		$this->wand->execute($session, $session->getLocation()->asPosition(), $args[0] ?? $session->getDefaultSelectionName());
	}
}
