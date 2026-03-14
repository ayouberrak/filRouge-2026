<?php

namespace App\Modules\Classroom\Infrastructure\Repositories;

use App\Modules\Classroom\Domain\Entities\ClassroomEntity;
use App\Modules\Classroom\Domain\ValueObjects\ClassroomName;
use App\Modules\Classroom\Domain\Repositories\ClassroomRepositoryInterface;
use App\Modules\Classroom\Infrastructure\Models\ClassroomModel;

class ClassroomRepository implements ClassroomRepositoryInterface
{
    public function findById(int $id) {
        $model = ClassroomModel::find($id);
        if (!$model) return null;
        return new ClassroomEntity(
            $model->id,
            new ClassroomName($model->name),
            $model->formateur_id
        ); 
    }


    public function findAll() {
        $model = ClassroomModel::all();
        $entities = [];
        foreach ($model as $item) {
            $entities[] = new ClassroomEntity(
                $item->id,
                new ClassroomName($item->name),
                $item->formateur_id
            );
        }
        return $entities;
    }



    public function create($data) {
         $model =ClassroomModel::create($data); 
         return new ClassroomEntity(
            $model->id,
            new ClassroomName($model->name),
            $model->formateur_id
        );
    }




    public function update(int $id, $data) {
        $model = ClassroomModel::query()->find($id);
        if ($model) {
            $model->fill($data);
            $model->save();
            return new ClassroomEntity(
                $model->id,
                new ClassroomName($model->name),
                $model->formateur_id 
            );
        }
        return null;
    }

    public function delete(int $id){
        $model = ClassroomModel::query()->find($id);
        if ($model) {
            $model->delete();
            return true;
        }
        return false;
    }   


    public function assignFormateur(int $classroomId, int $formateurId) {
        $model = ClassroomModel::query()->find($classroomId);
        if ($model) {
            $model->formateur_id = $formateurId;
            $model->save();
            return new ClassroomEntity(
                $model->id,
                new ClassroomName($model->name),
                $model->formateur_id 
            );
        }
        return null;
    }

}
