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

namespace LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\Selection\Edit;

use LegendsOfMCPE\WorldEditArt\Epsilon\BuilderSession;
use LegendsOfMCPE\WorldEditArt\Epsilon\Consts;
use LegendsOfMCPE\WorldEditArt\Epsilon\IShape;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\Session\SessionCommand;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\UserFormat;
use LegendsOfMCPE\WorldEditArt\Epsilon\Utils\WEAMath;
use LegendsOfMCPE\WorldEditArt\Epsilon\WorldEditArt;
use pocketmine\math\Vector3;
use sofe\libgeom\Shape;
use sofe\libgeom\shapes\CircularFrustumShape;

class CylinderCommand extends SessionCommand{
	public function __construct(WorldEditArt $plugin){
		parent::__construct($plugin, "/cyl", "Manage a cylinder selection", /** @lang text */
			"//cyl ratio|rad|j|n|c|f ... OR //cyl <radius> [x|y|z] <height>", ["/cylinder"], Consts::PERM_SELECT_SET_CUBOID, [
				"setRadiusHeight" => [
					[
						"name" => "radius",
						"type" => "float",
					],
					[
						"name" => "axis",
						"type" => "stringenum",
						"enum_values" => ["x", "y", "z"],
						"optional" => true,
					],
					[
						"name" => "height",
						"type" => "float",
					],
					[
						"name" => "selectionName",
						"type" => "string",
						"optional" => true,
					],
				],
				"ratio" => [
					[
						"name" => "action",
						"type" => "stringenum",
						"enum_values" => ["ratio"],
					],
					[
						"name" => "ratio",
						"type" => "float",
					],
					[
						"name" => "face",
						"type" => "stringenum",
						"enum_values" => ["t", "top", "b", "base"],
						"optional" => true,
					],
					[
						"name" => "selectionName",
						"type" => "string",
						"optional" => true,
					],
				],
				"oneRadius" => [
					[
						"name" => "action",
						"type" => "stringenum",
						"enum_values" => ["radius", "rad"],
					],
					[
						"name" => "face",
						"type" => "stringenum",
						"enum_values" => ["t", "top", "b", "base"],
						"optional" => true,
					],
					[
						"name" => "which",
						"type" => "stringenum",
						"enum_values" => ["l", "left", "r", "right", "f", "front", "b", "back"],
					],
					[
						"name" => "length",
						"type" => "float",
					],
					[
						"name" => "preserveRatio",
						"type" => "bool",
						"optional" => true,
					],
					[
						"name" => "selectionName",
						"type" => "string",
						"optional" => true,
					],
				],
				"justify" => [
					[
						"name" => "action",
						"type" => "stringenum",
						"enum_values" => ["j", "justify"],
					],
					[
						"name" => "face",
						"type" => "stringenum",
						"enum_values" => ["top", "base", "both"],
					],
					[
						"name" => "whichToChange",
						"type" => "stringenum",
						"enum_values" => ["l", "left", "r", "right", "f", "front", "b", "back"],
					],
					[
						"name" => "selectionName",
						"type" => "string",
						"optional" => true,
					],
				],
				"normalize" => [
					[
						"name" => "action",
						"type" => "stringenum",
						"enum_values" => ["n", "norm", "normalize"],
					],
					[
						"name" => "preserveRadiiLength",
						"type" => "bool",
						"optional" => true,
					],
					[
						"name" => "selectionName",
						"type" => "string",
						"optional" => true,
					],
				],
				"cone" => [
					[
						"name" => "action",
						"type" => "stringenum",
						"enum_values" => ["c", "cone"],
					],
					[
						"name" => "face",
						"type" => "stringenum",
						"enum_values" => ["top", "base"],
						"optional" => true,
					],
					[
						"name" => "selectionName",
						"type" => "string",
						"optional" => true,
					],
				],
				"flip" => [
					[
						"name" => "action",
						"type" => "stringenum",
						"enum_values" => ["flip", "f"],
					],
					[
						"name" => "selectionName",
						"type" => "string",
						"optional" => true,
					],
				],
			]);
	}

	public function run(BuilderSession $session, array $args){
		if(!isset($args[0])){
			$this->sendUsage($session);
			return;
		}
		$selName = $session->getDefaultSelectionName();
		if(is_numeric($args[0])){
			$result = $this->overloadSetRadiusHeight($session, $args, $selName);
		}else{
			switch(mb_strtolower(array_shift($args))){
				case "ratio":
					$result = $this->overloadRatio($session, $args, $selName);
					break;
				case "rad":
				case "radius":
					$result = $this->overloadOneRadius($session, $args, $selName);
					break;
				case "j":
				case "justify":
					$result = $this->overloadJustify($session, $args, $selName);
					break;
				case "n":
				case "norm":
				case "normalize":
					$result = $this->overloadNormalize($session, $args, $selName);
					break;
				case "c":
				case "cone":
					$result = $this->overloadCone($session, $args, $selName);
					break;
				case "f":
				case "flip":
					$result = $this->overloadFlip($session, $args, $selName);
					break;
				default:
					$result = 0;
					break;
			}
		}
		if($result === 1){
			assert(isset($selName));
			$session->msg("Selection \"$selName\" set to " . UserFormat::describeShape($this->getPlugin()->getServer(), $session->getSelection($selName), UserFormat::FORMAT_USER_DEFINITION), BuilderSession::MSG_CLASS_SUCCESS);
		}elseif($result === 0){
			$this->sendUsage($session);
		}
	}

