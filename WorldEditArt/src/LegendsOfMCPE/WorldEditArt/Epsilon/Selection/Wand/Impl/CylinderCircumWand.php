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
use pocketmine\math\Vector3;
use sofe\libgeom\Shape;
use sofe\libgeom\shapes\CircularFrustumShape;

abstract class CylinderCircumWand extends AbstractFieldDefinitionWand{
	/** @var bool */
	protected $isTop, $isFront;
	/** @var string */
	private $longAction, $shortAction;

	public function __construct(bool $isTop, bool $isFront, string $longAction, string $shortAction){
		$this->isTop = $isTop;
		$this->isFront = $isFront;
		$this->longAction = $longAction;
		$this->shortAction = $shortAction;
	}

	public function getName() : string{
		return "cyl" . ($this->isTop ? "top" : "base") . ($this->isFront ? "front" : "back") . $this->longAction;
	}

	public function getAliases() : array{
		return ["c" . ($this->isTop ? "t" : "b") . ($this->isFront ? "f" : "b") . $this->shortAction];
	}

	public function getPermission() : string{
		return Consts::PERM_SELECT_SET_CYLINDER;
	}

	protected function canModify(Shape $shape) : bool{
		if(!($shape instanceof CircularFrustumShape)){
			return false;
		}
		if($this->isTop && $shape->getTop() === null){
			return false;
		}
		return true;
	}

	public function canCreateNoisy(BuilderSession $session) : bool{
		$topMsg = $this->isTop ? "and the top center with the cyltop wand " : "";
		$session->msg("Please select the base center with the cylbase wand {$topMsg}first.", BuilderSession::MSG_CLASS_ERROR);
		return false;
	}

	/**
	 * @param CircularFrustumShape $shape
	 * @param bool                 $differentAxis
	 * @param bool                 $differentFace
	 *
	 * @return float|null
	 */
	protected function getRadius(CircularFrustumShape $shape, bool $differentAxis, bool $differentFace) : ?float{
		// the !== used below are equivalent to the XOR operator
		if($this->isTop !== $differentFace){
			if($this->isFront !== $differentAxis){
				return $shape->getTopFrontRadius();
			}
			return $shape->getTopRightRadius();
		}
		if($this->isFront !== $differentAxis){
			return $shape->getBaseFrontRadius();
		}
		return $shape->getBaseRightRadius();
	}

	protected function setRadius(CircularFrustumShape $shape, float $length, bool $differentAxis, bool $differentFace){
		// the !== used below are equivalent to the XOR operator
		if($this->isTop !== $differentFace){
			if($this->isFront !== $differentAxis){
				$shape->setTopFrontRadius($length);
			}else{
				$shape->setTopRightRadius($length);
			}
		}else{
			if($this->isFront !== $differentAxis){
				$shape->setBaseFrontRadius($length);
			}else{
				$shape->setBaseRightRadius($length);
			}
		}
	}

	protected function getDir(CircularFrustumShape $shape, bool $differentAxis){
		return ($this->isFront !== $differentAxis) ? $shape->getFrontDir() : $shape->getRightDir();
	}

	protected function setDir(CircularFrustumShape $shape, Vector3 $dir, bool $differentAxis){
		if($this->isFront !== $differentAxis){
			$shape->setFrontDir($dir);
		}else{
			$shape->setRightDir($dir);
		}
	}

	protected function getCenter(CircularFrustumShape $shape, bool $differentFace){
		return ($this->isTop !== $differentFace) ? $shape->getTop() : $shape->getBase();
	}
}
