# conversation-wordpress-plugin
A Wordpress plugin for Watson Assistant.

## Setup
Before you can use this plugin, you need to build the Javascript app by running the following commands from the `js` directory:

```bash
npm install
npm run build
```

In order to build a minimized file, use `npm run prod` instead.

## Testing
A Wordpress Docker Compose file has been provided for testing the plugin.
Simply run the following in your shell from the `docker` directory:

```bash
docker-compose up -d
```

The website can then be accessed at localhost:8000.

## Directories

### docker
This directory contains the files necessary to create a test Wordpress site using docker-compose.

### js
This directory contains the UI component of the plugin: a chat box component implemented using React.js. The commands outlined in `Setup` above are required to compile this React.js project into a single file located at `watson-conversation/app.js` for the Wordpress server to load.

### watson-conversation
When a user installs this plugin, this is the directory that is placed in the `wp-content/plugins` directory. Once the Javascript app has been built per the instructions above, this directory contains all the files necessary for Wordpress to run the plugin.

### aux
This directory contains the Readme.txt and plugin assets.