	private function overloadSetRadiusHeight(BuilderSession $session, array $args, string &$selName) : int{
		$radius = (float) array_shift($args);
		if(!isset($args[0])){
			return 0;
		}
		$args[0] = mb_strtolower($args[0]);
		$axis = new Vector3(0, 1, 0);
		$right = new Vector3(1, 0, 0);
		if($args[0] === "x"){
			array_shift($args);
			$axis = new Vector3(1, 0, 0);
			$right = new Vector3(0, 0, 1);
		}
		if($args[0] === "y"){
			array_shift($args);
		}
		if($args[0] === "z"){
			array_shift($args);
			$axis = new Vector3(0, 0, 1);
			$right = new Vector3(1, 0, 0);
		}
		if(!isset($args[0])){
			return 0;
		}
		$height = (float) array_shift($args);
		if(isset($args[0])){
			$selName = $args[0];
		}

		$loc = $session->getLocation();
		$shape = new class($loc->getLevel(), $loc, $loc->add($axis->multiply($height)), $axis,
			$loc->add($right->multiply($radius)), $radius, $radius, $radius) extends CircularFrustumShape implements IShape{
			public function getBaseShape() : Shape{
				return $this;
			}
		};
		$session->setSelection($selName, $shape);
		return 1;
	}

	private function overloadRatio(BuilderSession $session, array $args, string &$selName) : int{
		if(!isset($args[0])){
			return 0;
		}
		$ratio = (float) array_shift($args);
		$topFace = true;
		if(isset($args[0])){
			switch(mb_strtolower($args[0])){
				case "t":
				case "top":
					array_shift($args);
					break;
				case "b":
				case "base":
					array_shift($args);
					$topFace = false;
					break;
			}
		}
		if(isset($args[0])){
			$selName = array_shift($args);
		}

		$shape = $session->getSelection($selName);
		if($shape === null){
			$session->msg("You don't have a selection called \"$selName\"! '//cyl ratio' can only be used with an existing cylinder/cone/circular frustum selection.", BuilderSession::MSG_CLASS_ERROR);
			return 2;
		}
		$shape = $shape->getBaseShape();
		if(!($shape instanceof CircularFrustumShape)){
			$session->msg("Your \"$selName\" selection is not a cylinder/cone/circular frustum!", BuilderSession::MSG_CLASS_ERROR);
			return 2;
		}
		if(!$topFace && ($shape->getTop() === null || $shape->getTopRightRadius() || $shape->getTopFrontRadius() === null)){
			$session->msg("Your \"$selName\" selection does not have a complete top face yet, so you cannot use '//cyl ratio base' on it.", BuilderSession::MSG_CLASS_ERROR);
			return 2;
		}
		if($topFace && ($shape->getBaseRightRadius() === null || $shape->getTopRightRadius())){
			$session->msg("Your \"$selName\" selection does not have a complete top face yet, so you cannot use '//cyl ratio top' on it.", BuilderSession::MSG_CLASS_ERROR);
			return 2;
		}
		if($topFace){
			$shape->setTopRightRadius($shape->getBaseRightRadius() * $ratio);
			$shape->setTopFrontRadius($shape->getBaseFrontRadius() * $ratio);
		}else{
			if($shape->getTopRightRadius() === 0.0){
				$session->msg("Your \"$selName\" selection is a cone, hence this command does not make sense.", BuilderSession::MSG_CLASS_ERROR);
				return 2;
			}
			$shape->setBaseRightRadius($shape->getTopRightRadius() * $ratio);
			$shape->setBaseFrontRadius($shape->getTopFrontRadius() * $ratio);
		}
		return 1;
	}

