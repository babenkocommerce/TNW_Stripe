<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\Stripe\Test\Unit\Gateway\Validator\ResponseValidator;

use TNW\Stripe\Gateway\Helper\SubjectReader;
use TNW\Stripe\Gateway\Validator\ResponseValidator\Authorize;
use Magento\Framework\Phrase;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Test Authorize
 */
class AuthorizeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Authorize
     */
    private $responseValidator;

    /**
     * @var ResultInterfaceFactory|MockObject
     */
    private $resultInterfaceFactory;

    /**
     * @var SubjectReader|MockObject
     */
    private $subjectReader;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->resultInterfaceFactory = $this->getMockBuilder(ResultInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->subjectReader = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->setMethods(['readResponseObject'])
            ->getMock();

        $this->responseValidator = new Authorize(
            $this->resultInterfaceFactory,
            $this->subjectReader
        );
    }

    /**
     * Run test for validate method
     *
     * @param array $validationSubject
     * @param bool $isValid
     * @param Phrase[] $messages
     * @return void
     *
     * @dataProvider dataProviderTestValidate
     */
    public function testValidate(array $validationSubject, $isValid, $messages)
    {
        $this->resultInterfaceFactory->method('create')
            ->with([
                'isValid' => (bool)$isValid,
                'failsDescription' => $messages
            ]);

        $this->subjectReader->method('readResponseObject')
            ->with(['response' => ['object' => $validationSubject]])
            ->willReturn($validationSubject);

        $this->responseValidator->validate(['response' => ['object' => $validationSubject]]);
    }

    /**
     * @return array
     */
    public function dataProviderTestValidate()
    {
        return [
            [
                [
                    'status' => 'succeeded',
                    'outcome' => ['network_status'=>'approved_by_network']
                ],
                true,
                []
            ],
            [
                [
                    'status' => 'succeeded',
                    'outcome' => ['network_status'=>'declined_by_network']
                ],
                'isValid' => false,
                [
                    __('Transaction has been declined'),
                ]
            ],
            [
                [
                    'status' => 'failed',
                    'outcome' => ['network_status'=>'approved_by_network']
                ],
                'isValid' => false,
                [
                    __('Wrong transaction status')
                ]
            ],
        ];
    }
}
