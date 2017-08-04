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

namespace LegendsOfMCPE\WorldEditArt\Epsilon\Selection\Wand;

use LegendsOfMCPE\WorldEditArt\Epsilon\BuilderSession;
use LegendsOfMCPE\WorldEditArt\Epsilon\IShape;
use pocketmine\level\Position;
use sofe\libgeom\Shape;

/**
 * @internal This class should not be uesd by non-WorldEditArt code.
 */
abstract class AbstractFieldDefinitionWand implements Wand{
	public function execute(BuilderSession $session, Position $position, string $selectionName){
		$shape = $session->getSelection($selectionName)->getBaseShape();
		try{
			if($shape->getLevelName() === $position->getLevel()->getName() and $this->canModify($shape)){
				$this->modify($shape, $position);
			}else{
				$shape = $this->createNew($position);
				$session->setSelection($selectionName, $shape);
			}
		}catch(WandException $ex){
			$session->msg($ex->getMessage(), BuilderSession::MSG_CLASS_ERROR);
		}
	}

	protected abstract function canModify(Shape $shape) : bool;

	protected abstract function modify(Shape $shape, Position $position);

	protected abstract function createNew(Position $position) : IShape;
}
