<?php

namespace App\Http\Requests\Savings;

use App\Models\IncomeDistributionTodo;
use App\Models\IncomePeriod;
use Illuminate\Foundation\Http\FormRequest;

class CompleteIncomeDistributionTodoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        $period = $this->route('incomePeriod');
        $todo = $this->route('todo');

        if (! $period instanceof IncomePeriod || ! $todo instanceof IncomeDistributionTodo) {
            return false;
        }

        return $todo->income_period_id === $period->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
