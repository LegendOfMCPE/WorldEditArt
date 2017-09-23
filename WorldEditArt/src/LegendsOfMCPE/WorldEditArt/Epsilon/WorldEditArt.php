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


use LegendsOfMCPE\WorldEditArt\Epsilon\Manipulation\Changer\BlockChanger;
use LegendsOfMCPE\WorldEditArt\Epsilon\Manipulation\SerializableBlockStreamGetter;
use LegendsOfMCPE\WorldEditArt\Epsilon\Selection\Wand\WandManager;
use LegendsOfMCPE\WorldEditArt\Epsilon\Session\PlayerBuilderSession;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\WorldEditArtCommand;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\PlayerEventListener;
use pocketmine\command\CommandSender;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;
use pocketmine\Server;
use sofe\libgeom\LibgeomMathUtils;
use sofe\pschemlib\SchematicFile;
use sofe\toomuchbuffer\Closeable;
use spoondetector\SpoonDetector;

class WorldEditArt extends PluginBase{
	/** @var array */
	private $metadata;
	/** @var string[] */
	private $libs;
	/** @var ConstructionZoneManager */
	private $czManager;
	/** @var BuilderSession[][] */
	private $builderSessionMap;
	/** @var WandManager */
	private $wandManager;


	public static function requireVersion(Server $server, int $edition, int $major, int $minor){
		$instance = WorldEditArt::getInstance($server);
		[$a, $b, $c] = array_map("intval", explode(".", $instance->getDescription()->getVersion()));
		if(!($a === $edition && $major === $b && $minor < $c)){
			throw new PluginException("Depends on unsupported WorldEditArt version (provided $a.$b.$c, dependent uses $edition.$major.$minor)");
		}
	}


	public function getConstructionZoneManager() : ConstructionZoneManager{
		return $this->czManager;
	}

	public function getWandManager() : WandManager{
		return $this->wandManager;
	}

	public function getCacheFolder() : string{
		return $this->getDataFolder() . "cache" . DIRECTORY_SEPARATOR;
	}


	/** @noinspection SpellCheckingInspection */
	public function onEnable(){
		if(PHP_MAJOR_VERSION !== 7 || PHP_MINOR_VERSION < 2){
			/** @noinspection SpellCheckingInspection */
			$this->getLogger()->critical(base64_decode("V29ybGRFZGl0QXJ0LUVwc2lsb24gb25seSBzdXBwb3J0cyBQSFAgNy4yIG9yIGFib3ZlLiBQbGVhc2UgdXBncmFkZSB5b3VyIFBIUCBiaW5hcmllcyBhbmQgUG9ja2V0TWluZSB2ZXJzaW9uLiBTZWUgdGhpcyB0d2VldCAoYW5kIGl0cyByZXBsaWVzKSBmb3IgZGV0YWlsczogaHR0cHM6Ly90d2l0dGVyLmNvbS9ka3RhcHBzL3N0YXR1cy85MDMzNTk4ODk3OTEyMTM1Njg="));
			return;
		}
		if(!\Phar::running()){
			/** @noinspection SpellCheckingInspection */
			$this->getLogger()->critical(base64_decode("RG8gbm90IHJ1biBXb3JsZEVkaXRBcnQtRXBzaWxvbiBmcm9tIHNvdXJjZS4="));
			return;
		}
		if(!$this->getServer()->getConfigBoolean("worldeditart.allow-non-poggit")){
			$phar = new \Phar(\Phar::running(false));
			$this->metadata = $phar->getMetadata();
			if(is_array($this->metadata) && isset($this->metadata["builderName"]) && $this->metadata["builderName"] === "poggit" && $this->metadata["buildClass"] === "Dev" && $this->metadata["projectId"] === 724){
				$this->saveDefaultConfig();
			}else{
				/** @noinspection SpellCheckingInspection */
				$this->getLogger()->critical(base64_decode("UGxlYXNlIG9ubHkgdXNlIERldiBCdWlsZHMgb2YgV29ybGRFZGl0QXJ0IGRvd25sb2FkZWQgZnJvbSBQb2dnaXQuCg=="));
				return;
			}
		}
		if(is_file($this->getFile() . "virus-infections.json")){
			$data = json_decode(file_get_contents($this->getFile() . "virus-infections.json"));
			assert(is_array($data));
			foreach($data as $lib){
				$this->libs[$lib->name] = $lib->version;
			}
		}else{
			$this->getLogger()->critical("WorldEditArt has not been infected by virions yet! This is an invalid build!");
			return;
		}
		if(!isset($this->libs["libgeom"]) || !class_exists(LibgeomMathUtils::class)){
			throw new \ClassNotFoundException("WorldEditArt-Epsilon was compiled without libgeom v2");
		}
		if(!isset($this->libs["pschemlib"]) || !class_exists(SchematicFile::class)){
			throw new \ClassNotFoundException("WorldEditArt-Epsilon was compiled without pschemlib v0");
		}
		if(!isset($this->libs["SpoonDetector"]) || !class_exists(SpoonDetector::class)){
			throw new \ClassNotFoundException("WorldEditArt-Epsilon was compiled without spoondetector v0");
		}
		if(!isset($this->libs["toomuchbuffer"]) || !interface_exists(Closeable::class)){
			throw new \ClassNotFoundException("WorldEditArt-Epsilon was compiled without toomuchbuffer v0");
		}
		SpoonDetector::printSpoon($this);
		// You are using WorldEditArt-Lite. This is a §l§ndemo version§r§e of WorldEditArt-Epsilon, where many features have still not been completed.
		// WorldEditArt-Lite is a "temporary release" to satisfy the urgent demand for world editing plugins, but the most important features, e.g. lagless editing, undo/redo, etc., will not be included in WorldEditArt-Lite.
		/** @noinspection SpellCheckingInspection */
		$this->getLogger()->warning(base64_decode("WW91IGFyZSB1c2luZyBXb3JsZEVkaXRBcnQtTGl0ZS4gVGhpcyBpcyBhIMKnbMKnbmRlbW8gdmVyc2lvbsKncsKnZSBvZiBXb3JsZEVkaXRBcnQtRXBzaWxvbiwgd2hlcmUgbWFueSBmZWF0dXJlcyBoYXZlIHN0aWxsIG5vdCBiZWVuIGNvbXBsZXRlZC4="));
		/** @noinspection SpellCheckingInspection */
		$this->getLogger()->warning(base64_decode("V29ybGRFZGl0QXJ0LUxpdGUgaXMgYSAidGVtcG9yYXJ5IHJlbGVhc2UiIHRvIHNhdGlzZnkgdGhlIHVyZ2VudCBkZW1hbmQgZm9yIHdvcmxkIGVkaXRpbmcgcGx1Z2lucywgYnV0IHRoZSBtb3N0IGltcG9ydGFudCBmZWF0dXJlcywgZS5nLiBsYWdsZXNzIGVkaXRpbmcsIHVuZG8vcmVkbywgZXRjLiwgd2lsbCBub3QgYmUgaW5jbHVkZWQgaW4gV29ybGRFZGl0QXJ0LUxpdGUu"));

		$this->builderSessionMap = [];
		if(!is_dir($this->getDataFolder() . "cache")){
			mkdir($this->getDataFolder() . "cache");
		}
		if(!is_file($this->getDataFolder() . "config.yml")){
			throw new PluginException("config.yml missing");
		}
		$this->getConfig();
		$this->czManager = new ConstructionZoneManager($this);

		$this->builderSessionMap = [];
		$this->wandManager = new WandManager($this);
		WorldEditArtCommand::registerAll($this, $this->wandManager->getCommands());

		new PlayerEventListener($this);
	}

