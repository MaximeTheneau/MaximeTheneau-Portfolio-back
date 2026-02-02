<?php

namespace App\DTO;

class BookingRequest
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $phone,
        public readonly \DateTime $dateTime,
        public readonly ?string $message = null,
    ) {}

    /**
     * Crée un BookingRequest depuis un tableau de données
     *
     * @param array $data Données du formulaire
     * @return self
     * @throws \Exception Si les données sont invalides
     */
    public static function fromArray(array $data): self
    {
        if (empty($data['name'])) {
            throw new \Exception('Le nom est requis');
        }

        if (empty($data['email'])) {
            throw new \Exception('L\'email est requis');
        }

        if (empty($data['phone'])) {
            throw new \Exception('Le téléphone est requis');
        }

        if (empty($data['date']) || empty($data['time'])) {
            throw new \Exception('La date et l\'heure sont requises');
        }

        $dateTimeStr = $data['date'] . ' ' . $data['time'];
        $dateTime = \DateTime::createFromFormat('Y-m-d H:i', $dateTimeStr);

        if (!$dateTime) {
            throw new \Exception('Format de date/heure invalide');
        }

        return new self(
            name: trim($data['name']),
            email: trim($data['email']),
            phone: trim($data['phone']),
            dateTime: $dateTime,
            message: !empty($data['message']) ? trim($data['message']) : null
        );
    }

    /**
     * Valide les données de la réservation
     *
     * @return array Liste des erreurs (vide si valide)
     */
    public function validate(): array
    {
        $errors = [];

        if (strlen($this->name) < 3 || strlen($this->name) > 70) {
            $errors['name'] = 'Le nom doit contenir entre 3 et 70 caractères';
        }

        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'L\'adresse email est invalide';
        }

        if (strlen($this->phone) < 10) {
            $errors['phone'] = 'Le numéro de téléphone est invalide';
        }

        $now = new \DateTime();
        if ($this->dateTime <= $now) {
            $errors['dateTime'] = 'La date doit être dans le futur';
        }

        if ($this->message !== null && strlen($this->message) > 500) {
            $errors['message'] = 'Le message ne peut pas dépasser 500 caractères';
        }

        return $errors;
    }

    /**
     * Convertit en tableau pour Google Calendar
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'message' => $this->message,
        ];
    }
}
