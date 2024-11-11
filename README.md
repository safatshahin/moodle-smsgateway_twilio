# Twilio SMS Gateway
Twilio is a paid SMS gateway solution with a large amount of features. Use this plugin to integrate Twilio messaging service in Moodle.
Please note, this plugin uses Twilio Messaging API, not Verify API. Please visit https://www.twilio.com/ for more information.

---

## Installation instructions
_Follow these steps to install Question to activity:_
1. Download the plugin to the ___{moodle directory}/sms/gateway/___ directory as a new folder called ___twilio___. This can be done in either of the following ways:
    - With Git from within the _/sms/gateway_ directory, running the following command:
        ```
        git clone https://github.com/safatshahin/moodle-smsgateway_twilio
        ```
    - Or by downloading the code manually from https://github.com/safatshahin/moodle-smsgateway_twilio and extracting it to the _question/bank/qtoactivity_ directory.

2. Access the Admin Dashboard from your Moodle site to automatically trigger the install, or use the cli.

---

## Usage
Use the following URL to get started with messaging using Twilio: https://www.twilio.com/en-us/messaging. After creating the account and adding credits or selecting
subscription, you will get Account SID and an Authentication from the console dashboard: https://console.twilio.com. You will need a twilio number or setup one as a part
of the subscription. Navigate to the following URL to get one: https://console.twilio.com/us1/develop/phone-numbers/manage/search. After getting/setting up the phone number
setup an SMS gateway from Moodle (Site administration > SMS > Manage sms gateways) and start using Twilio as your SMS gateway.

---
