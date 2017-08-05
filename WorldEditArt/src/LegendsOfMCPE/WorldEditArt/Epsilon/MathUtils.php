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

namespace LegendsOfMCPE\WorldEditArt\Epsilon;

use pocketmine\math\Vector3;

class MathUtils{
	public static function vectorToYawPitch(Vector3 $vector, float &$yaw, float &$pitch){
		if($vector->lengthSquared() != 1){
			$vector = $vector->normalize();
		}
		$yaw = rad2deg(atan2($vector->z, -$vector->x));
		$pitch = rad2deg(asin(-$vector->y));
	}

	public static function yawPitchToVector(float $yaw, float $pitch) : Vector3{
		static $vector = null;
		if($vector === null){
			$vector = new Vector3;
		}

		$y = -sin(deg2rad($pitch));
		$xz = cos(deg2rad($pitch));
		$x = -$xz * sin(deg2rad($yaw));
		$z = $xz * cos(deg2rad($yaw));

		return $vector->setComponents($x, $y, $z)->normalize();
	}
}
