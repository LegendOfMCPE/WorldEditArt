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

namespace LegendsOfMCPE\WorldEditArt\Epsilon\Utils;

use pocketmine\math\Vector3;

/**
 * A 3x3 matrix is represented as a Vector3[3] array, and a 1x3 matrix is represented as a Vector3 object.
 */
class WEAMath{
	public static function vectorToYawPitch(Vector3 $vector, float &$yaw, float &$pitch){
		if(((float) $vector->lengthSquared()) !== 1.0){
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

	public static function projectionOn(Vector3 $object, Vector3 $onto) : Vector3{
		return $onto->normalize()->multiply($object->dot($onto));
	}

	public static function vectorAngleRadians(Vector3 $a, Vector3 $b) : float{
		return acos($a->dot($b) / ($a->length() * $b->length())); // a value between 0 and M_PI
	}

	/**
	 * @param Vector3 $from
	 * @param Vector3 $to
	 *
	 * @return Vector3[] (size=3)
	 */
	public static function rotationMatrixBetween(Vector3 $from, Vector3 $to) : array{
		$from = $from->normalize();
		$to = $to->normalize();
		$rotAxis = $from->cross($to); // $rotAxis is the axis of rotation by definition of "rotAxis product"
		$cosRotAngle = (float) $from->dot($to); // |a| = |b| = 1, a . b = |a| |b| cos(theta) = cos(theta), hence $rotAngle = rotation angle

		static $identityMatrix = null;
		if($identityMatrix === null){
			$identityMatrix = [new Vector3(1), new Vector3(0, 1), new Vector3(0, 0, 1)];
		}

		/** @noinspection PhpStrictTypeCheckingInspection */ // blame the PHP doc
		if((float) abs($cosRotAngle) === 1.0){ // $from and $to are parallel
			return WEAMath::matrixMultiplyScalar($identityMatrix, $cosRotAngle);
		}

		$crossMatrix = [
			new Vector3(0, -$rotAxis->z, $rotAxis->y),
			new Vector3($rotAxis->z, 0, -$rotAxis->x),
			new Vector3(-$rotAxis->y, $rotAxis->x, 0),
		];
		$crossSquared = WEAMath::matrixMultiplyMatrix($crossMatrix, $crossMatrix);

		$matrix = WEAMath::matrixAddMatrix($identityMatrix, $crossMatrix);
		$matrix = WEAMath::matrixAddMatrix($matrix, WEAMath::matrixMultiplyScalar($crossSquared, 1 / (1 + $cosRotAngle)));


		return $matrix;
	}

	/**
	 * @param Vector3[] $m1
	 * @param Vector3[] $m2
	 *
	 * @return Vector3[]
	 */
	public static function matrixAddMatrix(array $m1, array $m2) : array{
		return [
			$m1[0]->add($m2[0]),
			$m1[1]->add($m2[1]),
			$m1[2]->add($m2[2]),
		];
	}

	/**
	 * @param Vector3[] $matrix
	 * @param int|float $scalar
	 *
	 * @return Vector3[]
	 */
	public static function matrixMultiplyScalar(array $matrix, $scalar) : array{
		return [
			$matrix[0]->multiply($scalar),
			$matrix[1]->multiply($scalar),
			$matrix[2]->multiply($scalar),
		];
	}

	/**
	 * @param Vector3[] $m1
	 * @param Vector3[] $m2
	 *
	 * @return Vector3[]
	 */
	public static function matrixMultiplyMatrix(array $m1, array $m2) : array{
		$m2t = WEAMath::matrixTranspose($m2);
		return [
			new Vector3($m1[0]->dot($m2t[0]), $m1[0]->dot($m2t[1]), $m2t[0]->dot($m2t[2])),
			new Vector3($m1[1]->dot($m2t[0]), $m1[1]->dot($m2t[1]), $m2t[1]->dot($m2t[2])),
			new Vector3($m1[2]->dot($m2t[0]), $m1[2]->dot($m2t[1]), $m2t[2]->dot($m2t[2])),
		];
	}

	/**
	 * @param Vector3[] $matrix
	 *
	 * @return Vector3[]
	 */
	public static function matrixTranspose(array $matrix) : array{
		return [
			new Vector3($matrix[0]->x, $matrix[1]->x, $matrix[2]->x),
			new Vector3($matrix[0]->y, $matrix[1]->y, $matrix[2]->y),
			new Vector3($matrix[0]->z, $matrix[1]->z, $matrix[2]->z),
		];
	}

	/**
	 * @param Vector3[] $matrix
	 * @param Vector3   $vector
	 *
	 * @return Vector3
	 */
	public static function matrixMultiplyVector(array $matrix, Vector3 $vector) : Vector3{
		return new Vector3(
			$matrix[0]->dot($vector),
			$matrix[1]->dot($vector),
			$matrix[2]->dot($vector)
		);
	}
}
