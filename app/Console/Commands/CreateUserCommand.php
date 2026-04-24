<?php

namespace App\Console\Commands;

use App\Services\UserService;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

class CreateUserCommand extends Command
{
    protected $signature = 'user:create
        {login : Логин пользователя}
        {password : Пароль пользователя}
        {--name= : Имя пользователя}
        {--email= : Email пользователя}';

    protected $description = 'Создание нового пользователя';

    public function handle(UserService $service): int
    {
        try {
            $user = $service->create(
                name: $this->option('name') ?: $this->argument('login'),
                login: $this->argument('login'),
                password: $this->argument('password'),
                email: $this->option('email'),
            );
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $messages) {
                foreach ($messages as $message) {
                    $this->error($message);
                }
            }

            return self::FAILURE;
        }

        $this->info("Пользователь {$user->login} создан.");
        $this->line("ID: {$user->id}");
        $this->line('Текущий баланс: 0.00');

        return self::SUCCESS;
    }
}