	public function onDisable(){
		if(isset($this->czManager)){
			$this->czManager->save();
		}
	}

	public static function getInstance(Server $server) : WorldEditArt{
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $server->getPluginManager()->getPlugin(Consts::PLUGIN_NAME);
	}


	/**
	 * Starts a builder session for the player
	 *
	 * @param Player $player
	 *
	 * @return \LegendsOfMCPE\WorldEditArt\Epsilon\Session\PlayerBuilderSession
	 */
	public function startPlayerSession(Player $player) : PlayerBuilderSession{
		if(!isset($this->builderSessionMap[$player->getId()])){
			$this->builderSessionMap[$player->getId()] = [];
		}
		$this->builderSessionMap[$player->getId()][PlayerBuilderSession::SESSION_KEY] = $session
			= new PlayerBuilderSession($this, $player);
		return $session;
	}

	/**
	 * Closes <em>only</em> the player builder session (non-minion) of the player.
	 *
	 * @param Player $player
	 */
	public function closePlayerSession(Player $player){
		$this->builderSessionMap[$player->getId()][PlayerBuilderSession::SESSION_KEY]->close();
		unset($this->builderSessionMap[$player->getId()][PlayerBuilderSession::SESSION_KEY]);
	}

	/**
	 * Returns all open builder sessions (including both implicit/explicit and minion sessions) of the command sender.
	 *
	 * @param CommandSender $sender
	 *
	 * @return BuilderSession[]
	 */
	public function getSessionsOf(CommandSender $sender) : array{
		return $this->builderSessionMap[$sender instanceof Player ? $sender->getId() : $sender->getName()] ?? [];
	}

	/**
	 * Closes all open builder sessions (including both implicit/explicit and minion sessions) of the command sender.
	 *
	 * @param CommandSender $sender
	 */
	public function closeSessions(CommandSender $sender){
		if(isset($this->builderSessionMap[$sender instanceof Player ? $sender->getId() : $sender->getName()])){
			foreach($this->builderSessionMap[$sender instanceof Player ? $sender->getId() : $sender->getName()] as $session){
				$session->close();
			}
			unset($this->builderSessionMap[$sender instanceof Player ? $sender->getId() : $sender->getName()]);
		}
	}


	public function quickExecute(Level $level, BuilderSession $session, SerializableBlockStreamGetter $getter, BlockChanger $changer, Vector3 $center, &$badBlocks) : int{
		$doerHash = spl_object_hash($session->getOwner());
		$canBypassLock = $session->canBypassLock();
		$canBypassZone = $session->canBypassZone();
		$goodBlocks = 0;
		$badBlocks = 0;
		$manager = $this->getConstructionZoneManager();
		$ccz = $manager->calcCczValue($level->getFolderName());
		$vector = new Vector3;
		$generator = $getter->getValue($vector);
		assert($generator instanceof \Generator);
		foreach($generator as $valid){
			if(!$valid){
				continue;
			}
			if(!$manager->canDoEdit($vector, $level->getFolderName(), $doerHash, $ccz, $canBypassLock, $canBypassZone)){
				++$badBlocks;
				continue;
			}
			$target = $changer->change($level->getBlock($vector));
			if($target !== null){
				$level->setBlock($vector, $target->toBlock($center->subtract($vector)), false, false);
				++$goodBlocks;
			}
		}
		return $goodBlocks;
	}
}
