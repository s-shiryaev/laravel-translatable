<?php

namespace SShiryaev\LaravelTranslatable\Tests;

use Illuminate\Support\Arr;
use SShiryaev\LaravelTranslatable\TranslatableCollection;

class TranslatableTest extends TestCase
{
    protected TestModel $testModel;

    public function setUp(): void
    {
        parent::setUp();

        $this->testModel = new TestModel();
    }

    /**
     * @test
     *
     * @dataProvider getSingleModelTestToArrayData
     */
    public function singleModelTestToArray(
        array $fillData,
        string $locale,
        string $fallbackLocale,
        array $expectData
    ) {
        $this->app['config']->set('app.fallback_locale', $fallbackLocale);
        $this->app->setLocale($locale);

        $this->testModel->fill($fillData);
        $this->testModel->save();

        foreach ($expectData as $key => $value) {
            $this->assertSame($value, $this->testModel->$key);
        }

        foreach ($fillData as $key => $value) {
            $this->assertSame($value, $this->testModel->$key);
        }

        $this->assertEquals($expectData, Arr::only($this->testModel->toArray(), array_keys($expectData)));
        $this->assertEquals($fillData, Arr::only($this->testModel->toArray(false), array_keys($fillData)));
    }

    /**
     * @test
     *
     * @dataProvider getCollectionTestToArrayData
     */
    public function collectionTestToArray(
        array $dataArray,
        string $locale,
        string $fallbackLocale,
        array $expectArray
    ) {
        $this->app['config']->set('app.fallback_locale', $fallbackLocale);
        $this->app->setLocale($locale);

        foreach ($dataArray as $key => $modelData) {
            $model = new TestModel($modelData);
            $model->save();
            $expectArray[$key]['id'] = $dataArray[$key]['id'] = $model->id;
            $expectArray[$key]['parent_id'] = $dataArray[$key]['parent_id'] = null;
        }

        /** @var $collection TranslatableCollection */
        $collection = TestModel::all();

        $this->assertEquals($expectArray, $collection->toArray());
        $this->assertEquals($dataArray, $collection->toArray(false));
    }

    /**
     * @test
     *
     * @dataProvider getCollectionTestToArrayData
     */
    public function singleModelRelationTestToArray(
        array $dataArray,
        string $locale,
        string $fallbackLocale,
        array $expectArray
    ) {
        $this->app['config']->set('app.fallback_locale', $fallbackLocale);
        $this->app->setLocale($locale);

        $expect = [];
        $untranslated = [];

        foreach ($dataArray as $key => $modelData) {
            $model = new TestModel($modelData);
            if (isset($dataArray[$key - 1])) {
                $model->parent()->associate($dataArray[$key - 1]['id']);
                $expectArray[$key]['parent_id'] = $dataArray[$key]['parent_id'] = $dataArray[$key - 1]['id'];
            } else {
                $expectArray[$key]['parent_id'] = $dataArray[$key]['parent_id'] = null;
            }
            $model->save();
            $expectArray[$key]['id'] = $dataArray[$key]['id'] = $model->id;
            $expect[$model->id] = $expectArray[$key];
            $expect[$model->id]['parent'] =
                $expect[$model->id]['parent_id']
                    ? $expect[$expect[$model->id]['parent_id']]
                    : null;
            $untranslated[$model->id] = $dataArray[$key];
            $untranslated[$model->id]['parent'] =
                $untranslated[$model->id]['parent_id']
                    ? $untranslated[$untranslated[$model->id]['parent_id']]
                    : null;
        }

        $models = TestModel::with(['parent'])->get();
        foreach ($models as $model) {
            unset($expect[$model->id]['parent']['parent']);
            unset($untranslated[$model->id]['parent']['parent']);

            $this->assertEquals($expect[$model['id']], $model->toArray());
            $this->assertEquals($untranslated[$model['id']], $model->toArray(false));
        }
    }

    /*
     * Data providers
     */

