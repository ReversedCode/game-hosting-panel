<?php

namespace Tests\Unit\Service;

use App\Location;
use App\Node;
use App\Services\NodeService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class NodeServiceTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;

    protected NodeService $service;
    protected Location $location;
    protected array $create;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->service = app(NodeService::class);
        $this->location = factory(Location::class)->create(['id' => 1]);
        $this->create = [
            'name'        => 'Node Name',
            'description' => 'Node Description',
            'location_id' => $this->location->id,
        ];
    }

    public function test_first_or_create_finds_from_database(): void
    {
        $node = factory(Node::class)->create($this->create + ['id' => 1]);

        $result = $this->service->firstOrCreate($node->id, $this->create);

        $this->assertEquals($node->id, $result->id);
    }

    public function test_first_or_create_creates_if_missing(): void
    {
        $node = factory(Node::class)->create($this->create + ['id' => 1]);

        $this->service->firstOrCreate($node->id, $this->create);

        $this->assertDatabaseHas('nodes', $this->create);
    }
}
