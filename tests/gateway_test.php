<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

declare(strict_types=1);

namespace smsgateway_twilio;

use advanced_testcase;
use core\di;
use core\http_client;
use core_sms\message_status;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use smsgateway_twilio\tests\fixtures\testable_gateway;
use Twilio\Rest\Client;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../extlib/Twilio/autoload.php');
require_once(__DIR__ . '/fixtures/testable_gateway.php');

/**
 * Test the twilio gateway.
 *
 * @package    smsgateway_twilio
 * @copyright  2025 Safat Shahin <safat.shahin@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(gateway::class)]
final class gateway_test extends advanced_testcase {
    /**
     * Test if the sending sms through Twilio is successful.
     */
    public function test_send_success(): void {
        $this->resetAfterTest();
        $config = (object) [
            'account_sid' => 'ACXXXX',
            'auth_token' => 'secrettoken',
            'twilio_phone_number' => '+6112345678',
            'countrycode' => 'AU',
        ];

        // Queue a Twilio style created response.
        $body = json_encode(['sid' => 'SM123', 'status' => 'queued']);
        $history = [];
        $twilio = $this->make_twilio_with_guzzle_mock(
            [
                new Response(201, ['Content-Type' => 'application/json'], $body),
            ],
            $history,
        );
        // Inject mocked Twilio into our testable gateway seam.
        testable_gateway::$injected = $twilio;
        // Create the gateway through the manager.
        $manager = di::get(\core_sms\manager::class);
        $gw = $manager->create_gateway_instance(
            classname: testable_gateway::class,
            name: 'twilio',
            enabled: true,
            config: $config,
        );
        $message = $manager->send(
            recipientnumber: '+61400000000',
            content: 'Hello world',
            component: 'core',
            messagetype: 'test',
            recipientuserid: null,
            async: false,
        );

        $this->assertSame(message_status::GATEWAY_SENT, $message->status);
        $this->assertEquals($gw->id, $message->gatewayid);
        $this->assertCount(1, $history);
        $req = $history[0]['request'];
        $this->assertSame('POST', $req->getMethod());
        $this->assertStringContainsString('/2010-04-01/Accounts/ACXXXX/Messages.json', (string) $req->getUri());
    }

    /**
     * Test failed sending via Twilio.
     */
    public function test_send_failure_marks_failed(): void {
        $this->resetAfterTest(true);

        $config = (object) [
            'account_sid' => 'ACXXXX',
            'auth_token' => 'secrettoken',
            'twilio_phone_number' => '+6112345678',
            'countrycode' => 'AU',
        ];

        $history = [];
        $twilio = $this->make_twilio_with_guzzle_mock([
            new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'code' => 21606,
                'message' => 'invalid from',
            ])),
        ], $history);

        testable_gateway::$injected = $twilio;

        $manager = di::get(\core_sms\manager::class);
        $gw = $manager->create_gateway_instance(
            classname: testable_gateway::class,
            name: 'twilio',
            enabled: true,
            config: $config,
        );

        $message = $manager->send(
            recipientnumber: '+61400000000',
            content: 'Hello world',
            component: 'core',
            messagetype: 'test',
            recipientuserid: null,
            async: false,
        );

        $this->assertSame(message_status::GATEWAY_FAILED, $message->status);
        $this->assertEquals($gw->id, $message->gatewayid);
    }

    /**
     * Build a Twilio client that uses a Guzzle MockHandler, and capture history.
     *
     * @param array $queued
     * @param array $history
     * @return Client
     */
    private function make_twilio_with_guzzle_mock(array $queued, array &$history): Client {
        $mock = new MockHandler($queued);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($history));
        $guzzle = new http_client(['handler' => $stack]);

        $twilio = new Client('ACXXXX', 'secrettoken');
        $twilio->setHttpClient(new \Twilio\Http\GuzzleClient($guzzle));
        return $twilio;
    }
}
