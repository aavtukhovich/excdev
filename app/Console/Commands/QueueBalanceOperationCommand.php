<?php

namespace App\Console\Commands;

use App\Enums\OperationType;
use App\Services\BalanceOperationsService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use Illuminate\Support\Str;

class QueueBalanceOperationCommand extends Command
{
    protected $signature = 'balance:operate
        {login : Логин пользователя}
        {type : Начисление или списание}
        {amount : Сумма операции}
        {description : Описание операции}';

    protected $description = 'Поставить операцию по балансу пользователя в очередь';

    public function handle(BalanceOperationsService $service): int
    {
        $type = $this->resolveType($this->argument('type'));

        if ($type === null) {
            $this->error('Тип операции должен быть credit|debit или начисление|списание|кредит|дебит.');

            return self::FAILURE;
        }

        try {
            $operation = $service->queueByLogin(
                login: $this->argument('login'),
                type: $type,
                amount: $this->argument('amount'),
                description: $this->argument('description'),
            );
        } catch (ModelNotFoundException) {
            $this->error('Пользователь с указанным логином не найден.');

            return self::FAILURE;
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Операция #{$operation->id} поставлена в очередь.");
        $this->line("Тип: {$operation->type->label()}");
        $this->line("Сумма: {$operation->amount}");

        return self::SUCCESS;
    }

    private function resolveType(string $value): ?OperationType
    {
        return match (Str::lower(trim($value))) {
            'credit', 'accrual', 'deposit', 'начисление', 'кредит' => OperationType::CREDIT,
            'debit', 'debet', 'writeoff', 'withdraw', 'списание', 'дебит' => OperationType::DEBIT,
            default => null,
        };
    }
}
