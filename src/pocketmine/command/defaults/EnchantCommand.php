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

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\utils\TextFormat;

class EnchantCommand extends VanillaCommand {

	/**
	 * EnchantCommand constructor.
	 *
	 * @param $name
	 */
	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.enchant.description",
			"%pocketmine.command.enchant.usage"
		);
		$this->setPermission("pocketmine.command.enchant");
	}

	/**
	 * @param CommandSender $sender
	 * @param string $currentAlias
	 * @param array $args
	 *
	 * @return bool
	 */
	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) < 2){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));

			return true;
		}

		$player = $sender->getServer()->getPlayer($args[0]);

		if($player === null){
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.player.notFound"));

			return true;
		}

		$enchantId = $args[1];
		$enchantLevel = isset($args[2]) ? (int)$args[2] : 1;

		$enchantment = Enchantment::getEnchantment($enchantId);
		if($enchantment->getId() === Enchantment::TYPE_INVALID){
			$enchantment = Enchantment::getEnchantmentByName($enchantId);
			if($enchantment->getId() === Enchantment::TYPE_INVALID){
				$sender->sendMessage(new TranslationContainer("commands.enchant.notFound", [$enchantment->getId()]));

				return true;
			}
		}
		$id = $enchantment->getId();
		$maxLevel = Enchantment::getEnchantMaxLevel($id);
		if($enchantLevel > $maxLevel or $enchantLevel <= 0){
			$sender->sendMessage(new TranslationContainer("commands.enchant.maxLevel", [$maxLevel]));

			return true;
		}
		$enchantment->setLevel($enchantLevel);

		$item = $player->getInventory()->getItemInHand();

		if($item->getId() <= 0){
			$sender->sendMessage(new TranslationContainer("commands.enchant.noItem"));

			return true;
		}

		if(Enchantment::getEnchantAbility($item) === 0){
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.enchant.cantEnchant"));

			return true;
		}

		$item->addEnchantment($enchantment);
		$player->getInventory()->setItemInHand($item);


		self::broadcastCommandMessage($sender, new TranslationContainer("%commands.enchant.success"));

		return true;
	}
}