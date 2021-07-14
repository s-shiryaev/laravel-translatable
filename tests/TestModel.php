<?php

namespace SShiryaev\LaravelTranslatable\Tests;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SShiryaev\LaravelTranslatable\Translatable;

class TestModel extends Model
{
    use HasFactory;
    use Translatable;

    protected $table = 'test_models';

    protected $translatable = ['name', 'code'];

    protected $fillable = [
        'id',
        'name_ru',
        'name_en',
        'name',
        'code_ru',
        'code_en',
        'code_de',
    ];

    public $timestamps = false;

    public function parent()
    {
        return $this->belongsTo(TestModel::class, 'parent_id', 'id');
    }
}
