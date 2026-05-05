<?php

declare(strict_types=1);

namespace NexusFactions;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use NexusFactions\manager\FactionManager;
use NexusFactions\manager\IslandManager;
use NexusFactions\manager\ClaimManager;
use NexusFactions\command\FactionCommand;
use NexusFactions\listener\EventListener;

class Main extends PluginBase {
    use SingletonTrait;

    private FactionManager $factionManager;
    private IslandManager $islandManager;
    private ClaimManager $claimManager;

    protected function onLoad(): void {
        self::setInstance($this);
    }

    protected function onEnable(): void {
        $this->saveDefaultConfig();
        $this->saveResource("messages.yml");
        
        // Créer les dossiers nécessaires
        @mkdir($this->getDataFolder() . "factions");
        @mkdir($this->getDataFolder() . "islands");
        @mkdir($this->getDataFolder() . "players");
        
        // Initialiser les managers
        $this->factionManager = new FactionManager($this);
        $this->islandManager = new IslandManager($this);
        $this->claimManager = new ClaimManager($this);
        
        // Enregistrer les commandes
        $this->getServer()->getCommandMap()->register("nexusfactions", new FactionCommand($this));
        $this->getServer()->getCommandMap()->register("nexusfactions", new \NexusFactions\command\FactionAdminCommand($this));
        
        // Enregistrer les événements
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        
        $this->getLogger()->info("§aNexusFactions activé avec succès!");
    }

    protected function onDisable(): void {
        $this->factionManager->saveAll();
        $this->islandManager->saveAll();
        $this->getLogger()->info("§cNexusFactions désactivé!");
    }

    public function getFactionManager(): FactionManager {
        return $this->factionManager;
    }

    public function getIslandManager(): IslandManager {
        return $this->islandManager;
    }

    public function getClaimManager(): ClaimManager {
        return $this->claimManager;
    }
}
