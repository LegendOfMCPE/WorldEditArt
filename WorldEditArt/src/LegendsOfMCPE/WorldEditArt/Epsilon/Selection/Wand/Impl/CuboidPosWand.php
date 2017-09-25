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
use sofe\libgeom\shapes\CuboidShape;

class CuboidPosWand extends AbstractFieldDefinitionWand{
	/** @var bool */
	private $one;

	public function __construct(bool $one){
		$this->one = $one;
	}

	public function getName() : string{
		return $this->one ? "pos1" : "pos2";
	}

	public function getAliases() : array{
		return $this->one ? ["1"] : ["2"];
	}

	public function getPermission() : string{
		return Consts::PERM_SELECT_SET_CUBOID;
	}

	protected function canModify(Shape $shape) : bool{
		return $shape instanceof CuboidShape;
	}

	protected function modify(BuilderSession $session, Shape $shape, Position $position) : void{
		assert($shape instanceof CuboidShape);
		if($this->one){
			$shape->setFrom($position);
		}else{
			$shape->setTo($position);
		}
	}

	protected function createNew(Position $position) : IShape{
		$shape = new ShapeWrapper($base = new CuboidShape($position->getLevel()));
		if($this->one){
			$base->setFrom($position);
		}else{
			$base->setTo($position);
		}
		return $shape;
	}
}
