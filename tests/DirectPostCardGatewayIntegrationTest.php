<?php

namespace Omnipay\NMI;


use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Tests\GatewayTestCase;
use RecurringPayment\Database;
use RecurringPayment\RecurringPayment as RecurringPayment;

/**
 * Class DirectPostGatewayIntegrationTest
 *
 * Tests the driver implementation by actually communicating with NMI using their demo account
 *
 * @package Omnipay\NMI
 */
class DirectPostCardGatewayIntegrationTest extends GatewayTestCase
{
    /** @var  DirectPostGateway */
    protected $gateway;
    /** @var  array */
    protected $purchaseOptions;

    /**
     * Instantiate the gateway and the populate the purchaseOptions array
     */
    public function setUp()
    {
        $this->gateway = new Gateway();
        $this->gateway->setUsername('demo');
        $this->gateway->setPassword('password');

        $this->purchaseOptions = [
            'amount' => (random_int(1, 900) / 100) + 1,
            'card' => $this->getValidCard(),
            'description' => 'My Description'
        ];
    }

    /**
     * Test an authorize transaction followed by a capture
     */
    public function testAuthorizeCapture()
    {
        $response = $this->gateway->authorize($this->purchaseOptions)->send();

        $this->assertEquals('SUCCESS', $response->getMessage());
        $this->assertTrue($response->isSuccessful());

        $captureResponse = $this->gateway->capture([
            'amount' => '1.00',
            'transactionReference' => $response->getTransactionReference()
        ])->send();

        $this->assertTrue($captureResponse->isSuccessful());
        $this->assertEquals('SUCCESS', $captureResponse->getMessage());
    }

    public function testCreateCardSuccess()
    {
        $response = $this->gateway->createCard($this->purchaseOptions)->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('Customer Added', $response->getMessage());
        return $response->getCardReference();
    }

    /**
     * Test a purchase transaction followed by a refund
     */
    public function testPurchaseRefund()
    {
        $response = $this->testPurchaseSuccess();

        $refundResponse = $this->gateway->refund([
            'transactionReference' => $response->getTransactionReference()
        ])->send();

        $this->assertTrue($refundResponse->isSuccessful());
        $this->assertEquals('SUCCESS', $refundResponse->getMessage());
    }

    /**
     * Test a purchase transaction followed by a void
     */
    public function testPurchaseVoid()
    {
        $response = $this->testPurchaseSuccess();

        $voidResponse = $this->gateway->void([
            'transactionReference' => $response->getTransactionReference()
        ])->send();

        $this->assertTrue($voidResponse->isSuccessful());
        $this->assertEquals('Transaction Void Successful', $voidResponse->getMessage());
    }

    public function testCreateRecurringFailureRequiredData()
    {
        $options = $this->getValidRecurringData();
        unset($options['startDate']);
        try {
            $response = $this->gateway->createRecurring($options)->send();
            self::fail('Did not throw exception');
        } catch (InvalidRequestException $e) {
            //Just need to make sure it's thrown
        }
    }

    private function getValidRecurringData(
        bool $addCommission = false,
        bool $useMerchantProfileId = false,
        bool $includeInvoice = false
    )
    {
        $requestOptions = [
            'startDate' => date('Y-m-d'),
            'amount' => '10.00',
            'totalCount' => '3',
            'frequency' => 'Yearly',
            'description' => 'unittest',
            'cardReference' => $this->testCreateCardSuccess(),
            'locationID' => 13579,
            'subDomain' => 'http://www.test.com',
            'email' => 'test@testDigiProMedia.com'
        ];
        if ($useMerchantProfileId) {
            $requestOptions['merchantProfileId'] = '2195895';
        }
        if ($includeInvoice) {
            $requestOptions['invoice'] = '123456';
        }
        if ($addCommission) {
            $requestOptions['commission'] = [
                'fromAccount' => 32248512,
                'toAccount' => 32248513,
                'amount' => '2.00'
            ];
        }
        return $requestOptions;
    }

