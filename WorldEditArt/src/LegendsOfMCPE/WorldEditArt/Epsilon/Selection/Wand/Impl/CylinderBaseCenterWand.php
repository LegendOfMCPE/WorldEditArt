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
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\UserFormat;
use pocketmine\level\Position;
use sofe\libgeom\Shape;
use sofe\libgeom\shapes\CircularFrustumShape;

class CylinderBaseCenterWand extends AbstractFieldDefinitionWand{
	public function getName() : string{
		return "cylbase";
	}

	public function getAliases() : array{
		return ["cb"];
	}

	public function getPermission() : string{
		return Consts::PERM_SELECT_SET_CYLINDER;
	}

	protected function canModify(Shape $shape) : bool{
		return $shape instanceof CircularFrustumShape;
	}

	protected function modify(BuilderSession $session, Shape $shape, Position $position) : void{
		assert($shape instanceof CircularFrustumShape);
		$oldBase = $shape->getBase();
		$shape->setBase($position);
		$session->msg("Moved base center from " . UserFormat::formatVector($oldBase) . " to " . UserFormat::formatVector($position), BuilderSession::MSG_CLASS_SUCCESS);
	}

	protected function createNew(Position $position) : IShape{
		return new ShapeWrapper(new CircularFrustumShape($position->getLevel(), $position));
	}
}
