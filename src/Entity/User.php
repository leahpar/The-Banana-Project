<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column]
    public string $endpoint;

    #[ORM\Column]
    public string $publicKey;

    #[ORM\Column]
    public string $authToken;

    public function __construct(array $subscription)
    {
        $this->endpoint = $subscription['endpoint'];
        $this->publicKey = $subscription['keys']['p256dh'];
        $this->authToken = $subscription['keys']['auth'];
    }

    public function getSuscription(): array
    {
        return [
            'endpoint' => $this->endpoint,
            'keys' => [
                'p256dh' => $this->publicKey,
                'auth' => $this->authToken,
            ]
        ];
    }


}
