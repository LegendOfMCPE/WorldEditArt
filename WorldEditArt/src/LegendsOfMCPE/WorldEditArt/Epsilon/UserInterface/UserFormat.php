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

namespace LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface;

use LegendsOfMCPE\WorldEditArt\Epsilon\IShape;
use LegendsOfMCPE\WorldEditArt\Epsilon\LibgeomAdapter\ShapeWrapper;
use pocketmine\level\Level;
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use sofe\libgeom\Shape;
use sofe\libgeom\shapes\CuboidShape;

abstract class UserFormat{
	const FORMAT_USER_DEFINITION = 1;
	const FORMAT_USER_RANGE = 2;
	const FORMAT_DEBUG = 3;

	public static function describeShape(Server $server, IShape $shape, int $format) : string{
		return UserFormat::describeImpl($server, $shape, $format);
	}

	/**
	 * @param Server                    $server
	 * @param IShape|Shape|ShapeWrapper $shape
	 * @param int                       $format
	 *
	 * @return string
	 */
	private static function describeImpl(Server $server, $shape, int $format) : string{
		if($shape instanceof ShapeWrapper){
			return UserFormat::describeImpl($server, $shape->getBaseShape(), $format);
		}
		assert($shape instanceof Shape);
		$baseColor = TextFormat::GOLD;
		$em1 = TextFormat::AQUA;
		$em2 = TextFormat::LIGHT_PURPLE;
		$em3 = TextFormat::BLUE;

//		if($shape instanceof CuboidShape){
//			switch($format){
//				case UserFormat::FORMAT_USER_DEFINITION:
//					return "{$baseColor}Cuboid ({$em1}pos1: " . UserFormat::formatVector($shape->getFrom()) . "{$baseColor}, " .
//						"{$em2}pos2: " . UserFormat::formatVector($shape->getTo()) .
//						" {$baseColor}in world {$em3}" . UserFormat::nameLevel($shape->getLevel($server));
//				case UserFormat::FORMAT_USER_RANGE:
//					return "{$baseColor}Cuboid {$em1}from " . UserFormat::formatVector($shape->getMin()) .
//						" {$em2}to pos2: " . UserFormat::formatVector($shape->getMax()) .
//						" {$baseColor}in world {$em3}" . UserFormat::nameLevel($shape->getLevel($server));
//				case UserFormat::FORMAT_DEBUG:
//					return "Cuboid(from={$shape->getFrom()}, to={$shape->getTo()}, level=" . UserFormat::nameLevel($shape->getLevel($server)) . ")";
//			}
//			throw UserFormat::unknownFormat($format);
//		}

		$namespace = (new \ReflectionClass(Shape::class))->getNamespaceName();
		return str_replace($namespace, "", serialize($shape)); // TODO will be fixed in multi-lang support
		// TODO handle incomplete shapes
	}

	public static function formatLocation(Location $location, string $normalColor) : string{
		return TextFormat::AQUA . UserFormat::formatVector($location) .
			TextFormat::LIGHT_PURPLE . " (" . UserFormat::yawAsReducedBearing($location->yaw) . ", " . UserFormat::pitchAsBearing($location->pitch) .
			") {$normalColor}in world " . TextFormat::BLUE . UserFormat::nameLevel($location->getLevel()) . $normalColor;
	}

	/**
	 * @param float $yaw the azimuth in <em>degrees</em>, i.e. number of degrees clockwise from south
	 *
	 * @return string
	 */
	public static function yawAsReducedBearing(float $yaw) : string{
		while($yaw >= 360){
			$yaw -= 360;
		}
		while($yaw < 0){
			$yaw += 360;
		}

		if($yaw < 90){
			return "S " . round($yaw, 2) . "° W";
		}
		if($yaw < 180){
			return "N " . round(180 - $yaw, 2) . "° W";
		}
		if($yaw < 270){
			return "N " . round($yaw - 180, 2) . "° E";
		}
		return "S " . round(360 - $yaw, 2) . "° E";
	}

	/**
	 * @param float $pitch the pitch in <em>degrees</em>, i.e. number of degrees downwards from the horizontal
	 *
	 * @return string
	 */
	public static function pitchAsBearing(float $pitch) : string{
		return $pitch > 0 ? (round($pitch, 2) . "° down") : (round(-$pitch, 2) . "° up");
	}

	public static function formatPosition(Position $position, string $normalColor) : string{
		return TextFormat::AQUA . UserFormat::formatVector($position) . " {$normalColor}in world " . TextFormat::LIGHT_PURPLE . UserFormat::nameLevel($position->getLevel()) . $normalColor;
	}

	public static function formatVector(Vector3 $vector) : string{
		return "(" . round($vector->x, 2) . ", " . round($vector->y, 2) . ", " . round($vector->z, 2) . ")";
	}

	public static function formatDelta(Vector3 $delta) : string{
		$changes = [];
		if($delta->x > 0){
			$changes["east"] = $delta->x;
		}elseif($delta->x < 0){
			$changes["west"] = -$delta->x;
		}
		if($delta->y > 0){
			$changes["up"] = $delta->y;
		}elseif($delta->y < 0){
			$changes["down"] = -$delta->y;
		}
		if($delta->z > 0){
			$changes["south"] = $delta->z;
		}elseif($delta->z < 0){
			$changes["north"] = -$delta->z;
		}
		if($changes === []){
			return "0 blocks";
		}

		$phrases = [];
		foreach($changes as $side => $length){
			$phrases[] = round($length, 2) . " blocks $side";
		}
		return implode(", ", $phrases);
	}

	public static function nameLevel(Level $level) : string{
		return $level->getFolderName() . ($level->getFolderName() === $level->getName() ? "" : " ({$level->getName()})");
	}
}
