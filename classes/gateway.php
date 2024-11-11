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

namespace smsgateway_twilio;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../extlib/Twilio/autoload.php');

use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;
use core_sms\manager;
use core_sms\message;

/**
 * Twilio SMS gateway.
 *
 * For more information: https://www.twilio.com/docs/messaging/quickstart/php.
 * Library: https://github.com/twilio/twilio-php.
 *
 * @package    smsgateway_twilio
 * @copyright  2024 Safat Shahin <safat.shahin@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gateway extends \core_sms\gateway {
    #[\Override]
    public function send(
        message $message,
    ): message {
        global $DB;
        // Get the config from the message record.
        $twilioconfig = $DB->get_field(
            table: 'sms_gateways',
            return: 'config',
            conditions: ['id' => $message->gatewayid, 'enabled' => 1, 'gateway' => static::class],
        );
        $status = \core_sms\message_status::GATEWAY_NOT_AVAILABLE;
        if ($twilioconfig) {
            $config = (object) json_decode($twilioconfig, true, 512, JSON_THROW_ON_ERROR);
            $recipientnumber = manager::format_number(
                phonenumber: $message->recipientnumber,
                countrycode: isset($config->countrycode) ?? null,
            );

            $sid = $config->account_sid;
            $token = $config->auth_token;
            $twilio = $this->make_twilio_client($sid, $token);
            try {
                $twilio->messages->create(
                    to: $recipientnumber,
                    options: [
                        "from" => $config->twilio_phone_number,
                        "body" => $message->content,
                    ],
                );
                // It is also possible set up a status callback for a proper status.
                // See: https://www.twilio.com/docs/messaging/guides/track-outbound-message-status.
                $status = \core_sms\message_status::GATEWAY_SENT;
            } catch (TwilioException $e) {
                $status = \core_sms\message_status::GATEWAY_FAILED;
            }
        }

        return $message->with(
            status: $status,
        );
    }

    #[\Override]
    public function get_send_priority(message $message): int {
        return 50;
    }

    /**
     * Make a twilio client.
     *
     * Separated to allow mocking.
     *
     * @param string $sid
     * @param string $token
     * @return Client
     */
    protected function make_twilio_client(string $sid, string $token): \Twilio\Rest\Client {
        return new \Twilio\Rest\Client($sid, $token);
    }
}
