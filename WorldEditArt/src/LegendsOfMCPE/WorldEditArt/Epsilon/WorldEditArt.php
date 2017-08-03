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

use LegendsOfMCPE\WorldEditArt\Epsilon\LibgeomAdapter\ShapeWrapper;
use LegendsOfMCPE\WorldEditArt\Epsilon\Selection\Wand\WandManager;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\WorldEditArtCommand;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\PlayerBuilderSession;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\PlayerEventListener;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Binary;
use sofe\libgeom\LibgeomBinaryStream;
use sofe\libgeom\UnsupportedOperationException;
use spoondetector\SpoonDetector;

class WorldEditArt extends PluginBase{
	/** @var ConstructionZone[] */
	private $constructionZones;

	/** @var BuilderSession[][]|\SplObjectStorage <CommandSender -> BuilderSession[]> */
	private $builderSessionMap;
	/** @var WandManager */
	private $wandManager;

	public function onEnable(){
		SpoonDetector::printSpoon($this);
		$this->saveDefaultConfig();
		$this->getConfig();
		$this->loadConstructionZones();

		$this->builderSessionMap = new \SplObjectStorage();
		$this->wandManager = new WandManager($this);

		WorldEditArtCommand::registerAll($this);
		new PlayerEventListener($this);
	}

	public function onDisable(){
		if(isset($this->constructionZones)){
			$this->saveConstructionZones();
		}
	}

	public function onLoad(){
		if(!class_exists(LibgeomBinaryStream::class)){
			throw new \ClassNotFoundException("WorldEditArt-Epsilon was compiled without libgeom v2");
		}
	}

	public static function getInstance(Server $server) : WorldEditArt{
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $server->getPluginManager()->getPlugin(Consts::PLUGIN_NAME);
	}


	private function loadConstructionZones(){
		if(is_file($fn = $this->getDataFolder() . "constructionZones.dat")){
			try{
				$stream = new LibgeomBinaryStream(file_get_contents($fn));
				if($stream->getShort() !== 1){
					throw new UnsupportedOperationException("Unsupported constructionZones.dat version");
				}
				$count = $stream->getUnsignedVarInt();
				$this->constructionZones = [];
				for($i = 0; $i < $count; ++$i){
					$name = $stream->getString();
					/** @var string|\sofe\libgeom\Shape $class */
					$class = $stream->getString();
					$shape = $class::fromBinary($this->getServer(), $stream);
					$wrappedShape = new ShapeWrapper($shape);
					$this->constructionZones[mb_strtolower($name)] = new ConstructionZone($name, $wrappedShape);
				}
			}catch(\UnderflowException $e){
				$this->getLogger()->error("Corrupted constructionZones.dat, resetting to empty...");
				file_put_contents($fn, Binary::writeUnsignedVarInt(0));
				$this->constructionZones = [];
			}
		}else{
			$this->constructionZones = [];
		}
	}

	private function saveConstructionZones(){
		$stream = new LibgeomBinaryStream();
		$stream->putByte(1);
		$stream->putUnsignedVarInt(count($this->constructionZones));
		foreach($this->constructionZones as $zone){
			$shape = $zone->getShape();
			$stream->putString($zone->getName());
			$stream->putString(get_class($shape));
			$shape->toBinary($stream);
		}
		file_put_contents($this->getDataFolder() . "constructionZones.dat", $stream->getBuffer());
	}

	/**
	 * Returns all active construction zones on the server
	 *
	 * The keys of the array are the names of the construction zones in lowercase. The case-preserved name can be obtained from
	 * {@see ConstructionZone::getName()}
	 *
	 * @return ConstructionZone[]
	 */
	public function getConstructionZones() : array{
		return $this->constructionZones;
	}


	public function getWandManager() : WandManager{
		return $this->wandManager;
	}


	/**
	 * Starts a builder session for the player
	 *
	 * @param Player $player
	 *
	 * @return PlayerBuilderSession
	 */
	public function startPlayerSession(Player $player) : PlayerBuilderSession{
		if(!isset($this->builderSessionMap[$player])){
			$this->builderSessionMap[$player] = [];
		}
		$this->builderSessionMap[$player][PlayerBuilderSession::SESSION_KEY] = $session
			= new PlayerBuilderSession($this, $player);
		return $session;
	}

	/**
	 * Closes <em>only</em> the player builder session (non-minion) of the player.
	 *
	 * @param Player $player
	 */
	public function closePlayerSession(Player $player){
		$this->builderSessionMap[$player][PlayerBuilderSession::SESSION_KEY]->close();
		unset($this->builderSessionMap[$player][PlayerBuilderSession::SESSION_KEY]);
	}

	/**
	 * Returns all open builder sessions (including both implicit/explicit and minion sessions) of the command sender.
	 *
	 * @param CommandSender $sender
	 *
	 * @return BuilderSession[]
	 */
	public function getSessionsOf(CommandSender $sender) : array{
		return $this->builderSessionMap[$sender] ?? [];
	}

	/**
	 * Closes all open builder sessions (including both implicit/explicit and minion sessions) of the command sender.
	 *
	 * @param CommandSender $sender
	 */
	public function closeSessions(CommandSender $sender){
		if(isset($this->builderSessionMap[$sender])){
			foreach($this->builderSessionMap[$sender] as $session){
				$session->close();
			}
			unset($this->builderSessionMap[$sender]);
		}
	}
}
