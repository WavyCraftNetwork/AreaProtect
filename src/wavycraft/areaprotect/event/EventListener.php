<?php

declare(strict_types=1);

namespace wavycraft\areaprotect\event;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;

use pocketmine\player\Player;

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
 
        if ($this->areaManager->isSettingPosition($player)) {
            $this->areaManager->setPosition($player, $pos);
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
}