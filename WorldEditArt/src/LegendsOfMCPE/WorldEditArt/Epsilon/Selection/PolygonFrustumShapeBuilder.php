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

namespace LegendsOfMCPE\WorldEditArt\Epsilon\Selection;

use LegendsOfMCPE\WorldEditArt\Epsilon\IShape;
use pocketmine\math\Vector3;
use sofe\libgeom\LibgeomBinaryStream;
use sofe\libgeom\Shape;

/** @noinspection PhpInconsistentReturnPointsInspection */

/** @noinspection PhpInconsistentReturnPointsInspection */
class PolygonFrustumShapeBuilder extends Shape implements IShape{
	public function getBaseShape() : Shape{
		return $this;
	}

	public function isInside(Vector3 $vector) : bool{
		throw new \AssertionError("Incomplete shape");
	}

	protected function estimateSize() : int{
		throw new \AssertionError("Incomplete shape");
	}

	/** @noinspection PhpInconsistentReturnPointsInspection
	 * @param Vector3 $vector
	 *
	 * @return \Generator
	 * @throws \AssertionError
	 */
	public function getSolidStream(Vector3 $vector) : \Generator{
		throw new \AssertionError("Incomplete shape");
	}

	/** @noinspection PhpInconsistentReturnPointsInspection
	 * @param Vector3 $vector
	 * @param float   $padding
	 * @param float   $margin
	 *
	 * @return \Generator
	 * @throws \AssertionError
	 */
	public function getShallowStream(Vector3 $vector, float $padding, float $margin) : \Generator{
		throw new \AssertionError("Incomplete shape");
	}

	public function marginalDistance(Vector3 $vector) : float{
		throw new \AssertionError("Incomplete shape");
	}

	public function getChunksInvolved() : array{
		throw new \AssertionError("Incomplete shape");
	}

	public function toBinary(LibgeomBinaryStream $stream){
		throw new \AssertionError("Incomplete shape");
	}

	public function getMinX() : int{
		throw new \AssertionError("Incomplete shape");
	}

	public function getMinY() : int{
		throw new \AssertionError("Incomplete shape");
	}

	public function getMinZ() : int{
		throw new \AssertionError("Incomplete shape");
	}

	public function getMaxX() : int{
		throw new \AssertionError("Incomplete shape");
	}

	public function getMaxY() : int{
		throw new \AssertionError("Incomplete shape");
	}

	public function getMaxZ() : int{
		throw new \AssertionError("Incomplete shape");
	}

	public function isComplete() : bool{
		return false;
	}
}