    public function getSingleModelTestToArrayData(): array
    {
        return [
            [
                [
                    'name_ru' => 'Тестовое имя',
                    'name_en' => 'Test name',
                    'code_ru' => 'Код',
                    'code_en' => 'Code',
                    'code_de' => 'Produktcode',
                ],
                'ru',
                'ru',
                [
                    'name' => 'Тестовое имя',
                    'name_en' => 'Test name',
                    'code' => 'Код',
                    'code_en' => 'Code',
                    'code_de' => 'Produktcode',
                ],
            ],
            [
                [
                    'name_ru' => 'Тестовое имя',
                    'name_en' => 'Test name',
                    'code_ru' => 'Код',
                    'code_en' => 'Code',
                    'code_de' => 'Produktcode',
                ],
                'en',
                'ru',
                [
                    'name_ru' => 'Тестовое имя',
                    'name' => 'Test name',
                    'code_ru' => 'Код',
                    'code' => 'Code',
                    'code_de' => 'Produktcode',
                ],
            ],
            [
                [
                    'name_ru' => 'Тестовое имя',
                    'name_en' => 'Test name',
                    'code_ru' => 'Код',
                    'code_en' => 'Code',
                    'code_de' => 'Produktcode',
                ],
                'de',
                'ru',
                [
                    'name' => 'Тестовое имя',
                    'name_en' => 'Test name',
                    'code_ru' => 'Код',
                    'code_en' => 'Code',
                    'code' => 'Produktcode',
                ],
            ],
            [
                [
                    'name_ru' => 'Тестовое имя',
                    'name_en' => 'Test name',
                    'code_ru' => 'Код',
                    'code_en' => 'Code',
                    'code_de' => 'Produktcode',
                ],
                'de',
                'de',
                [
                    'name_ru' => 'Тестовое имя',
                    'name_en' => 'Test name',
                    'code_ru' => 'Код',
                    'code_en' => 'Code',
                    'code' => 'Produktcode',
                ],
            ],
            [
                [
                    'name_ru' => 'Тестовое имя',
                    'name' => 'Fallback name',
                    'name_en' => 'Test name',
                    'code_ru' => 'Код',
                    'code_en' => 'Code',
                    'code_de' => 'Produktcode',
                ],
                'nl',
                'de',
                [
                    'name_ru' => 'Тестовое имя',
                    'name_en' => 'Test name',
                    'name' => 'Fallback name',
                    'code_ru' => 'Код',
                    'code_en' => 'Code',
                    'code' => 'Produktcode',
                ],
            ],
        ];
    }

    public function getCollectionTestToArrayData(): array
    {
        return [
            [
                [
                    [
                        'name_ru' => 'Тестовое имя1',
                        'name_en' => 'Test name1',
                        'name' => null,
                        'code_ru' => 'Код1',
                        'code_en' => 'Code1',
                        'code_de' => 'Produktcode1',
                    ],
                    [
                        'name_ru' => 'Тестовое имя2',
                        'name_en' => 'Test name2',
                        'name' => null,
                        'code_ru' => 'Код2',
                        'code_en' => 'Code2',
                        'code_de' => 'Produktcode2',
                    ],
                    [
                        'name_ru' => 'Тестовое имя3',
                        'name_en' => 'Test name3',
                        'name' => null,
                        'code_ru' => 'Код3',
                        'code_en' => 'Code3',
                        'code_de' => 'Produktcode3',
                    ],
                ],
                'ru',
                'ru',
                [
                    [
                        'name' => 'Тестовое имя1',
                        'name_en' => 'Test name1',
                        'code' => 'Код1',
                        'code_en' => 'Code1',
                        'code_de' => 'Produktcode1',
                    ],
                    [
                        'name' => 'Тестовое имя2',
                        'name_en' => 'Test name2',
                        'code' => 'Код2',
                        'code_en' => 'Code2',
                        'code_de' => 'Produktcode2',
                    ],
                    [
                        'name' => 'Тестовое имя3',
                        'name_en' => 'Test name3',
                        'code' => 'Код3',
                        'code_en' => 'Code3',
                        'code_de' => 'Produktcode3',
                    ],
                ],
            ],
            [
                [
                    [
                        'name_ru' => 'Тестовое имя1',
                        'name_en' => 'Test name1',
                        'name' => null,
                        'code_ru' => 'Код1',
                        'code_en' => 'Code1',
                        'code_de' => 'Produktcode1',
                    ],
                    [
                        'name_ru' => 'Тестовое имя2',
                        'name_en' => 'Test name2',
                        'name' => null,
                        'code_ru' => 'Код2',
                        'code_en' => 'Code2',
                        'code_de' => 'Produktcode2',
                    ],
                    [
                        'name_ru' => 'Тестовое имя3',
                        'name_en' => 'Test name3',
                        'name' => null,
                        'code_ru' => 'Код3',
                        'code_en' => 'Code3',
                        'code_de' => 'Produktcode3',
                    ],
                ],
                'de',
                'ru',
                [
                    [
                        'name' => 'Тестовое имя1',
                        'name_en' => 'Test name1',
                        'code_ru' => 'Код1',
                        'code_en' => 'Code1',
                        'code' => 'Produktcode1',
                    ],
                    [
                        'name' => 'Тестовое имя2',
                        'name_en' => 'Test name2',
                        'code_ru' => 'Код2',
                        'code_en' => 'Code2',
                        'code' => 'Produktcode2',
                    ],
                    [
                        'name' => 'Тестовое имя3',
                        'name_en' => 'Test name3',
                        'code_ru' => 'Код3',
                        'code_en' => 'Code3',
                        'code' => 'Produktcode3',
                    ],
                ],
            ],
        ];
    }
}
