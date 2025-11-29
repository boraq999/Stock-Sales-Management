<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class CreateInvoiceDTO
{
    public function __construct(
        public int $marketerId,
        public ?int $storeId,
        public ?string $clientName,
        public ?string $clientPhone,
        public array $items,
    ) {}

    public static function fromRequest(array $data, int $marketerId): self
    {
        return new self(
            marketerId: $marketerId,
            storeId: isset($data['store_id']) ? (int)$data['store_id'] : null,
            clientName: $data['client_name'] ?? null,
            clientPhone: $data['client_phone'] ?? null,
            items: $data['items']
        );
    }
}
