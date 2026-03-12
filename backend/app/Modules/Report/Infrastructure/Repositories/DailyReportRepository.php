<?php

namespace App\Modules\Report\Infrastructure\Repositories;

use App\Modules\Report\Domain\Repositories\DailyReportRepositoryInterface;
use App\Modules\Report\Infrastructure\Models\DailyReportModel;

class DailyReportRepository implements DailyReportRepositoryInterface
{
    public function findById(int $id) { return DailyReportModel::find($id); }
    public function findAll() { return DailyReportModel::all(); }
    public function create(array $data) { return DailyReportModel::create($data); }
    public function update(int $id, array $data) {
        $model = DailyReportModel::find($id);
        if ($model) {
            $model->update($data);
            return $model;
        }
        return null;
    }
    public function delete(int $id): bool {
        $model = DailyReportModel::find($id);
        return $model ? $model->delete() : false;
    }
}
