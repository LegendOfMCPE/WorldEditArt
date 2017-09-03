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

use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Server;
use sofe\libgeom\Shape;

/**
 * Adapter interface for exposing libgeom shapes to other plugins
 */
interface IShape{
	public function isInside(Vector3 $vector) : bool;

	public function getEstimatedSize() : int;

	public function getEstimatedSurfaceSize(float $padding, float $margin) : int;

	public function getSolidStream(Vector3 $vector) : \Generator;

	public function getHollowStream(Vector3 $vector, float $padding, float $margin) : \Generator;

	public function marginalDistance(Vector3 $vector) : float;

	public function getChunksInvolved() : array;

	public function isComplete() : bool;

	public function getCenter();

	public function getBaseShape() : Shape;

	public function getLevel(Server $server) : ?Level;

	public function getLevelName() : string;
}
