const { defineConfig } = require('cypress');

module.exports = defineConfig({
  e2e: {
    baseUrl: process.env.SITE_URL || 'https://woocommerce.woodys.extend.com/',
    specPattern: 'cypress/integration/**/*.cy.{js,jsx,ts,tsx}',
    fixturesFolder: 'cypress/fixtures',
    screenshotsFolder: 'cypress/screenshots',
    videosFolder: 'cypress/videos',
    supportFile: false,
    env: {
      SITE_URL: process.env.CYPRESS_SITE_URL,
      WP_ADMIN_USERNAME: process.env.CYPRESS_WP_ADMIN_USERNAME,
      WP_ADMIN_PASSWORD: process.env.CYPRESS_WP_ADMIN_PASSWORD,
      STORE_ID: process.env.CYPRESS_STORE_ID,
      CLIENT_ID: process.env.CYPRESS_CLIENT_ID,
      CLIENT_SECRET: process.env.CYPRESS_CLIENT_SECRET
    }
  }
});