const { defineConfig } = require('cypress');

module.exports = defineConfig({
  e2e: {
    baseUrl: process.env.CYPRESS_BASE_URL || 'http://localhost:8000',
    viewportWidth: 1366,
    viewportHeight: 768,
    video: false,
    defaultCommandTimeout: 8000,
    setupNodeEvents(on, config) {
      return config;
    },
  },
});
