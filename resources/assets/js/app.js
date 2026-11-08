/**
 * Zen X Tube - Core JavaScript Application
 * Pure Vanilla JavaScript & HTML/CSS Architecture (Zero Vue Dependency)
 */

require('./bootstrap');

import ExampleComponent from './components/ExampleComponent';

document.addEventListener('DOMContentLoaded', () => {
    // Mount vanilla components if mount element exists
    const appElement = document.getElementById('app');
    if (appElement && appElement.querySelector('[data-component="example"]')) {
        new ExampleComponent(appElement.querySelector('[data-component="example"]'));
    }

    console.log('Zen X Tube frontend initialized with Vanilla JavaScript.');
});
