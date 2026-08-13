<?php
namespace App\Models;

class AppointmentType
{
    public ?int $id;
    public string $code;
    public string $name;
    public bool $active;

    public function __construct(
        ?int $id,
        string $code,
        string $name,
        bool $active = true
    ) {
        $this->id     = $id;
        $this->code   = $code;
        $this->name   = $name;
        $this->active = $active;
    }

    public static function fromArray(array $d): self
    {
        return new self(
            $d['id'] ?? null,
            $d['code'] ?? '',
            $d['name'] ?? '',
            isset($d['active']) ? (bool)$d['active'] : true
        );
    }
}