    public function testUpdateRecurring()
    {
        $recurringData = $this->getValidRecurringData();
        $recurringData['recurringReference'] = $this->testCreateRecurring();
        $recurringData['totalCount'] = 5;
        $recurringData['description'] = 'unittest updated!';
        $recurringData['nextDate'] = 1 + (int)(new \DateTime())->format('Y') . '-1-1';
        $response = $this->gateway->updateRecurring($recurringData)->send();
        static::assertSame('Recurring payment updated successfully.', $response->getMessage());
        static::assertEquals('00', $response->getCode());
        static::assertTrue($response->isSuccessful());
        static::assertEquals($recurringData['recurringReference'], $response->getRecurringReference());
        $this->verifyRecurringResponse($response);
    }

    public function testCreateRecurring()
    {
        return $this->createRecurringCall();
    }

    public function testCreateRecurringWithInvoice()
    {
        $recurringId = $this->createRecurringCall(false, true);
        $recurringPayments = new RecurringPayment();
        $recurringInfo = $recurringPayments->getPayment($recurringId);
        self::assertEquals('123456', $recurringInfo->invoice);
        return $recurringId;
    }

    public function testCreateRecurringMerchantProfileId()
    {
        return $this->createRecurringCall(true);
    }

    public function testCreateRecurringFuture()
    {
        $options = $this->getValidRecurringData();
        $tomorrow = new \DateTime('tomorrow');
        $options['startDate'] = $tomorrow->format('Y-m-d');
        $response = $this->gateway->createRecurring($options)->send();
        static::assertSame('Recurring payment setup successfully.', $response->getMessage());
        static::assertEquals('00', $response->getCode());
        static::assertTrue($response->isSuccessful());
        static::assertFalse($response->charged());
        static::assertGreaterThan(0, $response->getRecurringReference());
        static::assertNull($response->getTransactionReference());
        $this->verifyRecurringResponse($response);
        return $response->getRecurringReference();
    }

    public function testCreateRecurringPaymentFailed()
    {
        $data = $this->getValidRecurringData();
        $data['cardReference'] = 'fakefake!!';
        $this->gateway->setTestMode(false); //Force failure
        $response = $this->gateway->createRecurring($data)->send();
        static::assertStringStartsWith('Invalid Customer Vault ID specified REFID:', $response->getMessage());
        static::assertFalse($response->isSuccessful());
        static::assertFalse($response->charged());
        static::assertEquals('3', $response->getCode());
        static::assertNull($response->getRecurringReference());
        static::assertNull($response->getTransactionReference());
    }

    public function testCreateRecurringPaymentFailedBadCardRef()
    {
        $data = [
            'startDate' => date('Y-m-d'),
            'amount' => '10.00',
            'totalCount' => '3',
            'frequency' => 'Yearly',
            'description' => 'unittest',
            'cardReference' => 'fakefakefake',
            'locationID' => 13579,
            'subDomain' => 'http://www.test.com',
            'email' => 'test@testDigiProMedia.com'
        ];
        $response = $this->gateway->createRecurring($data)->send();
        static::assertStringStartsWith('Invalid Customer Vault ID specified REFID:', $response->getMessage());
        static::assertFalse($response->isSuccessful());
        static::assertFalse($response->charged());
        static::assertNull($response->getRecurringReference());
        static::assertNull($response->getTransactionReference());
    }

    public function testUpdateRecurringPartialData()
    {
        $recurringData = [
            'recurringReference' => $this->testCreateRecurring(),
            'totalCount' => 5,
            'description' => 'unittest updated!'
        ];
        $recurringPayments = new RecurringPayment();
        $preUpdatedPayment = $recurringPayments->getPayment($recurringData['recurringReference']);
        $response = $this->gateway->UpdateRecurring($recurringData)->send();
        static::assertSame('Recurring payment updated successfully.', $response->getMessage());
        static::assertEquals('00', $response->getCode());
        $postUpdatedPayment = $recurringPayments->getPayment($recurringData['recurringReference']);
        $this->verifyRecurringData($recurringData, $postUpdatedPayment, $preUpdatedPayment);
        static::assertEquals(1, $postUpdatedPayment->success_count);
        static::assertEquals($preUpdatedPayment->next_date, $postUpdatedPayment->next_date);
        static::assertEquals($preUpdatedPayment->start_date, $postUpdatedPayment->start_date);
    }

