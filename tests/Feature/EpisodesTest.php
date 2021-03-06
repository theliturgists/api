<?php

namespace Tests\Feature;


use App\Contributor;
use App\Episode;
use App\Podcast;
use App\Season;

class EpisodesTest extends ApiTestCase
{
    /**
     * @var string
     */
    protected $resourceType = 'episodes';

    /**
     * Test the search route.
     */
    public function testSearch()
    {
        // ensure there is at least one episode in the database
        $this->model();

        $this->doSearch()
            ->assertSearchResponse();
    }

    /**
     * Test searching for specific ids
     */
    public function testSearchById()
    {
        $models = factory(Episode::class, 2)->create();
        // This episode should not be returned in the results
        $this->model();

        $this->doSearchById($models)
            ->assertSearchByIdResponse($models);
    }

    /**
     * Test the create resource route.
     */
    public function testCreate()
    {
        $model = $this->model(false);

        $data = [
            'type' => 'episodes',
            'attributes' => [
                'title' => $model->title,
                'description' => $model->description,
                'image-url' => $model->image_url,
                'media-url' => $model->media_url,
                'player-url' => $model->player_url,
                'permalink-url' => $model->permalink_url,
                'published-at' => $model->published_at->format('c'),
                'status' => $model->status,
                'number' => $model->number
            ],
        ];

        $id = $this
            ->doCreate($data)
            ->assertCreateResponse($data);

        $this->assertModelCreated($model, $id, [
            'title',
            'description',
            'image_url',
            'media_url',
            'player_url',
            'permalink_url',
            'published_at',
            'status',
            'number',
        ]);
    }

    /**
     * Test the read resource route.
     */
    public function testRead()
    {
        $model = $this->model();

        $contributorsData = [];
        foreach ($model->contributors as $contributor) {
            $contributorsData[] = [
                'type' => 'contributors',
                'id' => $contributor->getKey()
            ];
        }

        $data = [
            'type' => 'episodes',
            'id' => $model->getKey(),
            'attributes' => [
                'created-at' => $model->created_at->format('c'),
                'updated-at' => $model->updated_at->format('c'),
                'title' => $model->title,
                'description' => $model->description,
                'image-url' => $model->image_url,
                'media-url' => $model->media_url,
                'player-url' => $model->player_url,
                'permalink-url' => $model->permalink_url,
                'published-at' => $model->published_at->format('c'),
                'status' => $model->status,
                'number' => $model->number
            ],
            'relationships' => [
                'contributors' => [
                    'data' => $contributorsData,
                    'meta' => [
                        'total' => count($contributorsData)
                    ]
                ]
            ]
        ];

        $this->doRead($model)
            ->assertReadResponse($data);
    }

    /**
     * Test the update resource route.
     */
    public function testUpdate()
    {
        $model = $this->model();

        $data = [
            'type' => 'episodes',
            'id' => (string) $model->getKey(),
            'attributes' => [
                'title' => 'Foo',
            ],
        ];

        $responseDate = $this->doUpdate($data)->assertUpdateResponse($data)->getDate();
        $this->assertModelPatched($model, [
            'title' => 'Foo',
            'updated_at' => $responseDate
        ], ['created_at', 'description', 'image_url']);
    }

    /**
     * Test the delete resource route.
     */
    public function testDelete()
    {
        $model = $this->model();

        $this->doDelete($model)->assertDeleteResponse();
        $this->assertModelDeleted($model);
    }

