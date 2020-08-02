<?php

namespace Tests\Unit\Service\User;

use App\Deploy;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\InvalidBillingPeriodException;
use App\Exceptions\InvalidPeriodCostException;
use App\Exceptions\TooManyServersException;
use App\Game;
use App\Node;
use App\Server;
use App\Services\User\DeployCostService;
use App\Services\User\DeployCreationService;
use App\Transaction;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DeployCreationServiceTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;

    protected int $costPerPeriod = 200;

    public function test_deploy_model_is_created(): void
    {
        $this->expectsCostCalculation();

        $game = factory(Game::class)->create();
        $node = factory(Node::class)->create();
        $user = factory(User::class)->create([
            'server_limit' => 1,
        ]);
        factory(Transaction::class)->create([
            'value'   => 200,
            'user_id' => $user->id,
        ]);

        $server = factory(Server::class)->create([
            'id'       => 1,
            'panel_id' => 2,
            'game_id'  => $game->id,
            'node_id'  => $node->id,
            'user_id'  => $user->id,
        ]);

        $deploy = app(DeployCreationService::class)->handle($server, 'daily', [
            'cpu'       => 1200,
            'memory'    => 512,
            'disk'      => 2500,
            'databases' => 1,
        ]);

        $this->assertInstanceOf(Deploy::class, $deploy);
        $this->assertEquals($this->costPerPeriod, $deploy->cost_per_period);
    }

    protected function expectsCostCalculation(): void
    {
        // 'twice' since deploy has a creating() event listener that also uses getCostPerPeriod
        $this->partialMock(DeployCostService::class)
             ->shouldReceive('getCostPerPeriod')
             ->andReturn($this->costPerPeriod)
             ->twice();
    }

    public function test_invalid_billing_period_will_raise_exception(): void
    {
        $node = factory(Node::class)->create();
        $user = factory(User::class)->create([
            'server_limit' => 1,
        ]);

        $this->expectException(InvalidBillingPeriodException::class);

        app(DeployCreationService::class)->preChecks($user, $node, 'potato', []);
    }

    public function test_negative_deploy_cost_will_raise_exception(): void
    {
        $node = factory(Node::class)->create();
        $user = factory(User::class)->create([
            'server_limit' => 1,
        ]);

        $this->partialMock(DeployCostService::class)
             ->shouldReceive('getCostPerPeriod')
             ->andReturn(-200)
             ->once();

        $this->expectException(InvalidPeriodCostException::class);

        app(DeployCreationService::class)->preChecks($user, $node, 'daily', []);
    }

    public function test_user_with_insufficient_balance_will_raise_exception(): void
    {
        $node = factory(Node::class)->create();
        $user = factory(User::class)->create([
            'server_limit' => 1,
        ]);
        factory(Transaction::class)->create([
            'value'   => 50,
            'user_id' => $user->id,
        ]);

        $this->partialMock(DeployCostService::class)
             ->shouldReceive('getCostPerPeriod')
             ->andReturn(200)
             ->once();

        $this->expectException(InsufficientBalanceException::class);

        app(DeployCreationService::class)->preChecks($user, $node, 'daily', []);
    }

    public function test_creating_too_many_servers_will_raise_exception(): void
    {
        $game = factory(Game::class)->create();
        $node = factory(Node::class)->create();
        $user = factory(User::class)->create([
            'server_limit' => 1,
        ]);
        factory(Transaction::class)->create([
            'value'   => 5000,
            'user_id' => $user->id,
        ]);

        $server = factory(Server::class, 2)->create([
            'panel_id' => 2,
            'game_id'  => $game->id,
            'node_id'  => $node->id,
            'user_id'  => $user->id,
        ]);

        $this->partialMock(DeployCostService::class)
             ->shouldReceive('getCostPerPeriod')
             ->andReturn(200)
             ->once();

        $this->expectException(TooManyServersException::class);

        app(DeployCreationService::class)->preChecks($user, $node, 'daily', []);
    }
}