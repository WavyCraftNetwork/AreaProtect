<?php

declare(strict_types=1);

namespace wavycraft\areaprotect;

use pocketmine\plugin\PluginBase;

use wavycraft\areaprotect\event\EventListener;

use wavycraft\areaprotect\command\AreaCommand;

use wavycraft\areaprotect\utils\AreaManager;

class Loader extends PluginBase {

    private static $instance;

    protected function onLoad() : void{
        self::$instance = $this;
    }

    protected function onEnable() : void{
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        
        $this->getServer()->getCommandMap()->register("AreaProtect", new AreaCommand());

        AreaManager::getInstance()->loadAreas();
    }

    protected function onDisable() : void{
        AreaManager::getInstance()->saveAreas();
    }

    public static function getInstance() : self{
        return self::$instance;
    }
}
