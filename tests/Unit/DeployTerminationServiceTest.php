<?php

namespace Tests\Unit;

use App\Deploy;
use App\Game;
use App\Node;
use App\Server;
use App\Services\User\DeployTerminationService;
use App\Services\User\ServerTerminationService;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class DeployTerminationServiceTest extends TestCase
{
    use RefreshDatabase;
    use DatabaseMigrations;

    protected DeployTerminationService $deployTermination;
    protected Server $server;
    protected Carbon $firstTime;
    protected Carbon $secondTime;

    protected function setUp(): void
    {
        parent::setUp();

        $this->firstTime = Carbon::create(2020, 1, 1, 0, 0, 0);
        $this->secondTime = Carbon::create(2021, 1, 1, 0, 0, 0);

        $game = factory(Game::class)->create();
        $node = factory(Node::class)->create();
        $user = factory(User::class)->create();

        /** @var Server $server */
        $this->server = factory(Server::class)->create([
            'game_id' => $game->id,
            'node_id' => $node->id,
            'user_id' => $user->id,
        ]);

        factory(Deploy::class)->create([
            'server_id'       => $this->server->id,
            'billing_period'  => 'daily',
            'cost_per_period' => 0,
            'created_at'      => now()->subDay(),
        ]);
    }

    protected function prepareDeployTermination($mockServerTermination = false, $shouldBeCalled = 1)
    {
        if ($mockServerTermination) {
            $calls = [
                0 => 'never',
                1 => 'once',
            ];
            $call = $calls[ $shouldBeCalled ];

            $serverTermination = Mockery::mock(ServerTerminationService::class);
            $serverTermination->shouldReceive('handle')->$call();

            $this->instance(ServerTerminationService::class, $serverTermination);
        }

        $this->deployTermination = app(DeployTerminationService::class);
    }

    /**
     * Tests first termination call (that should set termination_requested_at)
     */
    public function test_server_termination_requested_at_is_set_on_termination()
    {
        $this->prepareDeployTermination();

        Carbon::setTestNow($this->firstTime);

        $this->deployTermination->handle($this->server, 'Testing');

        $deploy = $this->server->deploys()->first();

        $this->assertEquals('Testing', $deploy->termination_reason);
        $this->assertEquals($this->firstTime, $deploy->termination_requested_at);
    }

    /**
     * Tests second termination call (that should NOT change termination_requested_at)
     */
    public function test_multiple_server_termination_requests_do_not_reset_termination_requested_at()
    {
        $this->prepareDeployTermination();

        Carbon::setTestNow($this->firstTime);
        $this->deployTermination->handle($this->server, 'Testing');

        Carbon::setTestNow($this->secondTime);
        $this->deployTermination->handle($this->server, 'Testing');

        $deploy = $this->server->deploys()->first();

        $this->assertEquals($this->firstTime, $deploy->termination_requested_at);
    }

    /**
     * No asserts are defined here since the Mock is already created and registered on setUp
     */
    public function test_forced_deploy_termination_will_call_server_termination()
    {
        $this->prepareDeployTermination(true);

        $this->deployTermination->handle($this->server, 'Testing', true);
    }

    /**
     * This is a somewhat bad test, since nothing should happen.
     *
     * If everything is correct, ServerTerminationService will not be called a second time.
     * This is the reason the flag 'forced' is true
     */
    public function test_nothing_happens_when_termination_is_requested_without_live_deploys()
    {
        $this->server->deploys()->delete();
        $this->prepareDeployTermination(true, 0);
        $this->deployTermination->handle($this->server, 'Testing', true);
    }
}
