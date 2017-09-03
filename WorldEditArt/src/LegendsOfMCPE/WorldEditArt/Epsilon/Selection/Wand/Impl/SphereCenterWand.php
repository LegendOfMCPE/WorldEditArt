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
use LegendsOfMCPE\WorldEditArt\Epsilon\Consts;
use LegendsOfMCPE\WorldEditArt\Epsilon\IShape;
use LegendsOfMCPE\WorldEditArt\Epsilon\LibgeomAdapter\ShapeWrapper;
use LegendsOfMCPE\WorldEditArt\Epsilon\Selection\Wand\AbstractFieldDefinitionWand;
use pocketmine\level\Position;
use sofe\libgeom\Shape;
use sofe\libgeom\shapes\EllipsoidShape;

class SphereCenterWand extends AbstractFieldDefinitionWand{
	public function getName() : string{
		return "sphcenter";
	}

	public function getAliases() : array{
		return ["sphc", "sphcentre"];
	}

	public function getPermission() : string{
		return Consts::PERM_SELECT_SET_SPHERE;
	}

	protected function canModify(Shape $shape) : bool{
		return $shape instanceof EllipsoidShape;
	}

	protected function modify(BuilderSession $session, Shape $shape, Position $position){
		assert($shape instanceof EllipsoidShape);
		$shape->setCenter($position);
	}

	protected function createNew(/** @noinspection PhpUnusedParameterInspection */
		Position $position) : IShape{
		return new ShapeWrapper(new EllipsoidShape($position->getLevel(), $position));
	}
}
