<?php

declare(strict_types=1);

namespace NexusFactions\faction;

use pocketmine\player\Player;

class Faction {
    
    private string $name;
    private string $leader;
    private array $members = [];
    private array $officers = [];
    private array $allies = [];
    private array $enemies = [];
    private int $power = 0;
    private int $maxPower = 100;
    private string $description = "";
    private bool $open = false;
    private ?string $island = null;
    private array $claims = [];
    private int $money = 0;
    private array $invites = [];
    private int $createdAt;

    public function __construct(string $name, string $leader) {
        $this->name = $name;
        $this->leader = $leader;
        $this->members[] = $leader;
        $this->createdAt = time();
    }

    public function getName(): string {
        return $this->name;
    }

    public function getLeader(): string {
        return $this->leader;
    }

    public function setLeader(string $leader): void {
        $this->leader = $leader;
    }

    public function getMembers(): array {
        return $this->members;
    }

    public function addMember(string $player): void {
        if (!$this->isMember($player)) {
            $this->members[] = $player;
        }
    }

    public function removeMember(string $player): void {
        $key = array_search($player, $this->members);
        if ($key !== false) {
            unset($this->members[$key]);
            $this->members = array_values($this->members);
        }
        // Retirer aussi des officiers si présent
        $this->demoteOfficer($player);
    }

    public function isMember(string $player): bool {
        return in_array($player, $this->members);
    }

    public function isLeader(string $player): bool {
        return $this->leader === $player;
    }

    public function getOfficers(): array {
        return $this->officers;
    }

    public function promoteOfficer(string $player): void {
        if ($this->isMember($player) && !$this->isOfficer($player)) {
            $this->officers[] = $player;
        }
    }

    public function demoteOfficer(string $player): void {
        $key = array_search($player, $this->officers);
        if ($key !== false) {
            unset($this->officers[$key]);
            $this->officers = array_values($this->officers);
        }
    }

    public function isOfficer(string $player): bool {
        return in_array($player, $this->officers);
    }

    public function getPower(): int {
        return $this->power;
    }

    public function setPower(int $power): void {
        $this->power = min($power, $this->maxPower);
    }

    public function addPower(int $amount): void {
        $this->setPower($this->power + $amount);
    }

    public function removePower(int $amount): void {
        $this->power = max(0, $this->power - $amount);
    }

    public function getMaxPower(): int {
        return $this->maxPower;
    }

    public function setMaxPower(int $maxPower): void {
        $this->maxPower = $maxPower;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function setDescription(string $description): void {
        $this->description = $description;
    }

    public function isOpen(): bool {
        return $this->open;
    }

    public function setOpen(bool $open): void {
        $this->open = $open;
    }

    public function getIsland(): ?string {
        return $this->island;
    }

    public function setIsland(?string $island): void {
        $this->island = $island;
    }

    public function getClaims(): array {
        return $this->claims;
    }

    public function addClaim(string $claim): void {
        if (!in_array($claim, $this->claims)) {
            $this->claims[] = $claim;
        }
    }

    public function removeClaim(string $claim): void {
        $key = array_search($claim, $this->claims);
        if ($key !== false) {
            unset($this->claims[$key]);
            $this->claims = array_values($this->claims);
        }
    }

    public function getMoney(): int {
        return $this->money;
    }

    public function setMoney(int $money): void {
        $this->money = max(0, $money);
    }

    public function addMoney(int $amount): void {
        $this->money += $amount;
    }

    public function removeMoney(int $amount): bool {
        if ($this->money >= $amount) {
            $this->money -= $amount;
            return true;
        }
        return false;
    }

    public function getAllies(): array {
        return $this->allies;
    }

    public function addAlly(string $faction): void {
        if (!in_array($faction, $this->allies)) {
            $this->allies[] = $faction;
        }
    }

    public function removeAlly(string $faction): void {
        $key = array_search($faction, $this->allies);
        if ($key !== false) {
            unset($this->allies[$key]);
            $this->allies = array_values($this->allies);
        }
    }

    public function isAlly(string $faction): bool {
        return in_array($faction, $this->allies);
    }

    public function getEnemies(): array {
        return $this->enemies;
    }

    public function addEnemy(string $faction): void {
        if (!in_array($faction, $this->enemies)) {
            $this->enemies[] = $faction;
        }
    }

    public function removeEnemy(string $faction): void {
        $key = array_search($faction, $this->enemies);
        if ($key !== false) {
            unset($this->enemies[$key]);
            $this->enemies = array_values($this->enemies);
        }
    }

    public function isEnemy(string $faction): bool {
        return in_array($faction, $this->enemies);
    }

    public function getInvites(): array {
        return $this->invites;
    }

    public function addInvite(string $player): void {
        if (!in_array($player, $this->invites)) {
            $this->invites[] = $player;
        }
    }

    public function removeInvite(string $player): void {
        $key = array_search($player, $this->invites);
        if ($key !== false) {
            unset($this->invites[$key]);
            $this->invites = array_values($this->invites);
        }
    }

    public function hasInvite(string $player): bool {
        return in_array($player, $this->invites);
    }

    public function getMemberCount(): int {
        return count($this->members);
    }

    public function getCreatedAt(): int {
        return $this->createdAt;
    }

    public function toArray(): array {
        return [
            "name" => $this->name,
            "leader" => $this->leader,
            "members" => $this->members,
            "officers" => $this->officers,
            "allies" => $this->allies,
            "enemies" => $this->enemies,
            "power" => $this->power,
            "maxPower" => $this->maxPower,
            "description" => $this->description,
            "open" => $this->open,
            "island" => $this->island,
            "claims" => $this->claims,
            "money" => $this->money,
            "invites" => $this->invites,
            "createdAt" => $this->createdAt
        ];
    }

    public static function fromArray(array $data): Faction {
        $faction = new Faction($data["name"], $data["leader"]);
        $faction->members = $data["members"] ?? [$data["leader"]];
        $faction->officers = $data["officers"] ?? [];
        $faction->allies = $data["allies"] ?? [];
        $faction->enemies = $data["enemies"] ?? [];
        $faction->power = $data["power"] ?? 0;
        $faction->maxPower = $data["maxPower"] ?? 100;
        $faction->description = $data["description"] ?? "";
        $faction->open = $data["open"] ?? false;
        $faction->island = $data["island"] ?? null;
        $faction->claims = $data["claims"] ?? [];
        $faction->money = $data["money"] ?? 0;
        $faction->invites = $data["invites"] ?? [];
        $faction->createdAt = $data["createdAt"] ?? time();
        return $faction;
    }
}
