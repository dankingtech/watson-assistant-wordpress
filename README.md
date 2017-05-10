# conversation-wordpress-plugin
A Wordpress plugin for Watson Conversation.

## Setup
Before you can use this plugin, you need to build the Javascript app by running the following commands from the project root:

```bash
cd js
npm install
npm run build
```

In order to build an optimized file, use the following instead:
```bash
cd js
npm install
npm run prod
```

## Testing
A Wordpress Docker Compose file has been provided for testing the plugin.
Simply run the following in your shell from the project root:

```bash
docker-compose up -d
```

The website can then be accessed at localhost:8000.
