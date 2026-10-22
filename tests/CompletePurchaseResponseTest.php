<?php

namespace Omnipay\StripeCheckout\Tests;

use Omnipay\StripeCheckout\Messages\CompletePurchaseRequest;
use Omnipay\StripeCheckout\Messages\CompletePurchaseResponse;
use Omnipay\StripeCheckout\Tests\Fixtures\PaymentIntent;
use Mockery;
use Omnipay\Tests\TestCase;

class CompletePurchaseResponseTest extends TestCase
{
    const TX_ID = 'complete-purchase-request-id';

    public function creationProvider()
    {
        $request = Mockery::mock(CompletePurchaseRequest::class);
        $request->shouldReceive('getTransactionId')->andReturn(self::TX_ID);

        return [
            'no payment intent' => [
                $request,
                [],
                false,
                'Could not retrieve payment',
                null,
            ],
            'successful' => [
                $request,
                ['paymentIntent' => new PaymentIntent(PaymentIntent::PI_ID, CompletePurchaseResponse::STATUS_SUCCESS)],
                true,
                CompletePurchaseResponse::STATUS_SUCCESS,
                PaymentIntent::PI_ID,
            ],
            'canceled' => [
                $request,
                ['paymentIntent' => new PaymentIntent(PaymentIntent::PI_ID, CompletePurchaseResponse::STATUS_CANCELED)],
                false,
                'Canceled by customer',
                PaymentIntent::PI_ID,
            ],
            'unknown' => [
                $request,
                ['paymentIntent' => new PaymentIntent(PaymentIntent::PI_ID, 'Other')],
                false,
                'Unknown error',
                PaymentIntent::PI_ID,
            ],
        ];
    }

    /**
     * @param CompletePurchaseRequest $request
     * @param $data
     * @param bool $expectSuccess
     * @param string $expectMessage
     * @param string|null $expectedTransactionReference
     *
     * @dataProvider creationProvider
     */
    public function testCreation(
        CompletePurchaseRequest $request,
        $data,
        bool $expectSuccess,
        string $expectMessage,
        string $expectedTransactionReference = null
    ) {
        $completePurchaseResponse = new CompletePurchaseResponse($request, $data);

        $this->assertEquals($expectSuccess, $completePurchaseResponse->isSuccessful());
        $this->assertEquals($expectMessage, $completePurchaseResponse->getMessage());
        $this->assertNull($completePurchaseResponse->getCode());

        $this->assertEquals($expectedTransactionReference, $completePurchaseResponse->getTransactionReference());
        $this->assertEquals(self::TX_ID, $completePurchaseResponse->getTransactionId());
    }
}