    private function verifyRecurringData($recurringData, $postUpdatedPayment, $preUpdatedPayment)
    {
        static::assertEquals($recurringData['totalCount'], $postUpdatedPayment->total_count);
        static::assertEquals($recurringData['description'], $postUpdatedPayment->description);
        static::assertEquals($recurringData['recurringReference'], $postUpdatedPayment->id);
        static::assertEquals($preUpdatedPayment->start_date, $postUpdatedPayment->start_date);
        static::assertEquals($preUpdatedPayment->card_reference, $postUpdatedPayment->card_reference);
        static::assertEquals($preUpdatedPayment->gateway, $postUpdatedPayment->gateway);
        static::assertEquals($preUpdatedPayment->gateway_password, $postUpdatedPayment->gateway_password);
        static::assertEquals($preUpdatedPayment->gateway_username, $postUpdatedPayment->gateway_username);
        static::assertNotEquals($preUpdatedPayment->description, $postUpdatedPayment->description);
        static::assertNotEquals($preUpdatedPayment->total_count, $postUpdatedPayment->total_count);
    }

    public function testUpdateRecurringNeedNewPayment()
    {
        $recurringReference = $this->testCreateRecurring();
        $recurringData = [
            'recurringReference' => $recurringReference,
            'totalCount' => 5,
            'frequency' => 'Monthly',
            'description' => 'unittest updated!',
            'nextDate' => date('Y-m-d'),
            'start_date' => '2019-01-01'
        ];
        $recurringPayments = new RecurringPayment();
        $preUpdatedPayment = $recurringPayments->getPayment($recurringReference);
        $today = date('Y-m-d');
        $preUpdatedPayment->next_date = $today;
        $preUpdatedPayment->start_date = '2019-01-01';
        $recurringPayments->updatePayment($preUpdatedPayment);
        $dbInfo = new Database();
        $db = $dbInfo->getDb();
        $statement = $db->query("UPDATE recurring_payment SET next_date = '$today' WHERE id = $recurringReference");
        $result = $statement->execute();

        $response = $this->gateway->updateRecurring($recurringData)->send();
        static::assertSame('Recurring payment updated and charged successfully.', $response->getMessage());
        static::assertEquals('1', $response->getCode());
        $postUpdatedPayment = $recurringPayments->getPayment($recurringData['recurringReference']);
        $this->verifyRecurringData($recurringData, $postUpdatedPayment, $preUpdatedPayment);
        static::assertEquals(2, $postUpdatedPayment->success_count);
        static::assertEquals(strtolower($recurringData['frequency']), $postUpdatedPayment->frequency);
        static::assertCount(2, $postUpdatedPayment->getPaymentLogs());
        static::assertNotEquals($preUpdatedPayment->next_date, $postUpdatedPayment->next_date);
        static::assertNotEquals($today, $postUpdatedPayment->next_date);
        $this->verifyRecurringResponse($response);
    }

    public function testDeleteRecurring()
    {
        $recurringReference = $this->testCreateRecurring();
        $response = $this->gateway->deleteRecurring(['recurringReference' => $recurringReference])->send();
        static::assertSame('Recurring payment deleted successfully.', $response->getMessage());
        static::assertSame('0', $response->getCode());
        static::assertTrue($response->isSuccessful());
        static::assertFalse($response->charged());
        static::assertNull($response->getTransactionReference());
        static::assertEquals($recurringReference, $response->getRecurringReference());
        $this->verifyRecurringResponse($response);
    }

    public function testDeleteRecurringInvalidRecurring()
    {
        $response = $this->gateway->deleteRecurring(['recurringReference' => '31415926535798'])->send();
        static::assertEquals('Recurring payment not found.', $response->getMessage());
        static::assertFalse($response->isSuccessful());
    }

