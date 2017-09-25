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
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\UserFormat;
use pocketmine\level\Position;
use sofe\libgeom\Shape;

/**
 * @internal This class should not be used by non-WorldEditArt code.
 */
abstract class AbstractFieldDefinitionWand implements Wand{
	public function execute(BuilderSession $session, Position $position, string $selectionName) : void{
		$ishape = $session->getSelection($selectionName);
		$shape = $ishape !== null ? $ishape->getBaseShape() : null;
		try{
			/** @noinspection NullPointerExceptionInspection */
			if($shape !== null and $shape->getLevelName() === $position->getLevel()->getFolderName() and $this->canModify($shape)){
				$this->modify($session, $shape, $position);
			}else{
				if(!$this->canCreateNoisy($session)){
					return;
				}
				$ishape = $this->createNew($position);
				$session->setSelection($selectionName, $ishape);
			}
			$session->msg(UserFormat::describeShape($session->getPlugin()->getServer(), $ishape, UserFormat::FORMAT_USER_DEFINITION), BuilderSession::MSG_CLASS_SUCCESS, "Your \"$selectionName\" selection has been changed.");
		}catch(WandException $ex){
			$session->msg($ex->getMessage(), BuilderSession::MSG_CLASS_ERROR);
		}
	}

	protected abstract function canModify(Shape $shape) : bool;

	protected abstract function modify(BuilderSession $session, Shape $shape, Position $position) : void;

	protected function canCreateNoisy(/** @noinspection PhpUnusedParameterInspection */
		BuilderSession $session) : bool{
		return true;
	}

	protected function createNew(/** @noinspection PhpUnusedParameterInspection */
		Position $position) : IShape{
		throw new \AssertionError("Implementation must override this method unless it returns false in canCreateNoisy");
	}
}
