<?php

namespace ACPT\Tests;

class AuthTest extends RestApiV1TestCase
{
    /**
     * @test
     */
    public function return_403_for_unauthorized_calls()
    {
        $response = $this->callRestApi('GET', '/cpt');

        $this->assertEquals(403, $response['status']);
    }
}
