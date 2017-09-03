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

namespace LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands;

use LegendsOfMCPE\WorldEditArt\Epsilon\Selection\Wand\WandCommand;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\ConstructionZone\ConstructionZoneCommand;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\Manipulation\ReplaceCommand;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\Manipulation\SetCommand;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\Selection\DefaultSelectionNameCommand;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\Selection\DeselectCommand;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\Selection\Edit\CuboidCommand;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\Selection\Edit\CylinderCommand;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\Selection\Edit\SphereCommand;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\Selection\ShowSelectionCommand;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\Session\AtCommand;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\Session\BookmarkCommand;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\Session\ManageSessionsCommand;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\Session\WandConfigCommand;
use LegendsOfMCPE\WorldEditArt\Epsilon\WorldEditArt;
use pocketmine\command\Command;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\plugin\Plugin;
use stdClass;

abstract class WorldEditArtCommand extends Command implements PluginIdentifiableCommand{
	/** @var WorldEditArt */
	private $plugin;
	/** @var array[] */
	private $formats;

	public function __construct(WorldEditArt $plugin, string $name, string $description = "", string $usageMessage = null, array $aliases = [], string $permission = null, array $formats = ["default" => []]){
		assert($name{0} === "/");
		parent::__construct($name, $description, $usageMessage, $aliases);
		$this->plugin = $plugin;
		$this->setPermission($permission);
		if(count($formats) > 0){
			$arr = new stdClass();
			foreach($formats as $formatName => $format){
				$arr->{$formatName} = [
					"input" => ["parameters" => $format],
					"output" => new stdClass(),
				];
			}
			$this->commandData["overloads"] = $arr;
		}
		$this->formats = $formats;
	}

	/**
	 * @return Plugin|WorldEditArt
	 */
	public function getPlugin() : Plugin{
		return $this->plugin;
	}

	public function getFormats(){
		return $this->formats;
	}

	/**
	 * @param WorldEditArt  $plugin
	 * @param WandCommand[] $cmds Wand commands
	 */
	public static function registerAll(WorldEditArt $plugin, array $cmds){
		// session commands except //@
		$cmds[] = new ConstructionZoneCommand($plugin);
		$cmds[] = new BookmarkCommand($plugin);
		$cmds[] = new ShowSelectionCommand($plugin);
		$cmds[] = new DeselectCommand($plugin);
		$cmds[] = new DefaultSelectionNameCommand($plugin);
		$cmds[] = new CuboidCommand($plugin);
		$cmds[] = new CylinderCommand($plugin);
		$cmds[] = new SphereCommand($plugin);
		$cmds[] = new WandConfigCommand($plugin);
		$cmds[] = new SetCommand($plugin);
		$cmds[] = new ReplaceCommand($plugin);
		// then //@
		$at = new AtCommand($plugin, $cmds);
		$cmds[] = $at;
		// then other commands
		$cmds[] = new WeaStatusCommand($plugin);
		$cmds[] = new ManageSessionsCommand($plugin);
		$plugin->getServer()->getCommandMap()->registerAll("wea", $cmds);
	}
}
