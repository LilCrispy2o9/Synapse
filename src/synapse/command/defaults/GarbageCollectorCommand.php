<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

namespace synapse\command\defaults;

use synapse\command\CommandSender;
use synapse\utils\TextFormat;


class GarbageCollectorCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%synapse.command.gc.description",
			"%synapse.command.gc.usage"
		);
		$this->setPermission("synapse.command.gc");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		$chunksCollected = 0;
		$entitiesCollected = 0;
		$tilesCollected = 0;

		$memory = memory_get_usage();

		foreach($sender->getServer()->getLevels() as $level){
			$diff = [count($level->getChunks()), count($level->getEntities()), count($level->getTiles())];
			$level->doChunkGarbageCollection();
			$level->unloadChunks(true);
			$chunksCollected += $diff[0] - count($level->getChunks());
			$entitiesCollected += $diff[1] - count($level->getEntities());
			$tilesCollected += $diff[2] - count($level->getTiles());
			$level->clearCache(true);
		}

		$cyclesCollected = $sender->getServer()->getMemoryManager()->triggerGarbageCollector();
		$sender->sendMessage(TextFormat::GREEN . "---- " . TextFormat::WHITE . "%synapse.command.gc.title" . TextFormat::GREEN . " ----");
		$sender->sendMessage(TextFormat::GOLD . "%synapse.command.gc.chunks" . TextFormat::RED . \number_format($chunksCollected));
		$sender->sendMessage(TextFormat::GOLD . "%synapse.command.gc.entities" . TextFormat::RED . \number_format($entitiesCollected));
		$sender->sendMessage(TextFormat::GOLD . "%synapse.command.gc.tiles" . TextFormat::RED . \number_format($tilesCollected));
		$sender->sendMessage(TextFormat::GOLD . "%synapse.command.gc.cycles" . TextFormat::RED . \number_format($cyclesCollected));
		$sender->sendMessage(TextFormat::GOLD . "%synapse.command.gc.memory" . TextFormat::RED . \number_format(\round((($memory - \memory_get_usage()) / 1024) / 1024, 2))." MB");
		return true;
	}
}