	private function overloadOneRadius(BuilderSession $session, array $args, string &$selName) : int{
		if(!isset($args[1])){
			return 0;
		}
		$faceTop = true;
		if(!is_numeric($args[1])){
			$faceString = mb_strtolower(array_shift($args));

			if($faceString === "b" || $faceString === "base"){
				$faceTop = false;
			}elseif($faceString !== "t" && $faceString !== "top"){
				return 0;
			}
			if(!isset($args[1]) || !is_numeric($args[1])){
				return 0;
			}
		}
		$whichString = mb_strtolower($args[0]);
		switch($whichString){
			case "l":
			case"left":
			case"r":
			case"right":
				$whichRight = true;
				break;
			case"t":
			case"top":
			case"b":
			case"back":
				$whichRight = false;
				break;
			default:
				return 0;
		}
		$length = (float) $args[1];
		$preserveRatio = isset($args[2]) && in_array(mb_strtolower($args[2]), ["true", "on", "yes", "y", "t"], true);
		if(isset($args[3])){
			$selName = $args[3];
		}

		$shape = $session->getSelection($selName);
		if($shape !== null){
			$shape = $shape->getBaseShape();
		}
		if(!($shape instanceof CircularFrustumShape)){
			if($preserveRatio){
				if($shape !== null){
					$session->msg("Your \"$selName\" selection is not a cylinder/cone/circular frustum, hence there is no ratio to preserve", BuilderSession::MSG_CLASS_ERROR);
				}else{
					$session->msg("You don't have a selection called \"$selName\", hence there is no ratio to preserve", BuilderSession::MSG_CLASS_ERROR);
				}
				return 2;
			}
			$shape = new class($session->getLocation()->getLevel(), $session->getLocation()) extends CircularFrustumShape implements IShape{
				public function getBaseShape() : Shape{
					return $this;
				}
			};
		}

		if($whichRight){
			if($faceTop){
				$shape->setTopRightRadius($length);
			}else{
				$shape->setBaseRightRadius($length);
			}
		}else{
			if($faceTop){
				$shape->setTopFrontRadius($length);
			}else{
				$shape->setBaseFrontRadius($length);
			}
		}
		return 1;
	}

	private function overloadJustify(BuilderSession $session, array $args, string &$selName) : int{
		if(!isset($args[0])){
			return 0;
		}
		$whichString = mb_strtolower($args[0]);
		$face = 3;
		if($whichString === "top"){
			$face = 1;
			array_shift($args);
			$whichString = $args[0];
		}elseif($whichString === "base"){
			$face = 2;
			array_shift($args);
			$whichString = $args[0];
		}elseif($whichString === "both"){
			array_shift($args);
			$whichString = $args[0];
		}
		$whichRight = true;
		if($whichString === "f" || $whichString === "front" || $whichString === "b" || $whichString === "back"){
			$whichRight = false;
		}elseif($whichString !== "l" && $whichString !== "left" && $whichString !== "r" && $whichString !== "right"){
			return 0;
		}
		if(isset($args[1])){
			$selName = array_shift($args);
		}

		$shape = $session->getSelection($selName);
		if($shape === null){
			$session->msg("Your \"$selName\" selection is empty", BuilderSession::MSG_CLASS_ERROR);
			return 2;
		}
		if(!($shape instanceof CircularFrustumShape)){
			$session->msg("Your \"$selName\" selection is not a cylinder/cone/circular frustum", BuilderSession::MSG_CLASS_ERROR);
			return 2;
		}

		if($face & 1){ // justify base ellipse
			$baseUse = $whichRight ? $shape->getBaseFrontRadius() : $shape->getBaseRightRadius();
			if($baseUse === null){
				$session->msg("The base " . ($whichRight ? "front" : "right") . " radius has not been set", BuilderSession::MSG_CLASS_ERROR);
				return 2;
			}
		}
		if($face & 2){ // justify top ellipse
			$topUse = $whichRight ? $shape->getTopFrontRadius() : $shape->getTopRightRadius();
			if($topUse === null){
				$session->msg("The top " . ($whichRight ? "front" : "right") . " radius has not been set", BuilderSession::MSG_CLASS_ERROR);
				return 2;
			}
		}
		if(isset($baseUse)){
			if($whichRight){
				$shape->setBaseRightRadius($baseUse);
			}else{
				$shape->setBaseFrontRadius($baseUse);
			}
		}
		if(isset($topUse)){
			if($whichRight){
				$shape->setTopRightRadius($topUse);
			}else{
				$shape->setTopFrontRadius($topUse);
			}
		}

		return 1;
	}

