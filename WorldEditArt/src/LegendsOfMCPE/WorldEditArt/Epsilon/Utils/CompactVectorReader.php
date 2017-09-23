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

namespace LegendsOfMCPE\WorldEditArt\Epsilon\Utils;

use pocketmine\math\Vector3;
use sofe\toomuchbuffer\InputStream;
use sofe\toomuchbuffer\LittleEndianDataReader;

class CompactVectorReader extends LittleEndianDataReader{
	/** @var Vector3 */
	private $vector;
	/** @var int */
	private $size, $i;

	public function __construct(InputStream $stream){
		parent::__construct($stream);
		$this->vector = new Vector3;
		$this->size = $this->readInt(false);
		$this->i = 0;
	}

	public function readVector() : ?Vector3{
		if($this->i++ >= $this->size){
			return null;
		}

		$byte = $this->readByte();
		if($byte & CompactVectorWriter::BIT_X){
			$this->vector->x = $this->readVarInt();
		}
		if($byte & CompactVectorWriter::BIT_Y){
			$this->vector->y = $this->readVarInt();
		}
		if($byte & CompactVectorWriter::BIT_Z){
			$this->vector->z = $this->readVarInt();
		}
		return clone $this->vector;
	}
}
