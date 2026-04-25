<?php

namespace App\Rivers\Forks\Conditions;

use App\Rivers\Enums\MultiConditionLogic;
use LsvEu\Rivers\Actions\EvaluateRiverElement;
use LsvEu\Rivers\Cartography\Condition;
use LsvEu\Rivers\Cartography\RiverElementCollection;
use LsvEu\Rivers\Models\RiverRun;

class MultiCondition extends Condition
{
    /**
     * @var RiverElementCollection<string, Condition>
     */
    public RiverElementCollection $conditions;

    public MultiConditionLogic $mode;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->conditions = RiverElementCollection::make($attributes['conditions'] ?? []);
        $this->mode = MultiConditionLogic::from($attributes['mode'] ?? MultiConditionLogic::AND);
    }

    public function toArray(): array
    {
        return parent::toArray() + [
            'conditions' => $this->conditions->toArray(),
            'mode' => $this->mode->value,
        ];
    }

    public function evaluate(?RiverRun $run = null): bool
    {
        if (! $run) {
            return false;
        }

        // Create a reusable evaluator so we aren't rebuilding dependency inject for each check
        $evaluator = new EvaluateRiverElement($run);

        return match ($this->mode) {
            MultiConditionLogic::AND => $this->conditions->doesntContain(fn (Condition $condition) => ! $evaluator->handle($condition)),
            MultiConditionLogic::OR => $this->conditions->contains(fn (Condition $condition) => $evaluator->handle($condition)),
            default => false,
        };
    }
}
