<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ApiTest extends TestCase
{
    /**
     * Test the API default endpoint. It should contain currencies.
     */
    public function testIndex()
    {
        $this->get('/');

        $this->assertJson($this->response->getContent());
        $this->seeJsonStructure ([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'symbol'
                ]
            ]
        ]);
    }

    /**
     * Test that the exchange is working without an amount.
     */
    public function testRateDefaultAmount()
    {
        $this
            ->get('/exchange/EUR-CHF')
            ->seeJsonStructure ([
                'value',
                '_ts'
            ])
            ->seeJson ([
                'value' => 1.1689,
            ])
            ->assertJson($this->response->getContent());
    }

    /**
     * Test that the amount option is working.
     */
    public function testRatSetAmount()
    {
        $this
            ->get('/exchange/EUR-CHF?amount=100')
            ->seeJsonStructure ([
                'value',
                '_ts'
            ])
            ->seeJson ([
                'value' => 116.89,
            ])
            ->assertJson($this->response->getContent());
    }


    /**
     * Test that the amount option is working.
     */
    public function testPostNewRate()
    {
        $this
            ->post('/rates', [
                'source' => 'CHF',
                'target' => 'EUR',
                'rate' => 1.1011
            ])
            ->seeJsonStructure ([
                'source_id',
                'target_id',
                'rate'
            ])
            ->seeJson ([
                'rate' => 1.1011,
            ])
            ->assertJson($this->response->getContent());
    }


    /**
     * Test that the reverse of the new rate is working
     */
    public function testNewRate()
    {
        $this
            ->get('/exchange/EUR-CHF?amount=100')
            ->seeJsonStructure ([
                'value',
                '_ts'
            ])
            ->seeJson ([
                'value' => 90.82,
            ])
            ->assertJson($this->response->getContent());
    }

}
