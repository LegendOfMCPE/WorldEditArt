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

namespace LegendsOfMCPE\WorldEditArt\Epsilon\Selection\Wand\Impl;

use LegendsOfMCPE\WorldEditArt\Epsilon\BuilderSession;
use pocketmine\level\Position;
use sofe\libgeom\Shape;
use sofe\libgeom\shapes\CircularFrustumShape;

class CylinderCircumProjectionWand extends CylinderCircumWand{
	public function __construct(bool $isTop, bool $isFront){
		parent::__construct($isTop, $isFront, "proj", "p");
	}

	protected function modify(BuilderSession $session, Shape $shape, Position $position){
		assert($shape instanceof CircularFrustumShape);
		$aRadius = $this->getRadius($shape, true, false);
		$otherDir = $this->getDir($shape, true);
		if($aRadius === null || $otherDir === null){
			$this->setRadius($shape, $radius = $position->distance($this->getCenter($shape, false)), false, false);
			if($aRadius === null){
				$this->setRadius($shape, $radius, true, false);
			}
			if($this->getRadius($shape, false, true) === null){
				$this->setRadius($shape, $radius, false, true);
			}
			if($this->getRadius($shape, true, true) === null){
				$this->setRadius($shape, $radius, true, true);
			}
			$shape->unsetDirections();
			$this->setDir($shape, $position->subtract($this->getCenter($shape, false)), false);
			return;
		}

		$c = $this->getCenter($shape, false);
		$cp = $position->subtract($c);
		// detect nearest point from $position to the plane containing $c perpendicular to $otherDir, see draft-1
		$cq = $cp->subtract($otherDir->multiply($cp->dot($otherDir)));
		$this->setDir($shape, $cq, false);
		$this->setRadius($shape, $cq->length(), false, false);
		if($this->getRadius($shape, false, true) === null){
			$this->setRadius($shape, $cq->length(), false, true);
		}
	}
}
