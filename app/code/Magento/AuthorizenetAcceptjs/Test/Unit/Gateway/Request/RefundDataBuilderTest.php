<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\Request\RefundDataBuilder;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\AuthorizenetAcceptjs\Model\PassthroughDataObject;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RefundDataBuilderTest extends TestCase
{
    /**
     * @var RefundDataBuilder
     */
    private $builder;

    /**
     * @var Payment|MockObject
     */
    private $paymentMock;

    /**
     * @var Payment|MockObject
     */
    private $paymentDOMock;

    protected function setUp()
    {
        $this->paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->paymentDOMock->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->builder = new RefundDataBuilder(
            new SubjectReader()
        );
    }

    public function testBuild()
    {
        $this->paymentMock->method('getAdditionalInformation')
            ->willReturnMap([
                ['opaqueDataDescriptor', 'encfoo'],
                ['opaqueDataValue', 'encbar']
            ]);

        $this->paymentMock->method('decrypt')
            ->willReturnMap([
                ['encfoo', 'foo'],
                ['encbar', 'bar']
            ]);

        $expected = [
            'transactionRequest' => [
                'payment' => [
                    'opaqueData' => [
                        'dataDescriptor' => 'foo',
                        'dataValue' => 'bar'
                    ]
                ]
            ]
        ];

        $buildSubject = [
            'payment' => $this->paymentDOMock,
            'amount' => 123.45
        ];

        $this->assertEquals($expected, $this->builder->build($buildSubject));
    }
}
