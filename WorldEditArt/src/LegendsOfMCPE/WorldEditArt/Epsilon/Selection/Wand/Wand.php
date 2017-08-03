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
use pocketmine\level\Position;

interface Wand{
	public function getName() : string;

	/**
	 * @return string[] without leading slashes
	 */
	public function getAliases() : array;

	public function getPermission() : string;

	/**
	 * Executes the wand at the specified position, also responsible for generating user feedback
	 *
	 * @param BuilderSession $session
	 * @param Position       $position
	 * @param string         $selectionName
	 */
	public function execute(BuilderSession $session, Position $position, string $selectionName);
}
