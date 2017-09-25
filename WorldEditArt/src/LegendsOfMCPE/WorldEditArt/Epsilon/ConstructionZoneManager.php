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

namespace LegendsOfMCPE\WorldEditArt\Epsilon;

use LegendsOfMCPE\WorldEditArt\Epsilon\LibgeomAdapter\ShapeWrapper;
use pocketmine\math\Vector3;
use pocketmine\utils\Binary;
use sofe\libgeom\io\LibgeomDataReader;
use sofe\libgeom\io\LibgeomDataWriter;
use sofe\libgeom\io\LibgeomLittleEndianDataReader;
use sofe\libgeom\io\LibgeomLittleEndianDataWriter;
use sofe\libgeom\UnsupportedOperationException;

class ConstructionZoneManager implements \Serializable{
	/** @var WorldEditArt */
	private $plugin;
	/** @var string */
	private $file;

	/** @var bool */
	private $configCheck;
	/** @var string[] */
	private $configWorlds;

	/** @var ConstructionZone[] */
	private $constructionZones;

	/** @var string|null */
	private $cachedSerialization = null;

	// TODO Reminder: Reset cachedSerialization to null upon modifying constructionZones

	public function __construct(WorldEditArt $plugin){
		$this->plugin = $plugin;
		$this->file = $this->plugin->getDataFolder() . "constructionZones.dat";
		$this->configCheck = $this->plugin->getConfig()->get(Consts::CONFIG_CONSTRUCTION_ZONE_CHECK);
		/** @noinspection UnnecessaryCastingInspection */
		$this->configWorlds = array_map("mb_strtolower", (array) ($this->plugin->getConfig()->get(Consts::CONFIG_CONSTRUCTION_ZONE_WORLDS) ?: []));
		$this->load();
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

	public function getConstructionZone(string $name) : ?ConstructionZone{
		return $this->constructionZones[mb_strtolower($name)] ?? null;
	}

	public function add(ConstructionZone $zone) : void{
		$this->constructionZones[mb_strtolower($zone->getName())] = $zone;
	}

	public function remove(string $name) : ?ConstructionZone{
		if(isset($this->constructionZones[$lowName = mb_strtolower($name)])){
			$zone = $this->constructionZones[$lowName];
			unset($this->constructionZones[$lowName]);
			return $zone;
		}
		return null;
	}

	public function rename(ConstructionZone $zone, string $name) : void{
		if(!isset($this->constructionZones[$oldName = mb_strtolower($zone->getName())])){
			throw new \UnexpectedValueException("The construction zone was not added");
		}
		if($this->constructionZones[$oldName] !== $zone){
			throw new \UnexpectedValueException("Different instances of ConstructionZone with the same name");
		}
		unset($this->constructionZones[$oldName]);
		$zone->setName($name);
		$this->constructionZones[mb_strtolower($name)] = $zone;
	}

	/**
	 * @return bool
	 */
	public function checks() : bool{
		return $this->configCheck;
	}

	/**
	 * @return string[]
	 */
	public function getWorlds() : array{
		return $this->configWorlds;
	}


	public function releaseBySession(BuilderSession $session) : void{
		foreach($this->constructionZones as $zone){
			if($zone->getLockingSession() === spl_object_hash($session->getOwner())){
				$zone->unlock();
			}
		}
	}

	/**
	 * Returns the $cczValue used in {@link ConstructionZoneManager::canDoEdit}.
	 *
	 *
	 * @param string $levelName
	 *
	 * @return bool whether editing in the level is limited by construction zones
	 */
	public function calcCczValue(string $levelName) : bool{
		return !($this->configCheck && !in_array(mb_strtolower($levelName), $this->configWorlds, true));
	}

	/**
	 * @param Vector3 $vector
	 * @param string  $levelName
	 * @param string  $sessionOwnerHash
	 * @param bool    $cczValue
	 * @param bool    $canBypassLock
	 * @param bool    $canBypassZone
	 *
	 * @return bool
	 */
	public function canDoEdit(Vector3 $vector, string $levelName, string $sessionOwnerHash, bool $cczValue, bool $canBypassLock, bool $canBypassZone) : bool{
		foreach($this->constructionZones as $zone){
			if($zone->getShape()->getLevelName() !== $levelName){
				continue;
			}
			$isInside = null;
			if(!$canBypassLock && $zone->isLocked() && $sessionOwnerHash !== $zone->getLockingSession()){
				$isInside = $zone->getShape()->isInside($vector);
				if($isInside){
					return false;
				}
			}
			if($canBypassZone || !$cczValue){ // no need checking if inside construction zone
				continue;
			}
			if($isInside === null){
				$isInside = $zone->getShape()->isInside($vector);
			}
			if($isInside === true || ($isInside === null && $zone->getShape()->isInside($vector))){
				return false;
			}
		}
		return true;
	}


	public function load() : void{
		if(is_file($this->file)){
			$reader = LibgeomLittleEndianDataReader::fromFile($this->file);
			try{
				$this->read($reader);
			}/** @noinspection BadExceptionsProcessingInspection */catch(\UnderflowException $e){
				$this->plugin->getLogger()->error("Corrupted constructionZones.dat, resetting to empty...");
				file_put_contents($this->file, Binary::writeUnsignedVarInt(0));
				$this->constructionZones = [];
			}finally{
				$reader->close();
			}
		}else{
			$this->constructionZones = [];
		}
	}

	public function save() : void{
		$writer = LibgeomLittleEndianDataWriter::toFile($this->file);
		$this->write($writer);
		$writer->close();
	}


	public function read(LibgeomDataReader $reader) : void{
		$version = $reader->readShort();
		if($version !== 1){
			throw new UnsupportedOperationException("Unsupported constructionZones.dat version ($version, only supports 1)");
		}
		$count = $reader->readVarInt(false);
		$this->constructionZones = [];
		for($i = 0; $i < $count; ++$i){
			$name = $reader->readString();
			/** @var string|\sofe\libgeom\Shape $class */
			$class = $reader->readString();
			$shape = $class::fromBinary($this->plugin->getServer(), $reader);
			$wrappedShape = new ShapeWrapper($shape);
			$this->constructionZones[mb_strtolower($name)] = new ConstructionZone($name, $wrappedShape);
		}
	}

	public function write(LibgeomDataWriter $writer) : void{
		$writer->writeShort(1); // version
		$writer->writeVarInt(count($this->constructionZones), false);
		foreach($this->constructionZones as $zone){
			$shape = $zone->getShape()->getBaseShape();
			$writer->writeString($zone->getName());
			$writer->writeString(get_class($shape));
			$shape->toBinary($writer);
		}
	}

	public function serialize() : string{
		return $this->cachedSerialization ?? ($this->cachedSerialization =
				serialize([$this->configCheck, $this->configWorlds, $this->constructionZones]));
	}

	public function unserialize($serialized) : void{
		$this->cachedSerialization = $serialized;
		[$this->configCheck, $this->configWorlds, $this->constructionZones] = unserialize($serialized, true);
	}
}
