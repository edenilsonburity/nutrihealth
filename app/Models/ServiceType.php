<?php
namespace App\Models;

class ServiceType
{
    public ?int $id;
    public string $code;
    public string $name;
    public ?string $description;
    public ?float $defaultPrice;
    public bool $active;

    public function __construct(
        ?int $id,
        string $code,
        string $name,
        ?string $description = null,
        ?float $defaultPrice = null,
        bool $active = true
    ) {
        $this->id           = $id;
        $this->code         = $code;
        $this->name         = $name;
        $this->description  = $description;
        $this->defaultPrice = $defaultPrice;
        $this->active       = $active;
    }

    public static function fromArray(array $d): self
    {
        return new self(
            $d['id'] ?? null,
            $d['code'] ?? '',
            $d['name'] ?? '',
            $d['description'] ?? null,
            isset($d['default_price']) && $d['default_price'] !== null ? (float)$d['default_price'] : null,
            isset($d['active']) ? (bool)$d['active'] : true
        );
    }
}
