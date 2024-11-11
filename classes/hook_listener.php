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

use core_sms\hook\after_sms_gateway_form_hook;

/**
 * Hook listener for Twilio sms gateway.
 *
 * @package    smsgateway_twilio
 * @copyright  2024 Safat Shahin <safat.shahin@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_listener {
    /**
     * Hook listener for the sms gateway setup form.
     *
     * @param after_sms_gateway_form_hook $hook The hook to add to sms gateway setup.
     */
    public static function set_form_definition_for_twilio_sms_gateway(after_sms_gateway_form_hook $hook): void {
        if ($hook->plugin !== 'smsgateway_twilio') {
            return;
        }

        $mform = $hook->mform;

        $mform->addElement('static', 'information', get_string('twilio_information', 'smsgateway_twilio'));

        $mform->addElement(
            'text',
            'account_sid',
            get_string('account_sid', 'smsgateway_twilio'),
            'maxlength="255" size="20"',
        );
        $mform->setType('account_sid', PARAM_TEXT);
        $mform->addRule('account_sid', get_string('maximumchars', '', 255), 'maxlength', 255);
        $mform->addRule('account_sid', null, 'required');
        $mform->setDefault(
            elementName: 'account_sid',
            defaultValue: '',
        );

        $mform->addElement(
            'passwordunmask',
            'auth_token',
            get_string('auth_token', 'smsgateway_twilio'),
            'maxlength="255" size="20"',
        );
        $mform->setType('auth_token', PARAM_TEXT);
        $mform->addRule('auth_token', get_string('maximumchars', '', 255), 'maxlength', 255);
        $mform->addRule('auth_token', null, 'required');
        $mform->setDefault(
            elementName: 'auth_token',
            defaultValue: '',
        );

        $mform->addElement(
            'text',
            'twilio_phone_number',
            get_string('twilio_phone_number', 'smsgateway_twilio'),
            'maxlength="255" size="20"',
        );
        $mform->setType('twilio_phone_number', PARAM_TEXT);
        $mform->addRule('twilio_phone_number', get_string('maximumchars', '', 255), 'maxlength', 255);
        $mform->addRule('twilio_phone_number', null, 'required');
        $mform->setDefault(
            elementName: 'twilio_phone_number',
            defaultValue: '',
        );
    }
}
