<?php

namespace App\Modules\Onboarding\Infrastructure\Persistence\Repositories;

use App\Modules\Onboarding\Domain\Aggregates\OnboardingTemplate\OnboardingTemplate;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTemplate\OnboardingTemplateId;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTemplateRepositoryInterface;
use App\Modules\Onboarding\Domain\ValueObjects\TemplateRules;
use App\Modules\Onboarding\Infrastructure\Persistence\Eloquent\OnboardingTemplateModel;

class EloquentOnboardingTemplateRepository implements OnboardingTemplateRepositoryInterface
{
    public function findById(OnboardingTemplateId $id): ?OnboardingTemplate
    {
        $model = OnboardingTemplateModel::find($id->value);

        return $model ? $this->toDomain($model) : null;
    }

    public function findByCode(string $code): ?OnboardingTemplate
    {
        $model = OnboardingTemplateModel::where('code', $code)->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function findMatching(?string $departmentId, ?string $positionId, ?string $locationId, ?string $employmentType): array
    {
        $models = OnboardingTemplateModel::where('active', true)->get();

        return array_values(
            array_filter(
                $models->map(fn ($m) => $this->toDomain($m))->toArray(),
                fn (OnboardingTemplate $t) => $t->matches($departmentId, $positionId, $locationId, $employmentType)
            )
        );
    }

    public function all(): array
    {
        return OnboardingTemplateModel::all()->map(fn ($m) => $this->toDomain($m))->toArray();
    }

    public function save(OnboardingTemplate $template): void
    {
        OnboardingTemplateModel::updateOrCreate(
            ['id' => $template->getId()->value],
            [
                'code' => $template->getCode(),
                'name' => $template->getName(),
                'rules' => $template->getRules()->toArray(),
                'active' => $template->isActive(),
            ]
        );
    }

    public function delete(OnboardingTemplateId $id): void
    {
        OnboardingTemplateModel::destroy($id->value);
    }

    private function toDomain(OnboardingTemplateModel $model): OnboardingTemplate
    {
        return OnboardingTemplate::reconstitute(
            OnboardingTemplateId::fromString($model->id),
            $model->code,
            $model->name,
            TemplateRules::fromArray($model->rules),
            $model->active,
        );
    }
}
