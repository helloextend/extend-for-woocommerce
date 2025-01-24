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
      SITE_URL: process.env.SITE_URL,
      WP_ADMIN_USERNAME: process.env.WP_ADMIN_USERNAME,
      WP_ADMIN_PASSWORD: process.env.WP_ADMIN_PASSWORD,
      STORE_ID: process.env.STORE_ID,
      CLIENT_ID: process.env.CLIENT_ID,
      CLIENT_SECRET: process.env.CLIENT_SECRET
    }
  }
});