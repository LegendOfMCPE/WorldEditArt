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

class CylinderShiftWand extends AbstractFieldDefinitionWand{
	public function getName() : string{
		return "cylshift";
	}

	public function getAliases() : array{
		return ["cs"];
	}

	public function getPermission() : string{
		return Consts::PERM_SELECT_SET_CYLINDER;
	}

	public function createNew(Position $position) : IShape{
		return new ShapeWrapper(new CircularFrustumShape($position->getLevel(), $position));
	}

	public function canModify(Shape $shape) : bool{
		return $shape instanceof CircularFrustumShape;
	}

	protected function modify(BuilderSession $session, Shape $shape, Position $newBase) : void{
		assert($shape instanceof CircularFrustumShape);
		$oldBase = $shape->getBase();
		$oldTop = $shape->getTop();
		$shape->setBase($newBase);
		$newTop = null;
		if($oldTop !== null){
			$delta = $oldTop->subtract($oldBase);
			$newTop = $newBase->add($delta);
			$shape->setTop($newTop);
		}
		if(isset($delta)){
			$session->msg(sprintf("The whole shape has been moved by %s", UserFormat::formatDelta($delta)), BuilderSession::MSG_CLASS_SUCCESS);
		}else{
			$session->msg(sprintf("The base has been moved from %s to %s", UserFormat::formatVector($oldBase), UserFormat::formatVector($newBase)), BuilderSession::MSG_CLASS_SUCCESS);
		}
	}
}
