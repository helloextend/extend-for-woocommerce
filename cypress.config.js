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
      CYPRESS_SITE_URL: process.env.SITE_URL,
      CYPRESS_WP_ADMIN_USERNAME: process.env.WP_ADMIN_USERNAME,
      CYPRESS_WP_ADMIN_PASSWORD: process.env.WP_ADMIN_PASSWORD,
      CYPRESS_STORE_ID: process.env.STORE_ID,
      CYPRESS_CLIENT_ID: process.env.CLIENT_ID,
      CYPRESS_CLIENT_SECRET: process.env.CLIENT_SECRET
    }
  }
});