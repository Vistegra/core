/// <reference types="cypress" />
// ***********************************************************
// This example plugins/index.js can be used to load plugins
//
// You can change the location of this file or turn off loading
// the plugins file with the 'pluginsFile' configuration option.
//
// You can read more here:
// https://on.cypress.io/plugins-guide
// ***********************************************************

// This function is called when a project is opened or re-opened (e.g. due to
// the project's config changing)

/**
 * @type {Cypress.PluginConfig}
 */

//const watch = require('c:\\Users\\TryDim\\AppData\\Roaming\\npm\\node_modules\\@cypress\\watch-preprocessor');
//const webpackPreprocessor = require('@cypress/webpack-preprocessor');

// eslint-disable-next-line no-unused-vars
module.exports = (on, config) => {
  // `on` is used to hook into various events Cypress emits
  // `config` is the resolved Cypress config


  //on('file:preprocessor', watch())

  /*const options = {
    // send in the options from your webpack.config.js, so it works the same
    // as your app's code
    webpackOptions: require('C:\\PHPstormProject\\graddoor\\public\\src\\webpack\\webpack.config.js'),
    watchOptions: {},
  }
  on('file:preprocessor', webpackPreprocessor(options))*/

  require('cypress-watch-and-reload/plugins')(config)
}
