<?php

declare(strict_types=1);

namespace NexusFactions\manager;

use NexusFactions\Main;
use pocketmine\world\Position;

class ClaimManager {
    
    private Main $plugin;
    private array $claims = [];

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function claimChunk(string $faction, int $chunkX, int $chunkZ, string $world): bool {
        $key = $this->getChunkKey($chunkX, $chunkZ, $world);
        
        if (isset($this->claims[$key])) {
            return false;
        }
        
        $this->claims[$key] = $faction;
        return true;
    }

    public function unclaimChunk(int $chunkX, int $chunkZ, string $world): bool {
        $key = $this->getChunkKey($chunkX, $chunkZ, $world);
        
        if (!isset($this->claims[$key])) {
            return false;
        }
        
        unset($this->claims[$key]);
        return true;
    }

    public function getChunkFaction(int $chunkX, int $chunkZ, string $world): ?string {
        $key = $this->getChunkKey($chunkX, $chunkZ, $world);
        return $this->claims[$key] ?? null;
    }

    public function isChunkClaimed(int $chunkX, int $chunkZ, string $world): bool {
        $key = $this->getChunkKey($chunkX, $chunkZ, $world);
        return isset($this->claims[$key]);
    }

    public function getFactionClaims(string $faction): array {
        $claims = [];
        foreach ($this->claims as $key => $factionName) {
            if (strtolower($factionName) === strtolower($faction)) {
                $claims[] = $key;
            }
        }
        return $claims;
    }

    public function getChunkFactionFromPosition(Position $position): ?string {
        $chunkX = $position->getFloorX() >> 4;
        $chunkZ = $position->getFloorZ() >> 4;
        $world = $position->getWorld()->getFolderName();
        return $this->getChunkFaction($chunkX, $chunkZ, $world);
    }

    private function getChunkKey(int $chunkX, int $chunkZ, string $world): string {
        return $world . ":" . $chunkX . ":" . $chunkZ;
    }

    public function getAllClaims(): array {
        return $this->claims;
    }
}
