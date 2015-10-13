<?php

namespace CurrencyCloud\Tests;

use CurrencyCloud\CurrencyCloud;
use CurrencyCloud\EntryPoint\AccountsEntryPoint;
use CurrencyCloud\EntryPoint\AuthenticateEntryPoint;
use CurrencyCloud\EntryPoint\BalancesEntryPoint;
use CurrencyCloud\EntryPoint\BeneficiariesEntryPoint;
use CurrencyCloud\EntryPoint\ContactsEntryPoint;
use CurrencyCloud\EntryPoint\ConversionsEntryPoint;
use CurrencyCloud\EntryPoint\PayersEntryPoint;
use CurrencyCloud\EntryPoint\PaymentsEntryPoint;
use CurrencyCloud\EntryPoint\RatesEntryPoint;
use CurrencyCloud\EntryPoint\ReferenceEntryPoint;
use CurrencyCloud\EntryPoint\SettlementsEntryPoint;
use CurrencyCloud\EntryPoint\TransactionsEntryPoint;
use CurrencyCloud\Session;
use DateTime;
use GuzzleHttp\Client;
use PHPUnit_Framework_TestCase;

class BaseCurrencyCloudTestCase extends PHPUnit_Framework_TestCase
{

    /**
     * @param string $loginId
     * @param string $apiKey
     *
     * @return CurrencyCloud
     */
    protected function getClient(
        $loginId = 'rjnienaber@gmail.com',
        $apiKey = 'ef0fd50fca1fb14c1fab3a8436b9ecb65f02f129fd87eafa45ded8ae257528f0'
    ) {
        //We do not use static method in CurrencyCloud because we are not testing it
        $session = new Session(Session::ENVIRONMENT_DEMONSTRATION, $loginId, $apiKey);

        $client = new Client();
        return new CurrencyCloud(
            $session,
            new AuthenticateEntryPoint($session, $client),
            new AccountsEntryPoint($session, $client),
            new BalancesEntryPoint($session, $client),
            new BeneficiariesEntryPoint($session, $client),
            new ContactsEntryPoint($session, $client),
            new ConversionsEntryPoint($session, $client),
            new PayersEntryPoint($session, $client),
            new PaymentsEntryPoint($session, $client),
            new ReferenceEntryPoint($session, $client),
            new RatesEntryPoint($session, $client),
            new SettlementsEntryPoint($session, $client),
            new TransactionsEntryPoint($session, $client)
        );
    }

    /**
     * @param string $authToken
     *
     * @return CurrencyCloud
     */
    protected function getAuthenticatedClient($authToken = 'e5070d4a16c5ffe4ed9fb268a2a716be')
    {
        $client = $this->getClient();
        $client->getSession()->setAuthToken($authToken);
        return $client;
    }

    protected function validateObjectStrictName($object, $dummy)
    {
        $this->assertInternalType('object', $object);
        foreach ($dummy as $key => $original) {
            $parts = explode('_', $key);
            $uCased = implode('', array_map('ucfirst', $parts));
            $getter = sprintf('get%s', $uCased);
            if (!is_callable([$object, $getter])) {
                $getter = sprintf('is%s', $uCased);
                if (!is_callable([$object, $getter])) {
                    $this->fail(
                        sprintf('Found property "%s" but not method "(is|get)%s". Is it wrongly named?', $key, $uCased)
                    );
                }
            }
            $value = $object->$getter();
            if ($value instanceof DateTime) {
                $value = $value->format(DateTime::RFC3339);
            } else if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            $this->assertEquals($original, $value, sprintf('Property "%s" with method "%s"', $key, $getter));
            unset($dummy[$key]);
        }
        $this->assertEquals(0, count($dummy));
    }
}