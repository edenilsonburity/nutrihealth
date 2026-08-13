<?php
namespace App\Models;

class Patient
{
    public ?int $id;
    public string $fullName;
    public string $cpf;
    public ?string $birthDate;
    public ?string $phone;
    public ?string $cellphone;
    public ?string $email;
    public ?string $address;
    public ?string $emergencyContact;
    public ?string $guardianName;
    public string $status;
    public ?string $notes;
    public ?string $rg;
    public ?string $nationality;
    public ?string $maritalStatus;
    public ?string $cep;
    public ?int $occupationId;
    public ?string $occupationDescription;

    public function __construct(
        ?int $id,
        string $fullName,
        string $cpf,
        ?string $birthDate = null,
        ?string $phone = null,
        ?string $cellphone = null,
        ?string $email = null,
        ?string $address = null,
        ?string $emergencyContact = null,
        ?string $guardianName = null,
        string $status = 'A',
        ?string $notes = null,
        ?string $rg = null,
        ?string $nationality = null,
        ?string $maritalStatus = null,
        ?string $cep = null,
        ?int $occupationId = null,
        ?string $occupationDescription = null
    ) {
        $this->id               = $id;
        $this->fullName         = $fullName;
        $this->cpf              = $cpf;
        $this->birthDate        = $birthDate;
        $this->phone            = $phone;
        $this->cellphone        = $cellphone;
        $this->email            = $email;
        $this->address          = $address;
        $this->emergencyContact = $emergencyContact;
        $this->guardianName     = $guardianName;
        $this->status           = $status;
        $this->notes            = $notes;
        $this->rg               = $rg;
        $this->nationality      = $nationality;
        $this->maritalStatus    = $maritalStatus;
        $this->cep              = $cep;
        $this->occupationId     = $occupationId;
        $this->occupationDescription = $occupationDescription;
    }

    public static function fromArray(array $d): self
    {
        return new self(
            $d['id']                ?? null,
            $d['name_patient']      ?? ($d['fullName']         ?? ''),
            $d['cpf']               ?? '',
            $d['birth_date']        ?? ($d['birthDate']        ?? null),
            $d['phone']             ?? null,
            $d['cellphone']         ?? null,
            $d['email']             ?? null,
            $d['address']           ?? null,
            $d['emergency_contact'] ?? ($d['emergencyContact'] ?? null),
            $d['guardian_name']     ?? ($d['guardianName']     ?? null),
            $d['status']            ?? 'A',
            $d['notes']             ?? null,
            $d['rg']                ?? null,
            $d['nationality']       ?? null,
            $d['marital_status']    ?? ($d['maritalStatus']    ?? null),
            $d['cep']               ?? null,
            $d['idOccupation']      ?? ($d['occupationId'] ?? null),
            $d['occupation_description'] ?? ($d['occupationDescription'] ?? null)
        );
    }
}
