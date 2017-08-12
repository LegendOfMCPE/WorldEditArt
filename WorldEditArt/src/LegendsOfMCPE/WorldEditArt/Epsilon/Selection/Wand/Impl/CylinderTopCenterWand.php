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
use LegendsOfMCPE\WorldEditArt\Epsilon\Selection\Wand\AbstractFieldDefinitionWand;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\UserFormat;
use pocketmine\level\Position;
use sofe\libgeom\Shape;
use sofe\libgeom\shapes\CircularFrustumShape;

class CylinderTopCenterWand extends AbstractFieldDefinitionWand{
	public function getName() : string{
		return "cyltop";
	}

	public function getAliases() : array{
		return ["ct"];
	}

	public function getPermission() : string{
		return Consts::PERM_SELECT_SET_CYLINDER;
	}

	protected function canModify(Shape $shape) : bool{
		return $shape instanceof CircularFrustumShape;
	}

	protected function modify(BuilderSession $session, Shape $shape, Position $position){
		assert($shape instanceof CircularFrustumShape);
		$oldTop = $shape->getTop();
		$shape->setTop($position);
		if($oldTop !== null){
			$session->msg("Moved top center from " . UserFormat::formatVector($oldTop) . " to " . UserFormat::formatVector($position), BuilderSession::MSG_CLASS_SUCCESS);
		}else{
			$session->msg("Set top center to " . UserFormat::formatVector($position));
		}
	}

	protected function canCreateNoisy(BuilderSession $session) : bool{
		$session->msg("Please define the base of the cylinder/cone/circular frustum before defining the top", BuilderSession::MSG_CLASS_ERROR);
		return false;
	}
}
