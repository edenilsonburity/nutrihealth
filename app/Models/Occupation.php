<?php
namespace App\Models;

class Occupation
{
    public ?int $id;
    public string $code;
    public string $description;

    public function __construct(?int $id, string $code, string $description)
    {
        $this->id          = $id;
        $this->code        = $code;
        $this->description = $description;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['code'] ?? '',
            $data['description_occupation'] ?? ($data['description'] ?? '')
        );
    }
}
