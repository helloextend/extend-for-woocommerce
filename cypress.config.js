const { defineConfig } = require('cypress');

module.exports = defineConfig({
  e2e: {
    baseUrl: process.env.SITE_URL || 'https://woocommerce.woodys.extend.com/',
    specPattern: 'cypress/integration/**/*.cy.{js,jsx,ts,tsx}',
    fixturesFolder: 'cypress/fixtures',
    screenshotsFolder: 'cypress/screenshots',
    videosFolder: 'cypress/videos',
    supportFile: false,
    env: require('./cypress.env.json'),
  }
});