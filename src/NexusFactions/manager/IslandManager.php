<?php

declare(strict_types=1);

namespace NexusFactions\manager;

use NexusFactions\Main;
use NexusFactions\island\Island;
use pocketmine\world\Position;

class IslandManager {
    
    private Main $plugin;
    private array $islands = [];

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
        $this->loadAll();
    }

    public function createIsland(string $faction, Position $spawnPoint, int $size = 100): Island {
        $id = uniqid("island_");
        $island = new Island($id, $faction, $spawnPoint, $size);
        $this->islands[$id] = $island;
        $this->saveIsland($island);
        return $island;
    }

    public function deleteIsland(string $id): bool {
        if (!isset($this->islands[$id])) {
            return false;
        }
        
        unset($this->islands[$id]);
        
        $file = $this->plugin->getDataFolder() . "islands/" . $id . ".json";
        if (file_exists($file)) {
            @unlink($file);
        }
        
        return true;
    }

    public function getIsland(string $id): ?Island {
        return $this->islands[$id] ?? null;
    }

    public function getIslandByFaction(string $faction): ?Island {
        foreach ($this->islands as $island) {
            if (strtolower($island->getFaction()) === strtolower($faction)) {
                return $island;
            }
        }
        return null;
    }

    public function getAllIslands(): array {
        return $this->islands;
    }

    public function saveIsland(Island $island): void {
        $file = $this->plugin->getDataFolder() . "islands/" . $island->getId() . ".json";
        file_put_contents($file, json_encode($island->toArray(), JSON_PRETTY_PRINT));
    }

    public function saveAll(): void {
        foreach ($this->islands as $island) {
            $this->saveIsland($island);
        }
    }

    private function loadAll(): void {
        $dir = $this->plugin->getDataFolder() . "islands/";
        if (!is_dir($dir)) {
            return;
        }
        
        foreach (scandir($dir) as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === "json") {
                $data = json_decode(file_get_contents($dir . $file), true);
                if ($data !== null) {
                    $island = Island::fromArray($data, $this->plugin->getServer());
                    if ($island !== null) {
                        $this->islands[$island->getId()] = $island;
                    }
                }
            }
        }
    }
}
