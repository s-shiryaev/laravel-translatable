<?php

namespace SShiryaev\LaravelTranslatable\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use SShiryaev\LaravelTranslatable\LaravelTranslatableServiceProvider;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelTranslatableServiceProvider::class,
        ];
    }

    public function setUpDatabase()
    {
        Schema::create('test_models', function (Blueprint $table) {
            $table->id();
            $table->string('name_ru')->nullable();
            $table->string('name_en')->nullable();
            $table->string('name')->nullable();
            $table->string('code_en')->nullable();
            $table->string('code_ru')->nullable();
            $table->string('code_de')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();

            $table->foreign('parent_id')->references('id')->on('test_models');
        });
    }
}
