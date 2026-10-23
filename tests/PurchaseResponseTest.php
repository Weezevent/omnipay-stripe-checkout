<?php

namespace Omnipay\StripeCheckout\Tests;

use Omnipay\StripeCheckout\Messages\CompletePurchaseRequest;
use Omnipay\StripeCheckout\Messages\PurchaseResponse;
use Omnipay\StripeCheckout\Tests\Fixtures\Request;
use Omnipay\Common\Message\RequestInterface;
use Omnipay\Tests\TestCase;
use Stripe\Checkout\Session;

class PurchaseResponseTest extends TestCase
{
    const SESSION_ID_1 = 'S101';
    const PAYMENT_INTENT_ID = 'PI101';

    public function creationProvider()
    {
        $request = new Request();
        $session = new Session(self::SESSION_ID_1);
        $session->payment_intent = self::PAYMENT_INTENT_ID;

        return [
            'no session' => [$request, [], null],
            'with session' => [
                $request,
                ['session' => $session],
                self::SESSION_ID_1
            ],
        ];
    }

    /**
     * @param RequestInterface $request
     * @param $data
     * @param string|null $expectedSessionID
     *
     * @dataProvider creationProvider
     */
    public function testCreation(RequestInterface $request, $data, string $expectedSessionID = null)
    {
        // I can't seem to get $this->expectException() to work, so I'm having to do it manually.
        try {
            $purchaseResponse = new PurchaseResponse($request, $data);
            $this->assertNotNull($expectedSessionID); // If it gets here, we expect the session to be present.
        } catch (\InvalidArgumentException $e) {
            // It will throw this exception if the session is not present.
            $this->assertNull($expectedSessionID);

            return; // Stop the test now.
        }

        // Check the session id.
        $this->assertEquals($expectedSessionID, $purchaseResponse->getSessionID());

        // Check the responses to various other methods, that are all return hard-coded values.
        $this->assertFalse($purchaseResponse->isSuccessful());
        $this->assertTrue($purchaseResponse->isRedirect());
        $this->assertEquals('', $purchaseResponse->getRedirectUrl());
        $this->assertEquals('GET', $purchaseResponse->getRedirectMethod());
        $this->assertEquals([], $purchaseResponse->getRedirectData());

        // Check the transaction reference.
        $this->assertEquals(self::PAYMENT_INTENT_ID, $purchaseResponse->getTransactionReference());
    }
}
