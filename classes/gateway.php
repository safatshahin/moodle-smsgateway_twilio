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

use core_sms\manager;
use core_sms\message;

/**
 * Twilio SMS gateway.
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
            conditions: ['id' => $message->gatewayid, 'enabled' => 1, 'gateway' => 'smsgateway_twilio\gateway',],
        );
        $status = \core_sms\message_status::GATEWAY_NOT_AVAILABLE;
        if ($twilioconfig) {
            $config = (object) json_decode($twilioconfig, true, 512, JSON_THROW_ON_ERROR);
            $recipientnumber = manager::format_number(
                phonenumber: $message->recipientnumber,
                countrycode: isset($config->countrycode) ?? null,
            );

            // Send the sms.
        }

        return $message->with(
            status: $status,
        );
    }

    #[\Override]
    public function get_send_priority(message $message): int {
        return 50;
    }
}