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

class ConstructionZone{
	const LOCK_MODE_EDIT = 1;
	const LOCK_MODE_BLOCKS = 2;
	const LOCK_MODE_ENTRY = 3;
	const LOCK_STRING_TO_ID = [
		"edit" => ConstructionZone::LOCK_MODE_EDIT,
		"blocks" => ConstructionZone::LOCK_MODE_BLOCKS,
		"entry" => ConstructionZone::LOCK_MODE_ENTRY,
	];
	const LOCK_ID_TO_PERM = [
		ConstructionZone::LOCK_MODE_EDIT => Consts::PERM_CZONE_BUILDER_LOCK_EDIT,
		ConstructionZone::LOCK_MODE_BLOCKS => Consts::PERM_CZONE_BUILDER_LOCK_BLOCKS,
		ConstructionZone::LOCK_MODE_ENTRY => Consts::PERM_CZONE_BUILDER_LOCK_ENTRY,
	];

	/** @var string */
	private $name;
	/** @var IShape */
	private $shape;
	/** @var string|null */
	private $lockingSession = null;
	/** @var string|null */
	private $lockingSessionName = null;
	/** @var int */
	private $lockMode = 0;

	/**
	 * ConstructionZone constructor.
	 *
	 * @param string $name
	 * @param IShape $shape
	 */
	public function __construct($name, IShape $shape){
		$this->name = $name;
		$this->shape = $shape;
	}

	/**
	 * Returns the name of the construction zone, case preserved
	 *
	 * @return string
	 */
	public function getName() : string{
		return $this->name;
	}

	public function getShape() : IShape{
		return $this->shape;
	}

	public function setShape(IShape $shape) : void{
		$this->shape = $shape;
	}

	public function getLockingSession(&$name = null) : ?string{
		if(isset($this->lockingSession, $this->lockingSessionName)){
			/** @noinspection CallableParameterUseCaseInTypeContextInspection */
			$name = $this->lockingSessionName;
			return $this->lockingSession;
		}
		return null;
	}

	public function isLocked() : bool{
		return $this->lockingSession !== null;
	}

	public function getLockMode() : int{
		return $this->lockMode;
	}

	public function lock(BuilderSession $lockingSession, int $lockMode) : void{
		if($this->isLocked()){
			throw new \InvalidStateException("Zone already locked!");
		}
		$this->lockingSession = spl_object_hash($lockingSession->getOwner());
		$this->lockingSessionName = $lockingSession->getOwner()->getName();
		$this->lockMode = $lockMode;
	}

	public function unlock() : void{
		unset($this->lockingSession, $this->lockingSessionName, $this->lockMode);
	}
}
