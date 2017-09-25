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
use pocketmine\level\Position;
use sofe\libgeom\Shape;
use sofe\libgeom\shapes\EllipsoidShape;

class SphereRadiusWand extends AbstractFieldDefinitionWand{
	const AXIS_X = "x";
	const AXIS_Y = "y";
	const AXIS_Z = "z";
	const AXIS_ALL = "r";

	private $axis;

	public function __construct(string $axis){
		$this->axis = $axis;
	}

	public function getName() : string{
		return "sph" . $this->axis;
	}

	/**
	 * @return string[] without leading slashes
	 */
	public function getAliases() : array{
		return [];
	}

	public function getPermission() : string{
		return Consts::PERM_SELECT_SET_SPHERE;
	}

	protected function canModify(Shape $shape) : bool{
		return $shape instanceof EllipsoidShape and $shape->getCenter() !== null;
	}

	protected function modify(BuilderSession $session, Shape $shape, Position $position) : void{
		assert($shape instanceof EllipsoidShape);
		$this->setRadius($shape, $position->distance($shape->getCenter()));
	}

	protected function canCreateNoisy(/** @noinspection PhpUnusedParameterInspection */
		BuilderSession $session) : bool{
		$session->msg("Please use the sphc wand first!", BuilderSession::MSG_CLASS_ERROR);
		return false;
	}

	private function setRadius(EllipsoidShape $shape, float $radius) : void{
		switch($this->axis){
			case self::AXIS_X:
				$shape->setRadiusX($radius);
				break;
			case self::AXIS_Y:
				$shape->setRadiusY($radius);
				break;
			case self::AXIS_Z:
				$shape->setRadiusZ($radius);
				break;
			case self::AXIS_ALL:
				$shape->setRadiusX($radius);
				$shape->setRadiusY($radius);
				$shape->setRadiusZ($radius);
				break;
		}
	}
}