	private function overloadNormalize(BuilderSession $session, array $args, string &$selName) : int{
		$preserveLength = false;
		if(isset($args[0])){
			$boolStr = mb_strtolower($args[0]);
			if(in_array($boolStr, ["true", "t", "yes", "y", "on"], true)){
				$preserveLength = true;
			}
			if(isset($args[1])){
				$selName = $args[1];
			}
		}

		$shape = $session->getSelection($selName);
		if($shape === null){
			$session->msg("You don't have a selection called \"$selName\"", BuilderSession::MSG_CLASS_ERROR);
			return 2;
		}
		if(!($shape instanceof CircularFrustumShape)){
			$session->msg("Your \"$selName\" selection is not a cylinder/cone/circular frustum", BuilderSession::MSG_CLASS_ERROR);
			return 2;
		}
		if(!$shape->isComplete()){
			$session->msg("Your \"$selName\" selection is incomplete, hence it cannot be normalized.", BuilderSession::MSG_CLASS_ERROR);
			return 2;
		}

		/** @noinspection NullPointerExceptionInspection */
		// variables:
		// uppercase = new, lowercase = original
		// n = normal, a = right, b = front, i.e. |a| X |n| = |b|
		// a 0 suffix implies that it is normalized
		// c = center
		$n = $shape->getNormal();
		$a = $shape->getRightDir();
		$c = $shape->getTop();
		$N = $c->subtract($shape->getBase())->normalize(); // uppercase implies it's new
		$rot = WEAMath::rotationMatrixBetween($n, $N);
		if($preserveLength){
			$A = WEAMath::matrixMultiplyVector($rot, $a);
			$shape->rotate($N, $A);
		}else{
			// FixMe implement
			$A = WEAMath::matrixMultiplyVector($rot, $a);
			$shape->rotate($N, $A);
			$session->msg("Non-length-preserving ellipse normalization (projection) is currently not supported. Length-preserving normalization (rotation) will be executed instead.", BuilderSession::MSG_CLASS_WARN);
		}

		return 1;
	}

	private function overloadCone(BuilderSession $session, array $args, string &$selName) : int{
		$isTop = true;
		if(isset($args[0])){
			if($args[0] === "base"){
				$isTop = false;
			}elseif($args[0] !== "top"){
				return 0;
			}

			if(isset($args[1])){
				$selName = $args[1];
			}
		}

		$shape = $session->getSelection($selName);
		if($shape === null){
			$session->msg("You don't have a selection called \"$selName\" to convert into a cone", BuilderSession::MSG_CLASS_ERROR);
			return 2;
		}
		if(!($shape instanceof CircularFrustumShape)){
			$session->msg("Your \"$selName\" selection is not a cylinder/circular frustum", BuilderSession::MSG_CLASS_ERROR);
			return 2;
		}

		if($isTop){
			$shape->setTopRightRadius(0.0)->setTopFrontRadius(0.0);
		}else{
			if(!$this->notNull($shape->getTop(), $shape->getNormal(), $shape->getRightDir(), $shape->getTopRightRadius(), $shape->getTopFrontRadius())){
				$session->msg("Your \"$selName\" selection does not have a complete top ellipse, hence the base ellipse cannot be converted into a cone tip.", BuilderSession::MSG_CLASS_ERROR);
				return 2;
			}
			$oldShape = $shape;
			/** @noinspection NullPointerExceptionInspection */
			$shape = new class($oldShape->getLevel($session->getPlugin()->getServer()), $oldShape->getTop(), $oldShape->getBase(), $oldShape->getNormal(), $oldShape->getTop()->add($oldShape->getRightDir()->multiply($oldShape->getTopRightRadius())), $oldShape->getTopFrontRadius(), 0.0, 0.0) extends CircularFrustumShape implements IShape{
				public function getBaseShape() : Shape{
					return $this;
				}
			};
			$session->setSelection($selName, $shape);
		}
		return 1;
	}

	private function overloadFlip(BuilderSession $session, array $args, string &$selName) : int{
		if(isset($args[0])){
			$selName = $args[0];
		}

		$shape = $session->getSelection($selName);
		if($shape === null){
			$session->msg("You don't have a selection called \"$selName\" to flip", BuilderSession::MSG_CLASS_ERROR);
			return 2;
		}
		if(!($shape instanceof CircularFrustumShape)){
			$session->msg("Your \"$selName\" selection is not a cylinder/circular frustum", BuilderSession::MSG_CLASS_ERROR);
			return 2;
		}
		if($shape->getTop() === null){
			$session->msg("Your \"$selName\" selection does not have a top center defined, so it cannot be flipped.", BuilderSession::MSG_CLASS_ERROR);
			return 2;
		}

		$base = $shape->getTop();
		$top = $shape->getBase();
		$baseFrontRadius = $shape->getTopFrontRadius();
		$baseRightRadius = $shape->getTopRightRadius();
		$topFrontRadius = $shape->getBaseFrontRadius();
		$topRightRadius = $shape->getBaseRightRadius();
		$shape->setBase($base);
		$shape->setTop($top);
		$shape->setBaseFrontRadius($baseFrontRadius);
		$shape->setBaseRightRadius($baseRightRadius);
		$shape->setTopFrontRadius($topFrontRadius);
		$shape->setTopRightRadius($topRightRadius);

		return 1;
	}

	private function notNull(...$values) : bool{
		foreach($values as $value){
			if($value === null){
				return false;
			}
		}
		return true;
	}
}