    public function testUpdateRecurringBadRecurringReference()
    {
        $recurringData = [
            'recurringReference' => 'abc213',
            'totalCount' => 5,
            'frequency' => 'Monthly',
            'description' => 'unittest updated!',
            'nextDate' => date('Y-m-d'),
            'start_date' => '2019-01-01'
        ];

        $response = $this->gateway->UpdateRecurring($recurringData)->send();
        static::assertSame('Invalid recurringReference.', $response->getMessage());
        static::assertEquals('00', $response->getCode());
        static::assertEmpty($response->getTransactionReference());
        static::assertFalse($response->isSuccessful());
    }

    private function getRecurringOptions($cardReference, $frequency = '1')
    {
        $requestParams = [
            'startDate' => '12/12/2019',
            'amount' => '10.00',
            'totalCount' => '3',
            'description' => 'Test Description',
            'frequency' => $frequency,
            'cardReference' => $cardReference
        ];
        return $requestParams;
    }

    private function verifyRecurringResponse($response)
    {
        static::assertFalse(isset($response->getData()['errors']),
            'Errors:' . json_encode($response->getData()['errors'] ?? ''));
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNotNull($response->getRecurringReference());
        $this->assertGreaterThan(0, $response->getRecurringReference());
    }

    private function createRecurringCall(bool $useMerchantProfileId = false, bool $includeInvoice = false)
    {
        $response = $this->gateway->createRecurring($this->getValidRecurringData(false, $useMerchantProfileId,
            $includeInvoice))->send();
        static::assertSame('Recurring payment setup and charged successfully.', $response->getMessage());
        static::assertEquals('1', $response->getCode());
        static::assertTrue($response->isSuccessful());
        static::assertTrue($response->charged());
        static::assertGreaterThan(0, $response->getRecurringReference());
        static::assertGreaterThan(0, $response->getTransactionReference());
        $this->verifyRecurringResponse($response);
        return $response->getRecurringReference();
    }

    public function testPurchaseSuccess()
    {
//        $this->gateway->setUsername("THEREFapi2020");
//        $this->gateway->setPassword("2020apiTHEREF");
//        $this->gateway->setTestMode(true);
        $response = $this->gateway->purchase($this->purchaseOptions)->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('SUCCESS', $response->getMessage());
        return $response;
    }

    public function testPurchaseSavedCardSuccess()
    {
        $cardReference = $this->testCreateCardSuccess();
        $requestData = [
            'cardReference' => $cardReference,
            'amount' => '1.00',
            'description' => 'Test description'
        ];

        $response = $this->gateway->purchase($requestData)->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('SUCCESS', $response->getMessage());
        return $response;
    }

    public function testPurchasePaymentTokenCardSuccess()
    {
        $requestData = [
            'paymentToken' => '00000000-000000-000000-000000000000',
            'amount' => '3.00',
            'description' => 'Test description'
        ];

        $response = $this->gateway->purchase($requestData)->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('SUCCESS', $response->getMessage());
        return $response;
    }

    public function testPurchasePaymentTokenCardFailure()
    {
        $requestData = [
            'paymentToken' => '000000A-000000-000000-000000000000',
            'amount' => '1.00',
            'description' => 'Test description'
        ];

        $response = $this->gateway->purchase($requestData)->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertStringStartsWith('Payment Token does not exist', $response->getMessage());
        return $response;
    }