    /**
     * Test the read season route.
     */
    public function testReadSeason()
    {
        $model = $this->model();

        $season = $model->season;

        $contributorsData = [];
        foreach ($season->contributors as $contributor) {
            $contributorsData[] = [
                'type' => 'contributors',
                'id' => $contributor->getKey()
            ];
        }

        $data = [
            'type' => 'seasons',
            'id' => $season->getKey(),
            'attributes' => [
                'created-at' => $season->created_at->format('c'),
                'updated-at' => $season->updated_at->format('c'),
                'title' => $season->title,
                'description' => $season->description,
                'image-url' => $season->image_url,
                'number' => $season->number
            ],
            'relationships' => [
                'podcast' => [
                    'data' => [
                        'type' => 'podcasts',
                        'id' => $season->podcast->getKey(),
                    ],
                ],
                'contributors' => [
                    'data' => $contributorsData,
                    'meta' => [
                        'total' => count($contributorsData)
                    ]
                ]
            ]
        ];

        $this->doReadRelatedResources($model, 'season')
            ->assertRelatedResourceResponse($data);
    }

    public function testUpdateSeason()
    {
        $model = $this->model();

        $season = factory(Season::class)->create();

        $this->doUpdateRelateResource($model, 'season', 'seasons', $season->getKey());
        $this->assertModelPatched($model, ['season'=>$season]);
    }

    /**
     * Test the read contributors route.
     */
    public function testReadContributors()
    {
        $model = $this->model();

        $this->doReadRelatedResources($model, 'contributors')
            ->assertRelatedResourcesResponse(['contributors']);
    }

    /**
     * Test the read contributors route.
     */
    public function testAddContributors()
    {
        $model = $this->model();

        $relatedModels = $model->contributors->all();
        $relatedModelsToAdd = factory(Contributor::class, 3)->create()->all();

        $relatedIds = [];
        foreach ($relatedModelsToAdd as $relatedModel) {
            $relatedIds[] = $relatedModel->getKey();
        }

        $response = $this->doAddRelatedResources($model, 'contributors', 'contributors', $relatedIds);

        $relationships = [];
        foreach (array_merge($relatedModels, $relatedModelsToAdd) as $relatedModel) {
            $relationships[] = ['type' => 'contributors', 'id' => (string) $relatedModel->getKey()];
        }
        $response->assertRelatedResourcesResponse(['contributors'])->assertExactJson([
            'data' => $relationships
        ]);
    }

    /**
     * Test the read contributors route.
     */
    public function testRemoveContributors()
    {
        $model = $this->model();

        $relatedModels = $model->contributors->all();
        $relatedModelToRemove = array_pop($relatedModels);
        $response = $this->doRemoveRelatedResources($model, 'contributors', 'contributors', [$relatedModelToRemove->getKey()]);

        $relationships = [];
        foreach ($relatedModels as $relatedModel) {
            $relationships[] = ['type' => 'contributors', 'id' => (string) $relatedModel->getKey()];
        }
        $response->assertRelatedResourcesResponse(['contributors'])->assertExactJson([
            'data' => $relationships
        ]);
    }

    /**
     * Test the read contributors route.
     */
    public function testReplaceContributors()
    {
        $model = $this->model();

        $relatedModelsToReplaceWith = (array) factory(Contributor::class, 3)->create()->all();

        $relatedIds = [];
        foreach ($relatedModelsToReplaceWith as $relatedModel) {
            $relatedIds[] = $relatedModel->getKey();
        }

        $response = $this->doReplaceRelatedResources($model, 'contributors', 'contributors', $relatedIds);

        $relationships = [];
        foreach ($relatedModelsToReplaceWith as $relatedModel) {
            $relationships[] = ['type' => 'contributors', 'id' => (string) $relatedModel->getKey()];
        }
        $response->assertRelatedResourcesResponse(['contributors'])->assertExactJson([
            'data' => $relationships
        ]);
    }

    /**
     * This is just a helper so that we get a type hinted model back.
     *
     * @param bool $create
     * @return Episode
     */
    protected function model($create = true)
    {
        $builder = factory(Episode::class);

        if($create) {
            $model = $builder->create();
            $season = factory(Season::class)->create();
            $podcast = factory(Podcast::class)->create();
            $season->podcast()->associate($podcast)->save();
            $model->season()->associate($season)->save();
            $model->contributors()->saveMany(factory(Contributor::class, 2)->create());
            return $model;
        } else {
            return $builder->make();
        }
    }
}