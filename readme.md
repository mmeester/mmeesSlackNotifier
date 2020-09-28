# Slack notifications for Shopware 6

## Installation

### Composer
Install the plugin using composer
```
composer require mmeester/mmees-slack-notifier
```

#### Activate plugin
In your CLI run the following inside the root of your Shopware project:

Detect new plugins: `bin/console plugin:refresh` 👉 Look for the new plugin
Install & activate the plugin: `bin/console plugin:install --activate mmeesSlackNotifier`

### Shopware store installation
URL will follow once the plugin is published to the Shopware store

## Setup
To use the plugin we need a Slack webhook to post an event: 

### Slack setup
1. Go to https://api.slack.com/apps
1. Click the green button "Create new app" in the right top corner
1. Give your app a proper name (eq. "Shopware"), select the workspace you want  to install the app, and click the button "Create App"
1. Create a webhook clicking the option "Incoming Webhooks"
1. Activate the webhook by flipping the switch in the right top corner
1. Scroll down and click the button "Add New Webhook to Workspace", select the channel you want your notifications to be posted to and click "Allow"


### Shopware Setup 
1. Install the plugin to your Shopware  store
1. Copy the webhook URL and add this to the configuration of this plugin.
1. You are all setup, start selling / placing orders and notifications will follow
