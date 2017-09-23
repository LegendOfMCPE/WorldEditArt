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
use sofe\toomuchbuffer\AmendableOutputStream;
use sofe\toomuchbuffer\LittleEndianDataWriter;

class CompactVectorWriter extends LittleEndianDataWriter{
	const BIT_X = 1;
	const BIT_Y = 2;
	const BIT_Z = 4;

	private $length = 0;

	private $lastX = null;
	private $lastY = null;
	private $lastZ = null;

	public function __construct(AmendableOutputStream $stream){
		parent::__construct($stream);
		$this->write("\0\0\0\0"); // reserve space for writing length
	}

	public function writeVector(Vector3 $vector) : void{
		++$this->length;
		$byte = 0;
		if($vector->x !== $this->lastX){
			$byte |= self::BIT_X;
		}
		if($vector->y !== $this->lastY){
			$byte |= self::BIT_Y;
		}
		if($vector->z !== $this->lastZ){
			$byte |= self::BIT_Z;
		}
		$this->writeByte($byte);
		if($vector->x !== $this->lastX){
			$this->lastX = $vector->x;
			$this->writeVarInt($vector->x);
		}
		if($vector->y !== $this->lastY){
			$this->lastY = $vector->y;
			$this->writeVarInt($vector->y);
		}
		if($vector->z !== $this->lastZ){
			$this->lastZ = $vector->z;
			$this->writeVarInt($vector->z);
		}
	}

	public function close() : void{
		assert($this->stream instanceof AmendableOutputStream);
		$this->stream->amend(0, pack("V", $this->length));
		parent::close();
	}
}
