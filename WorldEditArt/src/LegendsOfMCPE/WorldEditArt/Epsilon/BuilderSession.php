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

namespace LegendsOfMCPE\WorldEditArt\Epsilon;

use LegendsOfMCPE\WorldEditArt\Epsilon\Session\WandTrigger;
use pocketmine\command\CommandSender;
use pocketmine\level\Location;
use pocketmine\utils\TextFormat;

abstract class BuilderSession{
	const MSG_CLASS_LOADING = 1;
	const MSG_CLASS_UPDATE = 2;
	const MSG_CLASS_INFO = 3;
	const MSG_CLASS_SUCCESS = 4;
	const MSG_CLASS_WARN = 5;
	const MSG_CLASS_ERROR = 6;

	const MSG_CLASS_COLOR_MAP = [
		BuilderSession::MSG_CLASS_LOADING => TextFormat::DARK_GRAY,
		BuilderSession::MSG_CLASS_UPDATE => TextFormat::GRAY,
		BuilderSession::MSG_CLASS_INFO => TextFormat::WHITE,
		BuilderSession::MSG_CLASS_SUCCESS => TextFormat::GREEN,
		BuilderSession::MSG_CLASS_WARN => TextFormat::YELLOW,
		BuilderSession::MSG_CLASS_ERROR => TextFormat::RED,
	];

	/** @var WorldEditArt */
	private $plugin;

	/** @var Location|null */
	private $overridingLocation = null;

	/** @var Location[] */
	private $bookmarks = [];
	/** @var IShape[] */
	private $selections = [];
	/** @var string */
	private $defaultSelectionName = "default";
	/** @var bool */
	private $wandEnabled = true;
	/** @var WandTrigger[] */
	private $wandTriggers = [];

	public function __construct(WorldEditArt $plugin){
		$this->plugin = $plugin;
		// TODO load bookmarks
		// TODO load selections
		// TODO load wand triggers
	}

	public function close(){
		// TODO save bookmarks
		// TODO save selections
		// TODO save wand triggers
		foreach($this->plugin->getConstructionZones() as $zone){
			if($zone->getLockingSession() === $this){
				$zone->unlock();
			}
		}
	}


	public abstract function getOwner() : CommandSender;

	public abstract function getUniqueId() : string;

	protected abstract function getRealLocation() : Location;

	public function hasPermission(string $permission) : bool{
		return $this->getOwner()->hasPermission($permission);
	}

	public function isAvailable() : bool{
		return true;
	}

	public function getLocation() : Location{
		return ($this->overridingLocation ?? $this->getRealLocation())->asLocation();
	}

	public function executeAtLocation(Location $location, callable $function){
		$old = $this->overridingLocation;
		$this->overridingLocation = $location;
		$function();
		$this->overridingLocation = $old;
	}

	public function msg(string $message, int $class = BuilderSession::MSG_CLASS_INFO, string $title = null){
		if($title !== null){
			$this->getOwner()->sendMessage(TextFormat::BOLD . BuilderSession::MSG_CLASS_COLOR_MAP[$class] . $title);
		}
		foreach(explode("\n", $message) as $line){
			$this->getOwner()->sendMessage(BuilderSession::MSG_CLASS_COLOR_MAP[$class] . $line);
		}
	}

	public function getPlugin() : WorldEditArt{
		return $this->plugin;
	}


	/**
	 * @return Location[]
	 */
	public function getBookmarks() : array{
		return $this->bookmarks;
	}

	/**
	 * @param string $name
	 *
	 * @return null|Location
	 */
	public function getBookmark(string $name){
		return $this->bookmarks[mb_strtolower($name)] ?? null;
	}

	public function hasBookmark(string $name) : bool{
		return isset($this->bookmarks[mb_strtolower($name)]);
	}

	public function setBookmark(string $name, Location $location){
		$this->bookmarks[mb_strtolower($name)] = $location;
	}

	public function removeBookmark(string $name){
		unset($this->bookmarks[mb_strtolower($name)]);
	}


	/**
	 * @return IShape[]
	 */
	public function getSelections() : array{
		return $this->selections;
	}

	/**
	 * @param string $name
	 *
	 * @return IShape|null
	 */
	public function getSelection(string $name){
		return $this->selections[mb_strtolower($name)] ?? null;
	}

	public function hasSelection(string $name) : bool{
		return isset($this->selections[mb_strtolower($name)]);
	}

	public function setSelection(string $name, IShape $shape){
		$this->selections[mb_strtolower($name)] = $shape;
	}

	public function removeSelection(string $name){
		unset($this->selections[mb_strtolower($name)]);
	}

	public function getDefaultSelectionName() : string{
		return $this->defaultSelectionName;
	}

	public function setDefaultSelectionName(string $defaultSelectionName){
		$this->defaultSelectionName = $defaultSelectionName;
	}

	public function isWandEnabled() : bool{
		return $this->wandEnabled;
	}

	public function setWandEnabled(bool $wandEnabled){
		$this->wandEnabled = $wandEnabled;
	}

	public function addWandTrigger(WandTrigger $trigger){
		$this->wandTriggers[$trigger->getClickId()] = $trigger;
	}

	public function getWandTrigger(int $itemId, int $actionType){
		return $this->wandTriggers[WandTrigger::clickId($itemId, $actionType)] ?? null;
	}

	/**
	 * @return WandTrigger[]
	 */
	public function getWandTriggers() : array{
		return $this->wandTriggers;
	}
}