    public function testTransaction()
    {
        $this->gateway->setUsername("THEREFapi2020");
        $this->gateway->setPassword("2020apiTHEREF");

        $requestData = [
           'transactionReference' => '5593854806'
        ];

        $response = $this->gateway->transaction($requestData)->send();
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('SETTLED', $response->getTransactionType());
        $this->assertNotEmpty($response->getMessage());
        $this->assertEquals(100, $response->getCode());
        $this->assertGreaterThan(40, $response->getData());
        $this->assertArrayHasKey('action', $response->getData());
        $this->assertTrue(is_array($response->getData()));
        $this->assertTrue(is_array($response->getData()['action']));
        $this->assertNull($response->getCardReference());
        $this->assertEquals($requestData['transactionReference'], $response->getTransactionReference());
        $this->assertEquals('1.00', $response->getAmount());
        $this->assertFalse($response->canRefund());
        $this->assertFalse($response->isPending());
        $this->assertTrue($response->canVoid());
        $this->assertFalse($response->isRefunded());
        $this->assertFalse($response->isVoided());
        $this->assertEquals('complete', $response->getState());
        $this->assertEquals('439648313', $response->getBatchNumber());
        $this->assertEquals('2020-08-24T22:11:33+00:00', $response->getSettlementDate());
    }

    public function testTransactions()
    {
        $this->gateway->setUsername("THEREFapi2020");
        $this->gateway->setPassword("2020apiTHEREF");

        $requestData = [
           'startDate' => '2021-05-08',
           'endDate' => '2021-05-09',

        ];

        $response = $this->gateway->transactions($requestData)->send();
        $this->assertTrue($response->isSuccessful());
        self::assertCount(8, $response->getTransactions());
        foreach ($response->getTransactions() as $transaction) {
            $this->assertTrue(is_string($transaction['transactionType']));
            $this->assertNotEmpty($transaction['message']);
            $this->assertGreaterThan(1, $transaction['code']);
            $this->assertGreaterThan(40, $transaction['data']);
            $this->assertArrayHasKey('action', $transaction['data']);
            $this->assertTrue(is_array($transaction['data']));
            $this->assertTrue(is_array($transaction['data']['action']));
            $this->assertNull($transaction['cardReference']);
            $this->assertNotNull($transaction['transactionReference']);
            $this->assertGreaterThan('0.00', $transaction['amount']);
            $this->assertNotNull($transaction['canRefund']);
            $this->assertNotNull($transaction['isPending']);
            $this->assertNotNull($transaction['canVoid']);
            $this->assertNotNull($transaction['isRefunded']);
            $this->assertNotNull($transaction['isVoided']);
            $this->assertNotEmpty($transaction['transactionId']);
            $this->assertTrue(is_string($transaction['state']));
            if($transaction['transactionType'] === 'SETTLED') {
                $this->assertNotNull($transaction['batchNumber']);
                $this->assertTrue(is_string($transaction['settlementDate']));
            } else {
                $this->assertNull($transaction['settlementDate']);
                $this->assertNull($transaction['batchNumber']);
            }
        }
    }

    /*public function testSwipeSuccess()
    {
        $options = [
           'amount' => '4.00',
           'swipe' => '%B4012881888818888^Demo/Customer^2412101001020001000000701000000?;4012881888818888=24121010010270100001?'
        ];
        $response = $this->gateway->swipe($options)->send();
        $this->verifyPurchaseResult($response);
    }*/

    /*public function testEncryptedSwipeSuccess()
    {
        $options = [
           'amount' => '4.00',
           'swipe' => 'nELVjn9xI5m/CDSThOgMSvaXuBjCg+J4fuTLbQRlVXtwRd4toMgcdHEv9SFUCXw/BmTRbN/Vb431eoW6JawR983M2TbpjEd7qgmE87Y6C2A/sjoQWfU6WQGXJI08TRZXxFtds6ksYqRckthKT89Ym8q6AuXX4UR1CH/jsA20TKGpEolA/XHfXMS72nNLMcNti0+m5W6oPik50m7qSZJl0xFxXNsgN8mrKzMGhkQHsnPGSKjN30jkAa2ne8rYfQuoZQ/M9FEZzQWUX7JlKcrlWufO54jtuSC+DeGR+6pCzUqXctaGeKhIC12BfGVGNcRQMtHVZadGqXyR708fjeJdqg=='
        ];
        $response = $this->gateway->encryptedSwipe($options)->send();
        $this->verifyPurchaseResult($response);
    }*/
}
