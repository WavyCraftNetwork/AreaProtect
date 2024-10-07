<?php

declare(strict_types=1);

namespace wavycraft\areaprotect\event;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityShootBowEvent;

use pocketmine\player\Player;
use pocketmine\entity\Entity;

use pocketmine\math\Vector3;

use wavycraft\areaprotect\utils\AreaManager;

class EventListener implements Listener {

    private $areaManager;

    public function __construct() {
        $this->areaManager = AreaManager::getInstance();
    }

    public function onPlayerInteract(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $pos = $block->getPosition();
        $area = $this->areaManager->getAreaAtPosition($pos);
 
        if ($this->areaManager->isSettingPosition($player)) {
            $this->areaManager->setPosition($player, $pos);
            $event->cancel();
        }

        if ($area && !$area['flags']['interaction']) {
            $player->sendMessage("Interacting is disabled in this area.");
            $event->cancel();
        }
    }

    public function onBlockPlace(BlockPlaceEvent $event) {
        $player = $event->getPlayer();
        $block = $event->getBlockAgainst();
        $pos = $block->getPosition();
        $area = $this->areaManager->getAreaAtPosition($pos);

        if ($area && !$area['flags']['block_place']) {
            $player->sendMessage("Block placing is disabled in this area.");
            $event->cancel();
        }
    }

    public function onBlockBreak(BlockBreakEvent $event) {
        $player = $event->getPlayer();
        $pos = $event->getBlock()->getPosition();
        $area = $this->areaManager->getAreaAtPosition($pos);

        if ($area && !$area['flags']['block_break']) {
            $player->sendMessage("Block breaking is disabled in this area.");
            $event->cancel();
        }
    }

    public function onEntityDamage(EntityDamageByEntityEvent $event) {
        $entity = $event->getEntity();
        $damager = $event->getDamager();
        if ($damager instanceof Player) {
            $area = $this->areaManager->getAreaAtPosition($entity->getPosition());
            if ($area && !$area['flags']['pvp']) {
                $damager->sendMessage("pvp is disabled in this area.");
                $event->cancel();
            }
        }
    }


    public function onPlayerDropItem(PlayerDropItemEvent $event) {
        $player = $event->getPlayer();
        $area = $this->areaManager->getAreaAtPosition($player->getPosition());
        if ($area && !$area['flags']['item_drop']) {
            $player->sendMessage("Dropping items is disabled in this area.");
            $event->cancel();
        }
    }

    public function onPlayerItemUse(PlayerItemUseEvent $event) {
        $player = $event->getPlayer();
        $area = $this->areaManager->getAreaAtPosition($player->getPosition());
        if ($area && !$area['flags']['item_use']) {
            $player->sendMessage("Using items is disabled in this area.");
            $event->cancel();
        }
    }

    public function onEntityShootBow(EntityShootBowEvent $event) {
         $shooter = $event->getEntity();
         if ($shooter instanceof Player) {
            $area = $this->areaManager->getAreaAtPosition($shooter->getPosition());
            if ($area && !$area['flags']['shoot_bow']) {
                $shooter->sendMessage("Shooting bows is disabled in this area.");
                $event->cancel();
            }
        }
    }
}
