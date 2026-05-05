<?php

declare(strict_types=1);

namespace NexusFactions\island;

use pocketmine\world\Position;

class Island {
    
    private string $id;
    private string $faction;
    private Position $spawnPoint;
    private string $worldName;
    private int $size;
    private array $members = [];
    private bool $locked = false;
    private int $createdAt;

    public function __construct(string $id, string $faction, Position $spawnPoint, int $size = 100) {
        $this->id = $id;
        $this->faction = $faction;
        $this->spawnPoint = $spawnPoint;
        $this->worldName = $spawnPoint->getWorld()->getFolderName();
        $this->size = $size;
        $this->createdAt = time();
    }

    public function getId(): string {
        return $this->id;
    }

    public function getFaction(): string {
        return $this->faction;
    }

    public function getSpawnPoint(): Position {
        return $this->spawnPoint;
    }

    public function setSpawnPoint(Position $position): void {
        $this->spawnPoint = $position;
    }

    public function getWorldName(): string {
        return $this->worldName;
    }

    public function getSize(): int {
        return $this->size;
    }

    public function setSize(int $size): void {
        $this->size = $size;
    }

    public function getMembers(): array {
        return $this->members;
    }

    public function addMember(string $player): void {
        if (!in_array($player, $this->members)) {
            $this->members[] = $player;
        }
    }

    public function removeMember(string $player): void {
        $key = array_search($player, $this->members);
        if ($key !== false) {
            unset($this->members[$key]);
            $this->members = array_values($this->members);
        }
    }

    public function isMember(string $player): bool {
        return in_array($player, $this->members);
    }

    public function isLocked(): bool {
        return $this->locked;
    }

    public function setLocked(bool $locked): void {
        $this->locked = $locked;
    }

    public function getCreatedAt(): int {
        return $this->createdAt;
    }

    public function toArray(): array {
        return [
            "id" => $this->id,
            "faction" => $this->faction,
            "spawnPoint" => [
                "x" => $this->spawnPoint->getX(),
                "y" => $this->spawnPoint->getY(),
                "z" => $this->spawnPoint->getZ(),
                "world" => $this->worldName
            ],
            "size" => $this->size,
            "members" => $this->members,
            "locked" => $this->locked,
            "createdAt" => $this->createdAt
        ];
    }

    public static function fromArray(array $data, \pocketmine\Server $server): ?Island {
        $world = $server->getWorldManager()->getWorldByName($data["spawnPoint"]["world"]);
        if ($world === null) {
            return null;
        }
        
        $position = new Position(
            $data["spawnPoint"]["x"],
            $data["spawnPoint"]["y"],
            $data["spawnPoint"]["z"],
            $world
        );
        
        $island = new Island(
            $data["id"],
            $data["faction"],
            $position,
            $data["size"] ?? 100
        );
        
        $island->members = $data["members"] ?? [];
        $island->locked = $data["locked"] ?? false;
        $island->createdAt = $data["createdAt"] ?? time();
        
        return $island;
    }
}
