<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Controllers\TransactionController;
use App\Models\User;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    /**
     * @var TransactionController
     */
    private $testClass;

    /**
     * @var User|MockObject
     */
    private $user;

    /**
     * @var User|MockObject
     */
    private $payer;

    /**
     * @var User|MockObject
     */
    private $payee;

    /**
     * Unit test for transaction method
     *
     * @return void
     */
    public function testTransaction(): void
    {
        $requestParams = [
            'value' => 10.0,
            'payer' => 1,
            'payee' => 2
        ];

        $request = new \Illuminate\Http\Request();
        $request->setMethod('POST');
        $request->replace($requestParams);

        $this->user
            ->expects($this->any())
            ->method('find')
            ->willReturn($this->payer);
        $this->user
            ->expects($this->any())
            ->method('find')
            ->willReturn($this->payee);

        $this->payer
            ->expects($this->once())
            ->method('isShopkeeper')
            ->willReturn(1);

        $this->testClass->transaction($request);
    }

    /**
     * Setup method
     */
    protected function setUp(): void
    {
        $this->user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->addMethods(['find'])
            ->getMock();
        $this->payer = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->addMethods(['isShopkeeper'])
            ->getMock();
        $this->payee = $this->createMock(User::class);

        $this->testClass = new TransactionController();

        parent::setUp();
    }
}
