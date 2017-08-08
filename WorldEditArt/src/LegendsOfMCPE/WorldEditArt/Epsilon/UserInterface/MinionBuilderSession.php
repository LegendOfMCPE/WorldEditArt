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

class MinionBuilderSession extends BuilderSession{
	/** @var CommandSender */
	private $owner;
	/** @var string */
	private $name;
	/** @var Location */
	private $location;

	public function __construct(WorldEditArt $plugin, CommandSender $owner, string $name, Location $location){
		parent::__construct($plugin);
		$this->owner = $owner;
		$this->name = $name;
		$this->location = $location;
	}

	public function getOwner() : CommandSender{
		return $this->owner;
	}

	public function getUniqueId() : string{
		return "Minion:" . get_class($this->owner) . ":" . $this->owner->getName() . ":" . $this->name;
	}

	protected function getRealLocation() : Location{
		return $this->location;
	}

	public function setLocation(Location $location){
		$this->location = $location;
	}

	public function msg(string $message, int $class = BuilderSession::MSG_CLASS_INFO, string $title = null){
		if($title !== null){
			parent::msg($message, $class, "[Minion $this->name] " . $title);
		}else{
			parent::msg("[Minion $this->name] " . $message, $class);
		}
	}
}
