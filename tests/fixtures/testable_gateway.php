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

namespace smsgateway_twilio\tests\fixtures;

use Twilio\Rest\Client as twilio_client;

/**
 * Class to extend the gateway and mock it for testing.
 *
 * @package    smsgateway_twilio
 * @copyright  2025 Safat Shahin <safat.shahin@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class testable_gateway extends \smsgateway_twilio\gateway {
    /**
     * @var twilio_client|null for client injection.
     */
    public static ?twilio_client $injected = null;

    #[\Override]
    protected function make_twilio_client(string $sid, string $token): twilio_client {
        if (self::$injected) {
            return self::$injected;
        }
        return parent::make_twilio_client($sid, $token);
    }
}
