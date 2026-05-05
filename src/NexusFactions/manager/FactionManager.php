<?php

declare(strict_types=1);

namespace NexusFactions\manager;

use NexusFactions\Main;
use NexusFactions\faction\Faction;
use pocketmine\player\Player;

class FactionManager {
    
    private Main $plugin;
    private array $factions = [];
    private array $playerFactions = [];

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
        $this->loadAll();
    }

    public function createFaction(string $name, string $leader): ?Faction {
        if ($this->factionExists($name)) {
            return null;
        }
        
        $faction = new Faction($name, $leader);
        $this->factions[strtolower($name)] = $faction;
        $this->playerFactions[strtolower($leader)] = strtolower($name);
        $this->saveFaction($faction);
        return $faction;
    }

    public function deleteFaction(string $name): bool {
        $factionName = strtolower($name);
        if (!isset($this->factions[$factionName])) {
            return false;
        }
        
        $faction = $this->factions[$factionName];
        
        // Retirer tous les membres
        foreach ($faction->getMembers() as $member) {
            unset($this->playerFactions[strtolower($member)]);
        }
        
        // Supprimer l'île si elle existe
        if ($faction->getIsland() !== null) {
            $this->plugin->getIslandManager()->deleteIsland($faction->getIsland());
        }
        
        unset($this->factions[$factionName]);
        
        $file = $this->plugin->getDataFolder() . "factions/" . $factionName . ".json";
        if (file_exists($file)) {
            @unlink($file);
        }
        
        return true;
    }

    public function getFaction(string $name): ?Faction {
        return $this->factions[strtolower($name)] ?? null;
    }

    public function getPlayerFaction(string $player): ?Faction {
        $factionName = $this->playerFactions[strtolower($player)] ?? null;
        if ($factionName === null) {
            return null;
        }
        return $this->getFaction($factionName);
    }

    public function factionExists(string $name): bool {
        return isset($this->factions[strtolower($name)]);
    }

    public function isInFaction(string $player): bool {
        return isset($this->playerFactions[strtolower($player)]);
    }

    public function getAllFactions(): array {
        return $this->factions;
    }

    public function saveFaction(Faction $faction): void {
        $file = $this->plugin->getDataFolder() . "factions/" . strtolower($faction->getName()) . ".json";
        file_put_contents($file, json_encode($faction->toArray(), JSON_PRETTY_PRINT));
    }

    public function saveAll(): void {
        foreach ($this->factions as $faction) {
            $this->saveFaction($faction);
        }
    }

    private function loadAll(): void {
        $dir = $this->plugin->getDataFolder() . "factions/";
        if (!is_dir($dir)) {
            return;
        }
        
        foreach (scandir($dir) as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === "json") {
                $data = json_decode(file_get_contents($dir . $file), true);
                if ($data !== null) {
                    $faction = Faction::fromArray($data);
                    $this->factions[strtolower($faction->getName())] = $faction;
                    
                    foreach ($faction->getMembers() as $member) {
                        $this->playerFactions[strtolower($member)] = strtolower($faction->getName());
                    }
                }
            }
        }
    }

    public function updatePlayerFaction(string $player, ?string $factionName): void {
        if ($factionName === null) {
            unset($this->playerFactions[strtolower($player)]);
        } else {
            $this->playerFactions[strtolower($player)] = strtolower($factionName);
        }
    }
}
