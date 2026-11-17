<?php

declare(strict_types=1);

namespace Mammesat\EthiopicCalendar\Casts;

use DateTimeInterface;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Mammesat\EthiopicCalendar\EthiopicDate;

/**
 * Stores a Gregorian "Y-m-d" column and exposes it as an EthiopicDate.
 *
 *   protected function casts(): array
 *   {
 *       return ['start_date_ethiopic' => EthiopicDateCast::class.':start_date'];
 *   }
 *
 * Without an argument the attribute reads and writes its own column. With a
 * column name it reads from that column (a virtual, read-only companion to a
 * normal Carbon date attribute).
 *
 * Setting accepts EthiopicDate, DateTimeInterface, or a Gregorian "Y-m-d" string.
 *
 * @implements CastsAttributes<EthiopicDate|null, EthiopicDate|DateTimeInterface|string|null>
 */
final class EthiopicDateCast implements CastsAttributes
{
    public function __construct(private readonly ?string $column = null) {}

    public function get(Model $model, string $key, mixed $value, array $attributes): ?EthiopicDate
    {
        $raw = $this->column === null ? $value : ($attributes[$this->column] ?? null);

        if ($raw === null || $raw === '') {
            return null;
        }

        if ($raw instanceof DateTimeInterface) {
            return EthiopicDate::fromGregorian($raw);
        }

        // Accept "Y-m-d" and "Y-m-d H:i:s" as stored by different drivers.
        return EthiopicDate::fromGregorian(substr((string) $raw, 0, 10));
    }

    /**
     * @return array<string, string|null>
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($this->column !== null) {
            throw new InvalidArgumentException("{$key} is read-only; set {$this->column} instead.");
        }

        $stored = match (true) {
            $value === null || $value === '' => null,
            $value instanceof EthiopicDate => $value->toGregorianString(),
            default => EthiopicDate::fromGregorian($value)->toGregorianString(),
        };

        return [$key => $stored];
    }
}
