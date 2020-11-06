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

## What is next?

### First thoughts on extending

Currently We're looking for ideas / inspiration on watchable events.Please create an [issues](https://github.com/mmeester/mmeesSlackNotifier/issues) if you also have some thoughts on this! Here are some first thoughts:

- [x] State changes on order
- [ ] New customer registration
- [ ] Failed admin login attempt
- [ ] New review
- [ ] Newsletter subscription
- [ ] Product out of stock
- [ ] Return request

### Other work to do

- [ ] Refactor current code

## Contributing

We love your input! We want to make contributing to this project as easy and transparent as possible, whether it's:

- Reporting a bug
- Discussing the current state of the code
- Submitting a fix
- Proposing new features
- Becoming a maintainer
